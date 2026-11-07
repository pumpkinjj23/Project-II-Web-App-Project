<?php
/**
 * JWT.php - JSON Web Token (HS256) Generator and Validator
 * ใช้สำหรับการรักษาความปลอดภัยตามข้อกำหนด 01: Login · JWT Auth
 */

class JWT {
    private static $secretKey = 'Northwind-BMS-Secure-Secret-Key-CPE66-2026';

    /**
     * ตั้งค่า Secret Key
     */
    public static function setSecret($key) {
        self::$secretKey = $key;
    }

    /**
     * Base64Url Encode
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64Url Decode
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * สร้าง JWT Token
     *
     * @param array $payload ข้อมูลที่ต้องการบรรจุใน Token (เช่น user_id, email, role)
     * @param int $expirySeconds อายุของ Token ในหน่วยวินาที (ค่าเริ่มต้น 24 ชั่วโมง = 86400 วินาที)
     * @return string JWT Token
     */
    public static function generate(array $payload, $expirySeconds = 86400) {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $issuedAt = time();
        $expire = $issuedAt + $expirySeconds;

        $payload['iat'] = $issuedAt;
        $payload['exp'] = $expire;
        $payload['iss'] = 'Northwind-BMS';

        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * ตรวจสอบความถูกต้องของ JWT Token
     *
     * @param string $token JWT Token
     * @return array|false คืนค่า Payload หาก Token ถูกต้อง หรือ false หากไม่ถูกต้องหรือหมดอายุ
     */
    public static function verify($token) {
        if (empty($token)) return false;

        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($header64, $payload64, $sig64) = $parts;

        $expectedSig = self::base64UrlEncode(hash_hmac('sha256', $header64 . "." . $payload64, self::$secretKey, true));

        // ตรวจสอบ Signature ด้วย hash_equals ป้องกัน Timing Attack
        if (!hash_equals($expectedSig, $sig64)) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($payload64), true);
        if (!is_array($payload)) return false;

        // ตรวจสอบอายุ Token
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false; // หมดอายุ
        }

        return $payload;
    }

    /**
     * ดึง Bearer Token จาก HTTP Authorization Header
     */
    public static function getBearerToken() {
        $headers = '';
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }

        // อนุญาตให้อ่านจาก Query String 'token' สำรอง
        if (isset($_GET['token']) && !empty($_GET['token'])) {
            return $_GET['token'];
        }

        return null;
    }

    /**
     * Middleware สำหรับตรวจสิทธิ์ใน API (Protect Route)
     */
    public static function requireAuth() {
        $token = self::getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'status'  => 401,
                'message' => 'Unauthorized: กรุณาแนบ JWT Token ใน Authorization Header (Bearer {token})'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $decoded = self::verify($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'status'  => 401,
                'message' => 'Unauthorized: Token ไม่ถูกต้อง หรือหมดอายุแล้ว กรุณาเข้าสู่ระบบใหม่'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return $decoded;
    }
}

