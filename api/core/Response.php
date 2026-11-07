<?php
/**
 * Response.php - RESTful API JSON Response Handler
 * ตามข้อกำหนด Technology Stack และ API Doc (Slide 1, 4, 5)
 */

class Response {
    /**
     * ส่งข้อมูล JSON กลับไปยัง Client
     *
     * @param int $statusCode HTTP Status Code (200, 201, 400, 401, 404, 500)
     * @param mixed $data ข้อมูลที่ต้องการส่ง
     * @param string $message ข้อความตอบกลับ
     * @param bool $status สถานะความสำเร็จ (true / false)
     * @param array $extra ข้อมูลเพิ่มเติม เช่น pagination, total, count
     */
    public static function json($statusCode = 200, $data = null, $message = '', $status = true, array $extra = []) {
        if (ob_get_length()) ob_clean();

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        $response = [
            'status'  => $status,
            'success' => $status,
            'code'    => $statusCode
        ];

        if ($message !== '') {
            $response['message'] = $message;
        }

        foreach ($extra as $key => $val) {
            $response[$key] = $val;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function success($data = null, $message = 'ดำเนินการสำเร็จ', $extra = [], $statusCode = 200) {
        self::json($statusCode, $data, $message, true, $extra);
    }

    public static function created($data = null, $message = 'สร้างข้อมูลเรียบร้อยแล้ว') {
        self::json(201, $data, $message, true);
    }

    public static function error($message = 'เกิดข้อผิดพลาดในการประมวลผล', $statusCode = 400, $data = null) {
        self::json($statusCode, $data, $message, false);
    }

    public static function unauthorized($message = 'ไม่ได้รับอนุญาตให้เข้าถึง (Unauthorized)') {
        self::json(401, null, $message, false);
    }

    public static function notFound($message = 'ไม่พบข้อมูลที่ต้องการ (Not Found)') {
        self::json(404, null, $message, false);
    }

    public static function methodNotAllowed($message = 'Method Not Allowed') {
        self::json(405, null, $message, false);
    }

    public static function serverError($message = 'เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์ (Internal Server Error)') {
        self::json(500, null, $message, false);
    }
}

