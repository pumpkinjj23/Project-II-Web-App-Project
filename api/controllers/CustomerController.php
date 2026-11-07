<?php
/**
 * CustomerController.php - จัดการข้อมูลลูกค้า (tb_customers)
 * ข้อกำหนด 08: ลูกค้า (Customers · CRUD)
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/MockDataProvider.php';

class CustomerController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    private function getRequestData() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = $_POST;
            if (empty($data)) {
                parse_str(file_get_contents('php://input'), $data);
            }
        }
        return $data;
    }

    /**
     * คืนค่า Flag อิโมจิสำหรับแต่ละประเทศ
     */
    private static function getCountryFlag($country) {
        $flags = [
            'Germany' => '🇩🇪 เยอรมนี',
            'Mexico'  => '🇲🇽 เม็กซิโก',
            'UK'      => '🇬🇧 สหราชอาณาจักร',
            'Sweden'  => '🇸🇪 สวีเดน',
            'Austria' => '🇦🇹 ออสเตรีย',
            'France'  => '🇫🇷 ฝรั่งเศส',
            'USA'     => '🇺🇸 สหรัฐอเมริกา',
            'Brazil'  => '🇧🇷 บราซิล',
            'Spain'   => '🇪🇸 สเปน',
            'Italy'   => '🇮🇹 อิตาลี',
            'Canada'  => '🇨🇦 แคนาดา',
            'Ireland' => '🇮🇪 ไอร์แลนด์'
        ];
        return $flags[$country] ?? "🌐 {$country}";
    }

    /**
     * GET /api/v1/customers - ดึงรายการลูกค้าทั้งหมด
     */
    public function index() {
        $search  = trim($_GET['search'] ?? $_GET['keyword'] ?? '');
        $country = trim($_GET['country'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $limit   = max(1, min(100, (int)($_GET['limit'] ?? 25)));
        $offset  = ($page - 1) * $limit;

        if ($this->conn) {
            try {
                $where = ["1=1"];
                $params = [];

                if ($search !== '') {
                    $where[] = "(c_customername LIKE ? OR c_contactname LIKE ? OR c_city LIKE ?)";
                    $params[] = "%{$search}%";
                    $params[] = "%{$search}%";
                    $params[] = "%{$search}%";
                }

                if ($country !== '' && $country !== 'ทุกประเทศ' && $country !== 'all') {
                    $where[] = "c_country LIKE ?";
                    $params[] = "%{$country}%";
                }

                $whereClause = implode(" AND ", $where);

                $countStmt = $this->conn->prepare("SELECT COUNT(*) FROM tb_customers WHERE {$whereClause}");
                $countStmt->execute($params);
                $total = (int)$countStmt->fetchColumn();

                $sql = "SELECT i_customerid, c_customername, c_contactname, c_address, c_city, c_postalcode, c_country
                        FROM tb_customers
                        WHERE {$whereClause}
                        ORDER BY i_customerid ASC
                        LIMIT {$limit} OFFSET {$offset}";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute($params);
                $customers = $stmt->fetchAll();

                foreach ($customers as &$c) {
                    $c['country_display'] = self::getCountryFlag($c['c_country']);
                }

                Response::success($customers, 'ดึงข้อมูลลูกค้าสำเร็จ', [
                    'total' => $total,
                    'page'  => $page,
                    'limit' => $limit,
                    'totalPages' => max(1, ceil($total / $limit))
                ]);
            } catch (Exception $e) {
                // fallback
            }
        }

        $all = MockDataProvider::getCustomers();
        foreach ($all as &$c) {
            $c['country_display'] = self::getCountryFlag($c['c_country']);
        }
        Response::success($all, 'ดึงข้อมูลลูกค้าสำเร็จ (โหมดจำลอง)', [
            'total' => count($all),
            'page'  => 1,
            'limit' => $limit,
            'totalPages' => 1
        ]);
    }

    /**
     * GET /api/v1/customers/{id}
     */
    public function show($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare("SELECT * FROM tb_customers WHERE i_customerid = ?");
                $stmt->execute([$id]);
                $customer = $stmt->fetch();

                if ($customer) {
                    $customer['country_display'] = self::getCountryFlag($customer['c_country']);
                    Response::success($customer, 'ดึงข้อมูลลูกค้าสำเร็จ');
                } else {
                    Response::notFound('ไม่พบลูกค้ารหัส #' . $id);
                }
            } catch (Exception $e) {
                // fallback
            }
        }

        $all = MockDataProvider::getCustomers();
        foreach ($all as $c) {
            if ($c['i_customerid'] == $id) {
                $c['country_display'] = self::getCountryFlag($c['c_country']);
                Response::success($c, 'ดึงข้อมูลลูกค้าสำเร็จ');
            }
        }
        Response::notFound('ไม่พบลูกค้ารหัส #' . $id);
    }

    /**
     * POST /api/v1/customers
     */
    public function store() {
        $data = $this->getRequestData();

        $name       = trim($data['c_customername'] ?? $data['customer_name'] ?? '');
        $contact    = trim($data['c_contactname'] ?? $data['contact_name'] ?? '');
        $address    = trim($data['c_address'] ?? $data['address'] ?? '');
        $city       = trim($data['c_city'] ?? $data['city'] ?? '');
        $postalcode = trim($data['c_postalcode'] ?? $data['postal_code'] ?? '');
        $country    = trim($data['c_country'] ?? $data['country'] ?? '');

        if (empty($name) || empty($contact)) {
            Response::error('กรุณาระบุชื่อบริษัทและชื่อผู้ติดต่อ', 400);
        }

        if ($this->conn) {
            try {
                $maxStmt = $this->conn->prepare("SELECT COALESCE(MAX(i_customerid), 0) + 1 FROM tb_customers");
                $maxStmt->execute();
                $nextId = (int)$maxStmt->fetchColumn();

                $sql = "INSERT INTO tb_customers (i_customerid, c_customername, c_contactname, c_address, c_city, c_postalcode, c_country)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$nextId, $name, $contact, $address, $city, $postalcode, $country]);

                Response::created([
                    'i_customerid'   => $nextId,
                    'c_customername' => $name,
                    'c_contactname'  => $contact,
                    'c_country'      => $country
                ], 'เพิ่มข้อมูลลูกค้าใหม่สำเร็จ รหัส #' . $nextId);
            } catch (Exception $e) {
                Response::serverError('ไม่สามารถเพิ่มข้อมูลลูกค้าได้: ' . $e->getMessage());
            }
        }

        Response::created(['i_customerid' => rand(100, 999)], 'เพิ่มข้อมูลลูกค้าสำเร็จ (โหมดจำลอง)');
    }

    /**
     * PUT /api/v1/customers/{id}
     */
    public function update($id) {
        $id = (int)$id;
        $data = $this->getRequestData();

        $name       = trim($data['c_customername'] ?? $data['customer_name'] ?? '');
        $contact    = trim($data['c_contactname'] ?? $data['contact_name'] ?? '');
        $address    = trim($data['c_address'] ?? $data['address'] ?? '');
        $city       = trim($data['c_city'] ?? $data['city'] ?? '');
        $postalcode = trim($data['c_postalcode'] ?? $data['postal_code'] ?? '');
        $country    = trim($data['c_country'] ?? $data['country'] ?? '');

        if (empty($name) || empty($contact)) {
            Response::error('กรุณาระบุชื่อบริษัทและชื่อผู้ติดต่อ', 400);
        }

        if ($this->conn) {
            try {
                $sql = "UPDATE tb_customers
                        SET c_customername = ?, c_contactname = ?, c_address = ?, c_city = ?, c_postalcode = ?, c_country = ?
                        WHERE i_customerid = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$name, $contact, $address, $city, $postalcode, $country, $id]);

                Response::success(['i_customerid' => $id], 'อัปเดตข้อมูลลูกค้าสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการอัปเดต: ' . $e->getMessage());
            }
        }

        Response::success(['i_customerid' => $id], 'อัปเดตข้อมูลลูกค้าสำเร็จ (โหมดจำลอง)');
    }

    /**
     * DELETE /api/v1/customers/{id}
     */
    public function destroy($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                $chk = $this->conn->prepare("SELECT COUNT(*) FROM tb_orders WHERE i_CustomerID = ?");
                $chk->execute([$id]);
                $orderCount = (int)$chk->fetchColumn();

                if ($orderCount > 0) {
                    Response::error("ไม่สามารถลบลูกค้านี้ได้เนื่องจากมีคำสั่งซื้อในระบบ {$orderCount} รายการ", 400);
                }

                $del = $this->conn->prepare("DELETE FROM tb_customers WHERE i_customerid = ?");
                $del->execute([$id]);

                Response::success(null, 'ลบข้อมูลลูกค้าสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการลบ: ' . $e->getMessage());
            }
        }

        Response::success(null, 'ลบข้อมูลลูกค้าสำเร็จ (โหมดจำลอง)');
    }
}

