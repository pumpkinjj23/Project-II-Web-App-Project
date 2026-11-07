<?php
/**
 * SupplierController.php - จัดการข้อมูลผู้จัดจำหน่าย (tb_suppliers)
 * ข้อกำหนด 09: ผู้จัดจำหน่าย (Suppliers · CRUD)
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/MockDataProvider.php';

class SupplierController {
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
     * GET /api/v1/suppliers
     */
    public function index() {
        $search = trim($_GET['search'] ?? $_GET['keyword'] ?? '');

        if ($this->conn) {
            try {
                $where = "1=1";
                $params = [];

                if ($search !== '') {
                    $where = "(s.c_SupplierName LIKE ? OR s.c_ContactName LIKE ? OR s.c_Country LIKE ?)";
                    $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
                }

                $sql = "SELECT s.i_SupplierID, s.c_SupplierName, s.c_ContactName, s.c_Address, s.c_City, s.c_PostalCode, s.c_Country, s.c_Phone,
                               COUNT(p.i_ProductID) AS product_count
                        FROM tb_suppliers s
                        LEFT JOIN tb_products p ON s.i_SupplierID = p.i_SupplierID
                        WHERE {$where}
                        GROUP BY s.i_SupplierID, s.c_SupplierName, s.c_ContactName, s.c_Address, s.c_City, s.c_PostalCode, s.c_Country, s.c_Phone
                        ORDER BY s.i_SupplierID ASC";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute($params);
                $suppliers = $stmt->fetchAll();

                Response::success($suppliers, 'ดึงข้อมูลผู้จัดจำหน่ายสำเร็จ', [
                    'total' => count($suppliers)
                ]);
            } catch (Exception $e) {
                // fallback
            }
        }

        $all = MockDataProvider::getSuppliers();
        Response::success($all, 'ดึงข้อมูลผู้จัดจำหน่ายสำเร็จ (โหมดจำลอง)', [
            'total' => count($all)
        ]);
    }

    /**
     * GET /api/v1/suppliers/{id}
     */
    public function show($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                $sql = "SELECT s.*, COUNT(p.i_ProductID) AS product_count
                        FROM tb_suppliers s
                        LEFT JOIN tb_products p ON s.i_SupplierID = p.i_SupplierID
                        WHERE s.i_SupplierID = ?
                        GROUP BY s.i_SupplierID";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$id]);
                $supplier = $stmt->fetch();

                if ($supplier) {
                    Response::success($supplier, 'ดึงข้อมูลผู้จัดจำหน่ายสำเร็จ');
                } else {
                    Response::notFound('ไม่พบผู้จัดจำหน่ายรหัส #' . $id);
                }
            } catch (Exception $e) {
                // fallback
            }
        }

        $all = MockDataProvider::getSuppliers();
        foreach ($all as $s) {
            if ($s['i_SupplierID'] == $id) {
                Response::success($s, 'ดึงข้อมูลผู้จัดจำหน่ายสำเร็จ');
            }
        }
        Response::notFound('ไม่พบผู้จัดจำหน่ายรหัส #' . $id);
    }

    /**
     * POST /api/v1/suppliers
     */
    public function store() {
        $data = $this->getRequestData();

        $name       = trim($data['c_SupplierName'] ?? $data['supplier_name'] ?? '');
        $contact    = trim($data['c_ContactName'] ?? $data['contact_name'] ?? '');
        $address    = trim($data['c_Address'] ?? $data['address'] ?? '');
        $city       = trim($data['c_City'] ?? $data['city'] ?? '');
        $postalcode = trim($data['c_PostalCode'] ?? $data['postal_code'] ?? '');
        $country    = trim($data['c_Country'] ?? $data['country'] ?? '');
        $phone      = trim($data['c_Phone'] ?? $data['phone'] ?? '');

        if (empty($name) || empty($contact)) {
            Response::error('กรุณาระบุชื่อบริษัทและชื่อผู้ติดต่อ', 400);
        }

        if ($this->conn) {
            try {
                $maxStmt = $this->conn->prepare("SELECT COALESCE(MAX(i_SupplierID), 0) + 1 FROM tb_suppliers");
                $maxStmt->execute();
                $nextId = (int)$maxStmt->fetchColumn();

                $sql = "INSERT INTO tb_suppliers (i_SupplierID, c_SupplierName, c_ContactName, c_Address, c_City, c_PostalCode, c_Country, c_Phone)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$nextId, $name, $contact, $address, $city, $postalcode, $country, $phone]);

                Response::created(['i_SupplierID' => $nextId, 'c_SupplierName' => $name], 'เพิ่มผู้จัดจำหน่ายสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('ไม่สามารถเพิ่มผู้จัดจำหน่ายได้: ' . $e->getMessage());
            }
        }

        Response::created(['i_SupplierID' => rand(30, 99)], 'เพิ่มผู้จัดจำหน่ายสำเร็จ (โหมดจำลอง)');
    }

    /**
     * PUT /api/v1/suppliers/{id}
     */
    public function update($id) {
        $id = (int)$id;
        $data = $this->getRequestData();

        $name       = trim($data['c_SupplierName'] ?? $data['supplier_name'] ?? '');
        $contact    = trim($data['c_ContactName'] ?? $data['contact_name'] ?? '');
        $address    = trim($data['c_Address'] ?? $data['address'] ?? '');
        $city       = trim($data['c_City'] ?? $data['city'] ?? '');
        $postalcode = trim($data['c_PostalCode'] ?? $data['postal_code'] ?? '');
        $country    = trim($data['c_Country'] ?? $data['country'] ?? '');
        $phone      = trim($data['c_Phone'] ?? $data['phone'] ?? '');

        if (empty($name)) {
            Response::error('กรุณาระบุชื่อบริษัทผู้จัดจำหน่าย', 400);
        }

        if ($this->conn) {
            try {
                $sql = "UPDATE tb_suppliers
                        SET c_SupplierName = ?, c_ContactName = ?, c_Address = ?, c_City = ?, c_PostalCode = ?, c_Country = ?, c_Phone = ?
                        WHERE i_SupplierID = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$name, $contact, $address, $city, $postalcode, $country, $phone, $id]);

                Response::success(['i_SupplierID' => $id], 'อัปเดตผู้จัดจำหน่ายสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการอัปเดต: ' . $e->getMessage());
            }
        }

        Response::success(['i_SupplierID' => $id], 'อัปเดตผู้จัดจำหน่ายสำเร็จ (โหมดจำลอง)');
    }

    /**
     * DELETE /api/v1/suppliers/{id}
     */
    public function destroy($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                $chk = $this->conn->prepare("SELECT COUNT(*) FROM tb_products WHERE i_SupplierID = ?");
                $chk->execute([$id]);
                $count = (int)$chk->fetchColumn();

                if ($count > 0) {
                    Response::error("ไม่สามารถลบได้เนื่องจากมีสินค้าที่จัดส่งโดยบริษัทนี้ {$count} รายการ", 400);
                }

                $del = $this->conn->prepare("DELETE FROM tb_suppliers WHERE i_SupplierID = ?");
                $del->execute([$id]);

                Response::success(null, 'ลบผู้จัดจำหน่ายสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการลบ: ' . $e->getMessage());
            }
        }

        Response::success(null, 'ลบผู้จัดจำหน่ายสำเร็จ (โหมดจำลอง)');
    }
}

