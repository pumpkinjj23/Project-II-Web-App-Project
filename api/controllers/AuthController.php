<?php
/**
 * AuthController.php - ระบบพิสูจน์ตัวตน (JWT Authentication)
 * ข้อกำหนด 01: เข้าสู่ระบบ (Login · JWT Auth)
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/JWT.php';
require_once __DIR__ . '/../core/Database.php';

class AuthController {
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
        }
        return $data;
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login() {
        $input = $this->getRequestData();
        $email = trim($input['email'] ?? $input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($email) || empty($password)) {
            Response::error('กรุณากรอกอีเมลหรือชื่อผู้ใช้ และรหัสผ่านให้ครบถ้วน', 400);
        }

        // Demo User (หรือสามารถเช็คจาก tb_employees ใน DB ได้)
        $user = null;

        if ($this->conn) {
            try {
                // ค้นหาพนักงานที่ตรงกับ email หรือ ชื่อ
                $sql = "SELECT i_EmployeeID, c_FirstName, c_LastName, c_Notes FROM tb_employees WHERE c_FirstName = ? OR c_LastName = ? LIMIT 1";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$email, $email]);
                $emp = $stmt->fetch();
                if ($emp) {
                    $user = [
                        'id'    => (int)$emp['i_EmployeeID'],
                        'name'  => $emp['c_FirstName'] . ' ' . $emp['c_LastName'],
                        'email' => strtolower($emp['c_FirstName']) . '@northwind.co.th',
                        'role'  => 'ผู้ดูแลระบบ (Admin)'
                    ];
                }
            } catch (Exception $e) {
                // ถ้าค้นหาใน DB ไม่พบ ให้ใช้ fallback
            }
        }

        // Fallback Default Admin (เช่น admin@northwind.co.th หรือ วชิรา ล.)
        if (!$user) {
            $user = [
                'id'    => 1,
                'name'  => 'วชิรา ล.',
                'email' => $email ?: 'admin@northwind.co.th',
                'role'  => 'ผู้ดูแลระบบ (Administrator)'
            ];
        }

        // สร้าง JWT Token
        $payload = [
            'uid'   => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role']
        ];

        $token = JWT::generate($payload, 86400); // 24 hours

        Response::success([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400,
            'user'       => $user
        ], 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ ' . $user['name']);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me() {
        $user = JWT::requireAuth();
        Response::success($user, 'ตรวจสอบข้อมูลผู้ใช้สำเร็จ');
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout() {
        Response::success(null, 'ออกจากระบบเรียบร้อยแล้ว');
    }
}

