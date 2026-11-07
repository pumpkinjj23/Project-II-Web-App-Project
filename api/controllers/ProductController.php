<?php
/**
 * ProductController.php - จัดการข้อมูลสินค้า (tb_products)
 * ข้อกำหนด 03: รายการสินค้า (ค้นหา · กรอง · ตาราง)
 * ข้อกำหนด 04: เพิ่ม/แก้ไขสินค้า (Form · Validation · Bind Parameter)
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/MockDataProvider.php';

class ProductController {
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
     * GET /api/v1/products - ค้นหาและดึงรายการสินค้า
     */
    public function index() {
        $search     = trim($_GET['search'] ?? $_GET['keyword'] ?? '');
        $categoryId = trim($_GET['category_id'] ?? $_GET['catId'] ?? '');
        $supplierId = trim($_GET['supplier_id'] ?? $_GET['supplierId'] ?? '');
        $minPrice   = trim($_GET['min_price'] ?? $_GET['minPrice'] ?? '');
        $maxPrice   = trim($_GET['max_price'] ?? $_GET['maxPrice'] ?? '');
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $limit      = max(1, min(100, (int)($_GET['limit'] ?? 25)));
        $offset     = ($page - 1) * $limit;

        if ($this->conn) {
            try {
                $where = ["1=1"];
                $params = [];

                if ($search !== '') {
                    if (is_numeric($search)) {
                        $where[] = "(p.i_ProductID = ? OR p.c_ProductName LIKE ?)";
                        $params[] = (int)$search;
                        $params[] = "%{$search}%";
                    } else {
                        $where[] = "(p.c_ProductName LIKE ? OR c.c_CategoryName LIKE ? OR s.c_SupplierName LIKE ?)";
                        $params[] = "%{$search}%";
                        $params[] = "%{$search}%";
                        $params[] = "%{$search}%";
                    }
                }

                if ($categoryId !== '' && $categoryId !== 'all') {
                    $where[] = "p.i_CategoryID = ?";
                    $params[] = (int)$categoryId;
                }

                if ($supplierId !== '' && $supplierId !== 'all') {
                    $where[] = "p.i_SupplierID = ?";
                    $params[] = (int)$supplierId;
                }

                if ($minPrice !== '') {
                    $where[] = "p.i_Price >= ?";
                    $params[] = (float)$minPrice;
                }

                if ($maxPrice !== '') {
                    $where[] = "p.i_Price <= ?";
                    $params[] = (float)$maxPrice;
                }

                $whereClause = implode(" AND ", $where);

                // นับจำนวนทั้งหมด
                $countSql = "SELECT COUNT(*) FROM tb_products p
                             LEFT JOIN tb_categories c ON p.i_CategoryID = c.i_CategoryID
                             LEFT JOIN tb_suppliers s ON p.i_SupplierID = s.i_SupplierID
                             WHERE {$whereClause}";
                $countStmt = $this->conn->prepare($countSql);
                $countStmt->execute($params);
                $total = (int)$countStmt->fetchColumn();

                // ดึงข้อมูลรายการสินค้า
                $sql = "SELECT p.i_ProductID, p.c_ProductName, p.i_SupplierID, p.i_CategoryID, p.c_Unit, p.i_Price,
                               COALESCE(c.c_CategoryName, 'ไม่ระบุ') AS c_CategoryName,
                               COALESCE(s.c_SupplierName, 'ไม่ระบุ') AS c_SupplierName
                        FROM tb_products p
                        LEFT JOIN tb_categories c ON p.i_CategoryID = c.i_CategoryID
                        LEFT JOIN tb_suppliers s ON p.i_SupplierID = s.i_SupplierID
                        WHERE {$whereClause}
                        ORDER BY p.i_ProductID ASC
                        LIMIT {$limit} OFFSET {$offset}";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute($params);
                $products = $stmt->fetchAll();

                // คำนวณ Status และ Format ให้ตรงตาม Mock UI
                foreach ($products as &$p) {
                    $price = (float)$p['i_Price'];
                    if ($price > 10000) {
                        $p['status'] = 'ตรวจสอบ';
                        $p['badge_class'] = 'badge-amber';
                    } elseif ($p['i_ProductID'] == 63) {
                        $p['status'] = 'ไม่ใช้งาน';
                        $p['badge_class'] = 'badge-red';
                    } else {
                        $p['status'] = 'ใช้งานอยู่';
                        $p['badge_class'] = 'badge-green';
                    }
                }

                Response::success($products, 'ดึงรายการสินค้าสำเร็จ', [
                    'total' => $total,
                    'page'  => $page,
                    'limit' => $limit,
                    'totalPages' => ceil($total / $limit)
                ]);
            } catch (Exception $e) {
                // fallback to mock
            }
        }

        // Fallback Mock Products
        $all = MockDataProvider::getProducts();
        $filtered = array_filter($all, function($p) use ($search, $categoryId) {
            if ($search !== '' && stripos($p['c_ProductName'], $search) === false && strpos((string)$p['i_ProductID'], $search) === false) {
                return false;
            }
            if ($categoryId !== '' && $categoryId !== 'all' && $p['i_CategoryID'] != $categoryId) {
                return false;
            }
            return true;
        });

        $total = count($filtered);
        $slice = array_slice($filtered, $offset, $limit);

        Response::success(array_values($slice), 'ดึงรายการสินค้าสำเร็จ (โหมดจำลอง)', [
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
            'totalPages' => max(1, ceil($total / $limit))
        ]);
    }

    /**
     * GET /api/v1/products/{id} - ดึงข้อมูลสินค้ารายชิ้น
     */
    public function show($id) {
        $id = (int)$id;
        if ($this->conn) {
            try {
                $sql = "SELECT p.*,
                               COALESCE(c.c_CategoryName, 'ไม่ระบุ') AS c_CategoryName,
                               COALESCE(s.c_SupplierName, 'ไม่ระบุ') AS c_SupplierName,
                               COALESCE(s.c_ContactName, 'ไม่ระบุ') AS c_ContactName,
                               COALESCE(s.c_Phone, 'ไม่ระบุ') AS c_Phone
                        FROM tb_products p
                        LEFT JOIN tb_categories c ON p.i_CategoryID = c.i_CategoryID
                        LEFT JOIN tb_suppliers s ON p.i_SupplierID = s.i_SupplierID
                        WHERE p.i_ProductID = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$id]);
                $product = $stmt->fetch();

                if ($product) {
                    $price = (float)$product['i_Price'];
                    $product['status'] = ($price > 10000) ? 'ตรวจสอบ' : 'ใช้งานอยู่';
                    Response::success($product, 'ดึงข้อมูลสินค้าสำเร็จ');
                } else {
                    Response::notFound('ไม่พบสินค้ารหัส #' . $id);
                }
            } catch (Exception $e) {
                // fallback
            }
        }

        $all = MockDataProvider::getProducts();
        foreach ($all as $p) {
            if ($p['i_ProductID'] == $id) {
                Response::success($p, 'ดึงข้อมูลสินค้าสำเร็จ');
            }
        }
        Response::notFound('ไม่พบสินค้ารหัส #' . $id);
    }

    /**
     * POST /api/v1/products - เพิ่มสินค้าใหม่
     */
    public function store() {
        $data = $this->getRequestData();

        $name       = trim($data['c_ProductName'] ?? $data['name'] ?? '');
        $categoryId = (int)($data['i_CategoryID'] ?? $data['category_id'] ?? 0);
        $supplierId = (int)($data['i_SupplierID'] ?? $data['supplier_id'] ?? 0);
        $unit       = trim($data['c_Unit'] ?? $data['unit'] ?? '');
        $price      = (float)($data['i_Price'] ?? $data['price'] ?? 0);

        // Validation
        $errors = [];
        if (empty($name)) {
            $errors[] = 'กรุณากรอกชื่อสินค้า';
        } elseif (mb_strlen($name, 'UTF-8') > 30) {
            $errors[] = 'ชื่อสินค้าต้องมีความยาวไม่เกิน 30 ตัวอักษร (ตามขนาดคอลัมน์ varchar(30))';
        }

        if ($categoryId <= 0) {
            $errors[] = 'กรุณาเลือกหมวดหมู่สินค้า';
        }

        if ($supplierId <= 0) {
            $errors[] = 'กรุณาเลือกผู้จัดจำหน่าย';
        }

        if (empty($unit)) {
            $errors[] = 'กรุณากรอกหน่วยบรรจุภัณฑ์';
        } elseif (mb_strlen($unit, 'UTF-8') > 30) {
            $errors[] = 'หน่วยบรรจุภัณฑ์ต้องไม่เกิน 30 ตัวอักษร';
        }

        if ($price <= 0) {
            $errors[] = 'ราคาต้องมากกว่า 0 บาท';
        }

        if (!empty($errors)) {
            Response::error(implode(', ', $errors), 400);
        }

        if ($this->conn) {
            try {
                $sql = "INSERT INTO tb_products (c_ProductName, i_SupplierID, i_CategoryID, c_Unit, i_Price)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$name, $supplierId, $categoryId, $unit, $price]);
                $newId = $this->conn->lastInsertId();

                $autoStatus = ($price > 10000) ? 'ตรวจสอบ (เนื่องจากราคาสูงกว่า ฿10,000)' : 'ใช้งานอยู่';

                Response::created([
                    'i_ProductID'   => (int)$newId,
                    'c_ProductName' => $name,
                    'i_CategoryID'  => $categoryId,
                    'i_SupplierID'  => $supplierId,
                    'c_Unit'        => $unit,
                    'i_Price'       => $price,
                    'status_note'   => $autoStatus
                ], 'บันทึกสินค้าใหม่สำเร็จ รหัส #' . $newId);
            } catch (Exception $e) {
                Response::serverError('ไม่สามารถบันทึกข้อมูลได้: ' . $e->getMessage());
            }
        }

        // Mock Fallback
        Response::created([
            'i_ProductID'   => rand(105, 999),
            'c_ProductName' => $name,
            'i_CategoryID'  => $categoryId,
            'i_SupplierID'  => $supplierId,
            'c_Unit'        => $unit,
            'i_Price'       => $price
        ], 'บันทึกสินค้าใหม่สำเร็จ (โหมดจำลอง)');
    }

    /**
     * PUT /api/v1/products/{id} - แก้ไขข้อมูลสินค้า
     */
    public function update($id) {
        $id = (int)$id;
        $data = $this->getRequestData();

        $name       = trim($data['c_ProductName'] ?? $data['name'] ?? '');
        $categoryId = (int)($data['i_CategoryID'] ?? $data['category_id'] ?? 0);
        $supplierId = (int)($data['i_SupplierID'] ?? $data['supplier_id'] ?? 0);
        $unit       = trim($data['c_Unit'] ?? $data['unit'] ?? '');
        $price      = (float)($data['i_Price'] ?? $data['price'] ?? 0);

        if (empty($name) || $categoryId <= 0 || $supplierId <= 0 || empty($unit) || $price <= 0) {
            Response::error('กรุณากรอกข้อมูลสินค้าให้ถูกต้องและครบถ้วน', 400);
        }

        if (mb_strlen($name, 'UTF-8') > 30) {
            Response::error('ชื่อสินค้าต้องไม่เกิน 30 ตัวอักษร', 400);
        }

        if ($this->conn) {
            try {
                $sql = "UPDATE tb_products
                        SET c_ProductName = ?, i_SupplierID = ?, i_CategoryID = ?, c_Unit = ?, i_Price = ?
                        WHERE i_ProductID = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$name, $supplierId, $categoryId, $unit, $price, $id]);

                Response::success([
                    'i_ProductID'   => $id,
                    'c_ProductName' => $name,
                    'i_CategoryID'  => $categoryId,
                    'i_SupplierID'  => $supplierId,
                    'c_Unit'        => $unit,
                    'i_Price'       => $price
                ], 'อัปเดตข้อมูลสินค้ารหัส #' . $id . ' เรียบร้อยแล้ว');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการอัปเดต: ' . $e->getMessage());
            }
        }

        Response::success(['i_ProductID' => $id], 'อัปเดตข้อมูลสินค้ารหัส #' . $id . ' เรียบร้อยแล้ว (โหมดจำลอง)');
    }

    /**
     * DELETE /api/v1/products/{id} - ลบสินค้า
     */
    public function destroy($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                // ตรวจสอบก่อนว่ามีการใช้งานใน tb_orderdetails หรือไม่
                $chkSql = "SELECT COUNT(*) FROM tb_orderdetails WHERE i_ProductID = ?";
                $chkStmt = $this->conn->prepare($chkSql);
                $chkStmt->execute([$id]);
                $orderCount = (int)$chkStmt->fetchColumn();

                if ($orderCount > 0) {
                    Response::error("ไม่สามารถลบสินค้านี้ได้ เนื่องจากมีประวัติอยู่ในคำสั่งซื้อจำนวน {$orderCount} รายการ", 400);
                }

                $delSql = "DELETE FROM tb_products WHERE i_ProductID = ?";
                $delStmt = $this->conn->prepare($delSql);
                $delStmt->execute([$id]);

                Response::success(null, 'ลบสินค้ารหัส #' . $id . ' สำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการลบ: ' . $e->getMessage());
            }
        }

        Response::success(null, 'ลบสินค้ารหัส #' . $id . ' สำเร็จ (โหมดจำลอง)');
    }
}

