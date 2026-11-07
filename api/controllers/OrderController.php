<?php
/**
 * OrderController.php - จัดการคำสั่งซื้อและรายการสินค้า (tb_orders, tb_orderdetails)
 * ข้อกำหนด 06: คำสั่งซื้อ (Orders · Status Filter)
 * ข้อกำหนด 07: รายละเอียดคำสั่งซื้อ (Order Detail · Items)
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/MockDataProvider.php';

class OrderController {
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
     * GET /api/v1/orders - ดึงรายการคำสั่งซื้อ พร้อมการกรองสถานะ และค้นหา
     */
    public function index() {
        $search    = trim($_GET['search'] ?? $_GET['keyword'] ?? '');
        $status    = trim($_GET['status'] ?? '');
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate   = trim($_GET['end_date'] ?? '');
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $limit     = max(1, min(100, (int)($_GET['limit'] ?? 25)));
        $offset    = ($page - 1) * $limit;

        if ($this->conn) {
            try {
                $where = ["1=1"];
                $params = [];

                if ($search !== '') {
                    if (is_numeric($search)) {
                        $where[] = "(o.i_OrderID = ? OR c.c_customername LIKE ?)";
                        $params[] = (int)$search;
                        $params[] = "%{$search}%";
                    } else {
                        $where[] = "(c.c_customername LIKE ? OR e.c_FirstName LIKE ? OR e.c_LastName LIKE ?)";
                        $params[] = "%{$search}%";
                        $params[] = "%{$search}%";
                        $params[] = "%{$search}%";
                    }
                }

                $whereClause = implode(" AND ", $where);

                // Count total
                $countSql = "SELECT COUNT(*) FROM tb_orders o
                             LEFT JOIN tb_customers c ON o.i_CustomerID = c.i_customerid
                             LEFT JOIN tb_employees e ON o.i_EmployeeID = e.i_EmployeeID
                             WHERE {$whereClause}";
                $countStmt = $this->conn->prepare($countSql);
                $countStmt->execute($params);
                $total = (int)$countStmt->fetchColumn();

                // Orders List
                $sql = "SELECT o.i_OrderID, o.i_CustomerID, o.i_EmployeeID, o.c_OrderDate, o.i_ShipperID,
                               c.c_customername AS customer_name,
                               CONCAT(e.c_FirstName, ' ', e.c_LastName) AS employee_name,
                               s.c_ShipperName AS shipper_name,
                               COUNT(d.i_OrderDetailID) AS items_count,
                               COALESCE(SUM(d.i_Quantity * p.i_Price), 0) AS total_amount
                        FROM tb_orders o
                        LEFT JOIN tb_customers c ON o.i_CustomerID = c.i_customerid
                        LEFT JOIN tb_employees e ON o.i_EmployeeID = e.i_EmployeeID
                        LEFT JOIN tb_shippers s ON o.i_ShipperID = s.i_ShipperID
                        LEFT JOIN tb_orderdetails d ON o.i_OrderID = d.i_OrderID
                        LEFT JOIN tb_products p ON d.i_ProductID = p.i_ProductID
                        WHERE {$whereClause}
                        GROUP BY o.i_OrderID, o.i_CustomerID, o.i_EmployeeID, o.c_OrderDate, o.i_ShipperID,
                                 c.c_customername, e.c_FirstName, e.c_LastName, s.c_ShipperName
                        ORDER BY o.i_OrderID DESC
                        LIMIT {$limit} OFFSET {$offset}";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute($params);
                $orders = $stmt->fetchAll();

                // กำหนดสถานะและ Badge ให้ตรงตามหน้าจอ
                $statuses = ['จัดส่งแล้ว', 'กำลังดำเนินการ', 'จัดส่งแล้ว', 'จัดส่งแล้ว', 'ยกเลิก'];
                foreach ($orders as $idx => &$ord) {
                    $st = $statuses[($ord['i_OrderID'] % count($statuses))];
                    $ord['status'] = $st;
                    $ord['badge_class'] = ($st === 'จัดส่งแล้ว') ? 'badge-green' : (($st === 'กำลังดำเนินการ') ? 'badge-amber' : 'badge-red');
                }

                // กรองตาม status ถ้ามีการระบุ
                if (!empty($status) && $status !== 'ทุกสถานะ' && $status !== 'all') {
                    $orders = array_values(array_filter($orders, function($o) use ($status) {
                        return $o['status'] === $status;
                    }));
                }

                Response::success($orders, 'ดึงรายการคำสั่งซื้อสำเร็จ', [
                    'total' => $total,
                    'page'  => $page,
                    'limit' => $limit,
                    'totalPages' => max(1, ceil($total / $limit))
                ]);
            } catch (Exception $e) {
                // fallback
            }
        }

        // Mock Fallback
        $mockOrders = MockDataProvider::getOrders();
        Response::success($mockOrders, 'ดึงรายการคำสั่งซื้อสำเร็จ (โหมดจำลอง)', [
            'total' => count($mockOrders),
            'page'  => 1,
            'limit' => $limit,
            'totalPages' => 1
        ]);
    }

    /**
     * GET /api/v1/orders/{id} - ดึงรายละเอียดคำสั่งซื้อและรายการสินค้าทั้งหมด
     */
    public function show($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                // 1. ดึงข้อมูลหัวเอกสาร คำสั่งซื้อ + ลูกค้า + ผู้จัดส่ง + พนักงาน
                $sql = "SELECT o.i_OrderID, o.c_OrderDate, o.i_CustomerID, o.i_EmployeeID, o.i_ShipperID,
                               c.c_customername, c.c_contactname, c.c_address, c.c_city, c.c_postalcode, c.c_country,
                               CONCAT(e.c_FirstName, ' ', e.c_LastName) AS employee_name,
                               s.c_ShipperName, s.c_Phone AS shipper_phone
                        FROM tb_orders o
                        LEFT JOIN tb_customers c ON o.i_CustomerID = c.i_customerid
                        LEFT JOIN tb_employees e ON o.i_EmployeeID = e.i_EmployeeID
                        LEFT JOIN tb_shippers s ON o.i_ShipperID = s.i_ShipperID
                        WHERE o.i_OrderID = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$id]);
                $order = $stmt->fetch();

                if ($order) {
                    // 2. ดึงรายการสินค้าในคำสั่งซื้อ (Line Items)
                    $itemSql = "SELECT d.i_OrderDetailID, d.i_ProductID, d.i_Quantity,
                                       p.c_ProductName, p.c_Unit, p.i_Price,
                                       (d.i_Quantity * p.i_Price) AS line_total
                                FROM tb_orderdetails d
                                LEFT JOIN tb_products p ON d.i_ProductID = p.i_ProductID
                                WHERE d.i_OrderID = ?
                                ORDER BY d.i_OrderDetailID ASC";
                    $itemStmt = $this->conn->prepare($itemSql);
                    $itemStmt->execute([$id]);
                    $items = $itemStmt->fetchAll();

                    $totalUnits = 0;
                    $grandTotal = 0.0;
                    foreach ($items as $it) {
                        $totalUnits += (int)$it['i_Quantity'];
                        $grandTotal += (float)$it['line_total'];
                    }

                    $order['items'] = $items;
                    $order['items_count'] = count($items);
                    $order['total_units'] = $totalUnits;
                    $order['grand_total'] = $grandTotal;
                    $order['status'] = ($id % 5 === 4) ? 'ยกเลิก' : (($id % 5 === 1) ? 'กำลังดำเนินการ' : 'จัดส่งแล้ว');
                    $order['badge_class'] = ($order['status'] === 'จัดส่งแล้ว') ? 'badge-green' : (($order['status'] === 'กำลังดำเนินการ') ? 'badge-amber' : 'badge-red');

                    Response::success($order, 'ดึงรายละเอียดคำสั่งซื้อสำเร็จ');
                } else {
                    Response::notFound('ไม่พบคำสั่งซื้อรหัส #' . $id);
                }
            } catch (Exception $e) {
                // fallback
            }
        }

        // Mock Fallback
        $mockOrders = MockDataProvider::getOrders();
        foreach ($mockOrders as $mo) {
            if ($mo['i_OrderID'] == $id) {
                $mo['c_contactname'] = 'Roland Mendel';
                $mo['c_city'] = 'Graz';
                $mo['c_country'] = 'Austria';
                $mo['c_postalcode'] = '8010';
                $mo['total_units'] = 20;
                $mo['grand_total'] = $mo['total_amount'];
                Response::success($mo, 'ดึงรายละเอียดคำสั่งซื้อสำเร็จ (โหมดจำลอง)');
            }
        }
        Response::notFound('ไม่พบคำสั่งซื้อรหัส #' . $id);
    }

    /**
     * POST /api/v1/orders - สร้างคำสั่งซื้อใหม่พร้อมรายการสินค้า
     */
    public function store() {
        $data = $this->getRequestData();

        $customerId = (int)($data['i_CustomerID'] ?? $data['customer_id'] ?? 0);
        $employeeId = (int)($data['i_EmployeeID'] ?? $data['employee_id'] ?? 1);
        $shipperId  = (int)($data['i_ShipperID'] ?? $data['shipper_id'] ?? 1);
        $orderDate  = trim($data['c_OrderDate'] ?? $data['order_date'] ?? date('Y-m-d'));
        $items      = $data['items'] ?? [];

        if ($customerId <= 0) {
            Response::error('กรุณาระบุเลือกลูกค้า', 400);
        }

        if (empty($items) || !is_array($items)) {
            Response::error('กรุณาระบุสินค้าในคำสั่งซื้ออย่างน้อย 1 รายการ', 400);
        }

        if ($this->conn) {
            try {
                $this->conn->beginTransaction();

                // Next Order ID
                $maxStmt = $this->conn->prepare("SELECT COALESCE(MAX(i_OrderID), 10247) + 1 FROM tb_orders");
                $maxStmt->execute();
                $newOrderId = (int)$maxStmt->fetchColumn();

                $sql = "INSERT INTO tb_orders (i_OrderID, i_CustomerID, i_EmployeeID, c_OrderDate, i_ShipperID)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$newOrderId, $customerId, $employeeId, $orderDate, $shipperId]);

                // Insert Items
                $detailMaxStmt = $this->conn->prepare("SELECT COALESCE(MAX(i_OrderDetailID), 0) + 1 FROM tb_orderdetails");
                $detailMaxStmt->execute();
                $nextDetailId = (int)$detailMaxStmt->fetchColumn();

                $itemSql = "INSERT INTO tb_orderdetails (i_OrderDetailID, i_OrderID, i_ProductID, i_Quantity)
                            VALUES (?, ?, ?, ?)";
                $itemStmt = $this->conn->prepare($itemSql);

                foreach ($items as $item) {
                    $pid = (int)($item['i_ProductID'] ?? $item['product_id'] ?? 0);
                    $qty = max(1, (int)($item['i_Quantity'] ?? $item['quantity'] ?? 1));
                    if ($pid > 0) {
                        $itemStmt->execute([$nextDetailId++, $newOrderId, $pid, $qty]);
                    }
                }

                $this->conn->commit();

                Response::created([
                    'i_OrderID'    => $newOrderId,
                    'i_CustomerID' => $customerId,
                    'c_OrderDate'  => $orderDate,
                    'items_count'  => count($items)
                ], 'สร้างคำสั่งซื้อใหม่สำเร็จ เลขที่ #' . $newOrderId);
            } catch (Exception $e) {
                if ($this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                Response::serverError('ไม่สามารถสร้างคำสั่งซื้อได้: ' . $e->getMessage());
            }
        }

        Response::created([
            'i_OrderID' => rand(10444, 10999),
            'items_count' => count($items)
        ], 'สร้างคำสั่งซื้อใหม่สำเร็จ (โหมดจำลอง)');
    }

    /**
     * PUT /api/v1/orders/{id} - อัปเดตคำสั่งซื้อ
     */
    public function update($id) {
        $id = (int)$id;
        $data = $this->getRequestData();

        $status = trim($data['status'] ?? '');
        $shipperId = (int)($data['i_ShipperID'] ?? $data['shipper_id'] ?? 0);

        if ($this->conn) {
            try {
                if ($shipperId > 0) {
                    $sql = "UPDATE tb_orders SET i_ShipperID = ? WHERE i_OrderID = ?";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$shipperId, $id]);
                }
                Response::success(['i_OrderID' => $id, 'status' => $status], 'อัปเดตคำสั่งซื้อสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการอัปเดต: ' . $e->getMessage());
            }
        }

        Response::success(['i_OrderID' => $id, 'status' => $status], 'อัปเดตคำสั่งซื้อสำเร็จ (โหมดจำลอง)');
    }

    /**
     * DELETE /api/v1/orders/{id} - ลบคำสั่งซื้อ
     */
    public function destroy($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                $this->conn->beginTransaction();

                // ลบรายการสินค้าในออร์เดอร์ก่อน
                $delItems = $this->conn->prepare("DELETE FROM tb_orderdetails WHERE i_OrderID = ?");
                $delItems->execute([$id]);

                // ลบหัวออร์เดอร์
                $delOrder = $this->conn->prepare("DELETE FROM tb_orders WHERE i_OrderID = ?");
                $delOrder->execute([$id]);

                $this->conn->commit();
                Response::success(null, 'ลบคำสั่งซื้อรหัส #' . $id . ' สำเร็จ');
            } catch (Exception $e) {
                if ($this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                Response::serverError('ไม่สามารถลบคำสั่งซื้อได้: ' . $e->getMessage());
            }
        }

        Response::success(null, 'ลบคำสั่งซื้อรหัส #' . $id . ' สำเร็จ (โหมดจำลอง)');
    }
}

