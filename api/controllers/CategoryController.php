<?php
/**
 * CategoryController.php - จัดการหมวดหมู่สินค้า (tb_categories)
 * ข้อกำหนด 05: หมวดหมู่สินค้า (CRUD · Categories)
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/MockDataProvider.php';

class CategoryController {
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
     * กำหนดไอคอนสำหรับหมวดหมู่
     */
    private static function getCategoryIcon($name, $id) {
        $icons = [
            1 => '🍹', // Beverages
            2 => '🧂', // Condiments
            3 => '🍬', // Confections
            4 => '🧀', // Dairy
            5 => '🌾', // Grains
            6 => '🥩', // Meat
            7 => '🍄', // Produce
            8 => '🐟', // Seafood
            9 => '👟', // Sports
        ];
        return $icons[$id] ?? '📦';
    }

    /**
     * GET /api/v1/categories
     */
    public function index() {
        if ($this->conn) {
            try {
                $sql = "SELECT c.i_CategoryID, c.c_CategoryName, c.c_Description,
                               COUNT(p.i_ProductID) AS product_count
                        FROM tb_categories c
                        LEFT JOIN tb_products p ON c.i_CategoryID = p.i_CategoryID
                        GROUP BY c.i_CategoryID, c.c_CategoryName, c.c_Description
                        ORDER BY c.i_CategoryID ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $categories = $stmt->fetchAll();

                foreach ($categories as &$cat) {
                    $cat['icon'] = self::getCategoryIcon($cat['c_CategoryName'], $cat['i_CategoryID']);
                }

                Response::success($categories, 'ดึงข้อมูลหมวดหมู่สินค้าสำเร็จ', [
                    'total' => count($categories)
                ]);
            } catch (Exception $e) {
                // fallback
            }
        }

        $cats = MockDataProvider::getCategories();
        Response::success($cats, 'ดึงข้อมูลหมวดหมู่สินค้าสำเร็จ (โหมดจำลอง)', [
            'total' => count($cats)
        ]);
    }

    /**
     * GET /api/v1/categories/{id}
     */
    public function show($id) {
        $id = (int)$id;
        if ($this->conn) {
            try {
                $sql = "SELECT c.i_CategoryID, c.c_CategoryName, c.c_Description,
                               COUNT(p.i_ProductID) AS product_count
                        FROM tb_categories c
                        LEFT JOIN tb_products p ON c.i_CategoryID = p.i_CategoryID
                        WHERE c.i_CategoryID = ?
                        GROUP BY c.i_CategoryID";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$id]);
                $category = $stmt->fetch();

                if ($category) {
                    $category['icon'] = self::getCategoryIcon($category['c_CategoryName'], $category['i_CategoryID']);
                    Response::success($category, 'ดึงข้อมูลหมวดหมู่สำเร็จ');
                } else {
                    Response::notFound('ไม่พบหมวดหมู่รหัส #' . $id);
                }
            } catch (Exception $e) {
                // fallback
            }
        }

        $cats = MockDataProvider::getCategories();
        foreach ($cats as $c) {
            if ($c['i_CategoryID'] == $id) {
                Response::success($c, 'ดึงข้อมูลหมวดหมู่สำเร็จ');
            }
        }
        Response::notFound('ไม่พบหมวดหมู่รหัส #' . $id);
    }

    /**
     * POST /api/v1/categories
     */
    public function store() {
        $data = $this->getRequestData();
        $name = trim($data['c_CategoryName'] ?? $data['name'] ?? '');
        $desc = trim($data['c_Description'] ?? $data['description'] ?? '');

        if (empty($name)) {
            Response::error('กรุณาระบุชื่อหมวดหมู่สินค้า', 400);
        }
        if (mb_strlen($name, 'UTF-8') > 30) {
            Response::error('ชื่อหมวดหมู่ต้องไม่เกิน 30 ตัวอักษร', 400);
        }

        if ($this->conn) {
            try {
                // คำนวณ Next ID หากตารางไม่มี AUTO_INCREMENT
                $maxStmt = $this->conn->prepare("SELECT COALESCE(MAX(i_CategoryID), 0) + 1 FROM tb_categories");
                $maxStmt->execute();
                $nextId = (int)$maxStmt->fetchColumn();

                $sql = "INSERT INTO tb_categories (i_CategoryID, c_CategoryName, c_Description)
                        VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$nextId, $name, $desc]);

                Response::created([
                    'i_CategoryID'   => $nextId,
                    'c_CategoryName' => $name,
                    'c_Description'  => $desc,
                    'icon'           => '📦',
                    'product_count'  => 0
                ], 'เพิ่มหมวดหมู่สินค้าสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('ไม่สามารถเพิ่มหมวดหมู่ได้: ' . $e->getMessage());
            }
        }

        Response::created([
            'i_CategoryID'   => rand(10, 99),
            'c_CategoryName' => $name,
            'c_Description'  => $desc
        ], 'เพิ่มหมวดหมู่สินค้าสำเร็จ (โหมดจำลอง)');
    }

    /**
     * PUT /api/v1/categories/{id}
     */
    public function update($id) {
        $id = (int)$id;
        $data = $this->getRequestData();
        $name = trim($data['c_CategoryName'] ?? $data['name'] ?? '');
        $desc = trim($data['c_Description'] ?? $data['description'] ?? '');

        if (empty($name)) {
            Response::error('กรุณาระบุชื่อหมวดหมู่สินค้า', 400);
        }

        if ($this->conn) {
            try {
                $sql = "UPDATE tb_categories SET c_CategoryName = ?, c_Description = ? WHERE i_CategoryID = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$name, $desc, $id]);

                Response::success([
                    'i_CategoryID'   => $id,
                    'c_CategoryName' => $name,
                    'c_Description'  => $desc
                ], 'แก้ไขหมวดหมู่สินค้าสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการแก้ไข: ' . $e->getMessage());
            }
        }

        Response::success(['i_CategoryID' => $id], 'แก้ไขหมวดหมู่สินค้าสำเร็จ (โหมดจำลอง)');
    }

    /**
     * DELETE /api/v1/categories/{id}
     */
    public function destroy($id) {
        $id = (int)$id;
        if ($this->conn) {
            try {
                // ตรวจสอบสินค้าในหมวดหมู่
                $chk = $this->conn->prepare("SELECT COUNT(*) FROM tb_products WHERE i_CategoryID = ?");
                $chk->execute([$id]);
                $count = (int)$chk->fetchColumn();

                if ($count > 0) {
                    Response::error("ไม่สามารถลบหมวดหมู่นี้ได้เนื่องจากมีสินค้าผูกอยู่ {$count} รายการ", 400);
                }

                $del = $this->conn->prepare("DELETE FROM tb_categories WHERE i_CategoryID = ?");
                $del->execute([$id]);

                Response::success(null, 'ลบหมวดหมู่สินค้าสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการลบ: ' . $e->getMessage());
            }
        }

        Response::success(null, 'ลบหมวดหมู่สินค้าสำเร็จ (โหมดจำลอง)');
    }
}

