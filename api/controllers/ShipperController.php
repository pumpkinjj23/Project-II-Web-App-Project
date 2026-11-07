<?php
/**
 * ShipperController.php - จัดการข้อมูลบริษัทขนส่ง (tb_shippers)
 * ข้อกำหนด 11: บริษัทขนส่ง (Shippers · CRUD)
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/MockDataProvider.php';

class ShipperController {
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

    private static function getShipperIcon($id) {
        $icons = [1 => '🚀', 2 => '📦', 3 => '🛳'];
        return $icons[$id] ?? '🚚';
    }

    /**
     * GET /api/v1/shippers
     */
    public function index() {
        if ($this->conn) {
            try {
                $sql = "SELECT s.i_ShipperID, s.c_ShipperName, s.c_Phone,
                               COUNT(o.i_OrderID) AS delivery_count
                        FROM tb_shippers s
                        LEFT JOIN tb_orders o ON s.i_ShipperID = o.i_ShipperID
                        GROUP BY s.i_ShipperID, s.c_ShipperName, s.c_Phone
                        ORDER BY s.i_ShipperID ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $shippers = $stmt->fetchAll();

                foreach ($shippers as &$sh) {
                    $sh['icon'] = self::getShipperIcon($sh['i_ShipperID']);
                    $sh['status'] = 'พร้อมให้บริการ';
                }

                Response::success($shippers, 'ดึงข้อมูลบริษัทขนส่งสำเร็จ', [
                    'total' => count($shippers)
                ]);
            } catch (Exception $e) {
                // fallback
            }
        }

        $all = MockDataProvider::getShippers();
        Response::success($all, 'ดึงข้อมูลบริษัทขนส่งสำเร็จ (โหมดจำลอง)', [
            'total' => count($all)
        ]);
    }

    /**
     * GET /api/v1/shippers/{id}
     */
    public function show($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                $sql = "SELECT s.*, COUNT(o.i_OrderID) AS delivery_count
                        FROM tb_shippers s
                        LEFT JOIN tb_orders o ON s.i_ShipperID = o.i_ShipperID
                        WHERE s.i_ShipperID = ?
                        GROUP BY s.i_ShipperID";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$id]);
                $shipper = $stmt->fetch();

                if ($shipper) {
                    $shipper['icon'] = self::getShipperIcon($shipper['i_ShipperID']);
                    $shipper['status'] = 'พร้อมให้บริการ';
                    Response::success($shipper, 'ดึงข้อมูลบริษัทขนส่งสำเร็จ');
                } else {
                    Response::notFound('ไม่พบบริษัทขนส่งรหัส #' . $id);
                }
            } catch (Exception $e) {
                // fallback
            }
        }

        $all = MockDataProvider::getShippers();
        foreach ($all as $sh) {
            if ($sh['i_ShipperID'] == $id) {
                Response::success($sh, 'ดึงข้อมูลบริษัทขนส่งสำเร็จ');
            }
        }
        Response::notFound('ไม่พบบริษัทขนส่งรหัส #' . $id);
    }

    /**
     * POST /api/v1/shippers
     */
    public function store() {
        $data = $this->getRequestData();

        $name  = trim($data['c_ShipperName'] ?? $data['shipper_name'] ?? '');
        $phone = trim($data['c_Phone'] ?? $data['phone'] ?? '');

        if (empty($name)) {
            Response::error('กรุณาระบุชื่อบริษัทขนส่ง', 400);
        }

        if ($this->conn) {
            try {
                $maxStmt = $this->conn->prepare("SELECT COALESCE(MAX(i_ShipperID), 0) + 1 FROM tb_shippers");
                $maxStmt->execute();
                $nextId = (int)$maxStmt->fetchColumn();

                $sql = "INSERT INTO tb_shippers (i_ShipperID, c_ShipperName, c_Phone) VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$nextId, $name, $phone]);

                Response::created(['i_ShipperID' => $nextId, 'c_ShipperName' => $name], 'เพิ่มบริษัทขนส่งสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('ไม่สามารถเพิ่มบริษัทขนส่งได้: ' . $e->getMessage());
            }
        }

        Response::created(['i_ShipperID' => rand(4, 99)], 'เพิ่มบริษัทขนส่งสำเร็จ (โหมดจำลอง)');
    }

    /**
     * PUT /api/v1/shippers/{id}
     */
    public function update($id) {
        $id = (int)$id;
        $data = $this->getRequestData();

        $name  = trim($data['c_ShipperName'] ?? $data['shipper_name'] ?? '');
        $phone = trim($data['c_Phone'] ?? $data['phone'] ?? '');

        if (empty($name)) {
            Response::error('กรุณาระบุชื่อบริษัทขนส่ง', 400);
        }

        if ($this->conn) {
            try {
                $sql = "UPDATE tb_shippers SET c_ShipperName = ?, c_Phone = ? WHERE i_ShipperID = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$name, $phone, $id]);

                Response::success(['i_ShipperID' => $id], 'อัปเดตบริษัทขนส่งสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการอัปเดต: ' . $e->getMessage());
            }
        }

        Response::success(['i_ShipperID' => $id], 'อัปเดตบริษัทขนส่งสำเร็จ (โหมดจำลอง)');
    }

    /**
     * DELETE /api/v1/shippers/{id}
     */
    public function destroy($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                $chk = $this->conn->prepare("SELECT COUNT(*) FROM tb_orders WHERE i_ShipperID = ?");
                $chk->execute([$id]);
                $count = (int)$chk->fetchColumn();

                if ($count > 0) {
                    Response::error("ไม่สามารถลบได้เนื่องจากมีคำสั่งซื้อที่ใช้บริการขนส่งนี้ {$count} ครั้ง", 400);
                }

                $del = $this->conn->prepare("DELETE FROM tb_shippers WHERE i_ShipperID = ?");
                $del->execute([$id]);

                Response::success(null, 'ลบบริษัทขนส่งสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการลบ: ' . $e->getMessage());
            }
        }

        Response::success(null, 'ลบบริษัทขนส่งสำเร็จ (โหมดจำลอง)');
    }
}

