<?php
/**
 * EmployeeController.php - จัดการข้อมูลพนักงาน (tb_employees)
 * ข้อกำหนด 10: พนักงาน (Employees · CRUD)
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/MockDataProvider.php';

class EmployeeController {
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

    private static function getAvatarColor($id) {
        $colors = ['#2563EB', '#7C3AED', '#059669', '#D97706', '#DC2626', '#0891B2', '#65A30D', '#C026D3', '#9333EA', '#4F46E5'];
        return $colors[$id % count($colors)];
    }

    /**
     * GET /api/v1/employees
     */
    public function index() {
        $search = trim($_GET['search'] ?? $_GET['keyword'] ?? '');

        if ($this->conn) {
            try {
                $where = "1=1";
                $params = [];

                if ($search !== '') {
                    $where = "(c_FirstName LIKE ? OR c_LastName LIKE ? OR c_Notes LIKE ?)";
                    $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
                }

                $sql = "SELECT i_EmployeeID, c_LastName, c_FirstName, c_BirthDate, c_Photo, c_Notes
                        FROM tb_employees
                        WHERE {$where}
                        ORDER BY i_EmployeeID ASC";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute($params);
                $employees = $stmt->fetchAll();

                foreach ($employees as &$emp) {
                    $initials = strtoupper(substr($emp['c_FirstName'], 0, 1) . substr($emp['c_LastName'], 0, 1));
                    $emp['initials'] = $initials ?: 'EM';
                    $emp['avatar_bg'] = self::getAvatarColor($emp['i_EmployeeID']);
                    $emp['full_name'] = $emp['c_FirstName'] . ' ' . $emp['c_LastName'];
                    $emp['title'] = ($emp['i_EmployeeID'] == 2 || $emp['i_EmployeeID'] == 5) ? 'ผู้จัดการฝ่ายขาย' : 'พนักงานขาย';
                }

                Response::success($employees, 'ดึงข้อมูลพนักงานสำเร็จ', [
                    'total' => count($employees)
                ]);
            } catch (Exception $e) {
                // fallback
            }
        }

        $all = MockDataProvider::getEmployees();
        foreach ($all as &$emp) {
            $emp['initials'] = strtoupper(substr($emp['c_FirstName'], 0, 1) . substr($emp['c_LastName'], 0, 1));
            $emp['avatar_bg'] = self::getAvatarColor($emp['i_EmployeeID']);
            $emp['full_name'] = $emp['c_FirstName'] . ' ' . $emp['c_LastName'];
        }
        Response::success($all, 'ดึงข้อมูลพนักงานสำเร็จ (โหมดจำลอง)', [
            'total' => count($all)
        ]);
    }

    /**
     * GET /api/v1/employees/{id}
     */
    public function show($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare("SELECT * FROM tb_employees WHERE i_EmployeeID = ?");
                $stmt->execute([$id]);
                $emp = $stmt->fetch();

                if ($emp) {
                    $emp['initials'] = strtoupper(substr($emp['c_FirstName'], 0, 1) . substr($emp['c_LastName'], 0, 1));
                    $emp['avatar_bg'] = self::getAvatarColor($emp['i_EmployeeID']);
                    $emp['full_name'] = $emp['c_FirstName'] . ' ' . $emp['c_LastName'];
                    Response::success($emp, 'ดึงข้อมูลพนักงานสำเร็จ');
                } else {
                    Response::notFound('ไม่พบพนักงานรหัส #' . $id);
                }
            } catch (Exception $e) {
                // fallback
            }
        }

        $all = MockDataProvider::getEmployees();
        foreach ($all as $emp) {
            if ($emp['i_EmployeeID'] == $id) {
                $emp['initials'] = strtoupper(substr($emp['c_FirstName'], 0, 1) . substr($emp['c_LastName'], 0, 1));
                $emp['avatar_bg'] = self::getAvatarColor($emp['i_EmployeeID']);
                $emp['full_name'] = $emp['c_FirstName'] . ' ' . $emp['c_LastName'];
                Response::success($emp, 'ดึงข้อมูลพนักงานสำเร็จ');
            }
        }
        Response::notFound('ไม่พบพนักงานรหัส #' . $id);
    }

    /**
     * POST /api/v1/employees
     */
    public function store() {
        $data = $this->getRequestData();

        $first = trim($data['c_FirstName'] ?? $data['first_name'] ?? '');
        $last  = trim($data['c_LastName'] ?? $data['last_name'] ?? '');
        $birth = trim($data['c_BirthDate'] ?? $data['birth_date'] ?? date('Y-m-d'));
        $notes = trim($data['c_Notes'] ?? $data['notes'] ?? '');
        $photo = trim($data['c_Photo'] ?? 'EmpDefault.pic');

        if (empty($first) || empty($last)) {
            Response::error('กรุณาระบุชื่อและนามสกุลพนักงาน', 400);
        }

        if ($this->conn) {
            try {
                $maxStmt = $this->conn->prepare("SELECT COALESCE(MAX(i_EmployeeID), 0) + 1 FROM tb_employees");
                $maxStmt->execute();
                $nextId = (int)$maxStmt->fetchColumn();

                $sql = "INSERT INTO tb_employees (i_EmployeeID, c_LastName, c_FirstName, c_BirthDate, c_Photo, c_Notes)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$nextId, $last, $first, $birth, $photo, $notes]);

                Response::created(['i_EmployeeID' => $nextId, 'full_name' => "{$first} {$last}"], 'เพิ่มพนักงานใหม่สำเร็จ');
            } catch (Exception $e) {
                Response::serverError('ไม่สามารถเพิ่มพนักงานได้: ' . $e->getMessage());
            }
        }

        Response::created(['i_EmployeeID' => rand(11, 99)], 'เพิ่มพนักงานใหม่สำเร็จ (โหมดจำลอง)');
    }

    /**
     * PUT /api/v1/employees/{id}
     */
    public function update($id) {
        $id = (int)$id;
        $data = $this->getRequestData();

        $first = trim($data['c_FirstName'] ?? $data['first_name'] ?? '');
        $last  = trim($data['c_LastName'] ?? $data['last_name'] ?? '');
        $birth = trim($data['c_BirthDate'] ?? $data['birth_date'] ?? '');
        $notes = trim($data['c_Notes'] ?? $data['notes'] ?? '');

        if (empty($first) || empty($last)) {
            Response::error('กรุณาระบุชื่อและนามสกุลพนักงาน', 400);
        }

        if ($this->conn) {
            try {
                $sql = "UPDATE tb_employees
                        SET c_LastName = ?, c_FirstName = ?, c_BirthDate = ?, c_Notes = ?
                        WHERE i_EmployeeID = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$last, $first, $birth, $notes, $id]);

                Response::success(['i_EmployeeID' => $id], 'อัปเดตข้อมูลพนักงานสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการอัปเดต: ' . $e->getMessage());
            }
        }

        Response::success(['i_EmployeeID' => $id], 'อัปเดตข้อมูลพนักงานสำเร็จ (โหมดจำลอง)');
    }

    /**
     * DELETE /api/v1/employees/{id}
     */
    public function destroy($id) {
        $id = (int)$id;

        if ($this->conn) {
            try {
                $chk = $this->conn->prepare("SELECT COUNT(*) FROM tb_orders WHERE i_EmployeeID = ?");
                $chk->execute([$id]);
                $count = (int)$chk->fetchColumn();

                if ($count > 0) {
                    Response::error("ไม่สามารถลบพนักงานนี้ได้เนื่องจากมีคำสั่งซื้อที่ดูแลอยู่ {$count} รายการ", 400);
                }

                $del = $this->conn->prepare("DELETE FROM tb_employees WHERE i_EmployeeID = ?");
                $del->execute([$id]);

                Response::success(null, 'ลบพนักงานสำเร็จ');
            } catch (Exception $e) {
                Response::serverError('เกิดข้อผิดพลาดในการลบ: ' . $e->getMessage());
            }
        }

        Response::success(null, 'ลบพนักงานสำเร็จ (โหมดจำลอง)');
    }
}

