<?php
/**
 * ReportController.php - รายงานและวิเคราะห์ข้อมูลธุรกิจ
 * ข้อกำหนด 12: รายงาน (Reports · Chart · Export)
 */

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Database.php';

class ReportController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * GET /api/v1/reports/summary
     */
    public function summary() {
        $year = trim($_GET['year'] ?? 'all');

        $kpis = [
            'total_revenue'   => '฿2,450,800.00',
            'revenue_raw'     => 2450800,
            'total_orders'    => 196,
            'avg_order_value' => '฿1,250.40',
            'top_shipper'     => 'United Package',
            'top_shipper_trips' => 74
        ];

        // กราฟแนวโน้มยอดขายรายเดือน
        $monthlyTrend = [
            ['month' => 'เม.ย.', 'sales' => 145000, 'height' => 30],
            ['month' => 'พ.ค.', 'sales' => 195000, 'height' => 45],
            ['month' => 'มิ.ย.', 'sales' => 220000, 'height' => 55],
            ['month' => 'ก.ค.', 'sales' => 280000, 'height' => 70],
            ['month' => 'ส.ค.', 'sales' => 260000, 'height' => 65],
            ['month' => 'ก.ย.', 'sales' => 310000, 'height' => 80],
            ['month' => 'ต.ค.', 'sales' => 345000, 'height' => 90],
            ['month' => 'พ.ย.', 'sales' => 390000, 'height' => 100],
            ['month' => 'ธ.ค.', 'sales' => 305800, 'height' => 85]
        ];

        // 5 สินค้าขายดีสูงสุด
        $topProducts = [
            ['rank' => 1, 'name' => 'ชาไทย (Chai)', 'units' => 324, 'revenue' => 5832, 'percent' => 90],
            ['rank' => 2, 'name' => 'น้ำซุปชาม (Chang)', 'units' => 290, 'revenue' => 5510, 'percent' => 80],
            ['rank' => 3, 'name' => 'แมนจิมัป (Manjimup)', 'units' => 218, 'revenue' => 11554, 'percent' => 60],
            ['rank' => 4, 'name' => 'ลูกอมผลไม้ (Pavlova)', 'units' => 198, 'revenue' => 7722, 'percent' => 54],
            ['rank' => 5, 'name' => 'แซนด์วิชฟิงเกอร์ (Nord-Ost)', 'units' => 156, 'revenue' => 5187, 'percent' => 43],
        ];

        // 5 ลูกค้าที่มียอดสั่งซื้อสูงสุด
        $topCustomers = [
            ['rank' => '🥇', 'company' => 'Ernst Handel', 'country' => '🇦🇹 ออสเตรีย', 'orders' => 10, 'percent' => 90],
            ['rank' => '🥈', 'company' => 'Save-a-lot Markets', 'country' => '🇺🇸 สหรัฐอเมริกา', 'orders' => 9, 'percent' => 81],
            ['rank' => '🥉', 'company' => 'QUICK-Stop', 'country' => '🇩🇪 เยอรมนี', 'orders' => 8, 'percent' => 72],
            ['rank' => '4',  'company' => 'Rattlesnake Canyon Grocery', 'country' => '🇺🇸 สหรัฐอเมริกา', 'orders' => 7, 'percent' => 63],
            ['rank' => '5',  'company' => 'Hungry Owl All-Night Grocers', 'country' => '🇮🇪 ไอร์แลนด์', 'orders' => 6, 'percent' => 54],
        ];

        // ยอดขายแบ่งตามพนักงาน
        $salesByEmployee = [
            ['employee' => 'Margaret Peacock (มาร์กาเร็ต พีค็อก)', 'orders' => 40, 'sales' => '฿580,000'],
            ['employee' => 'Janet Leverling (แจเน็ต เลฟเวอร์ลิง)', 'orders' => 31, 'sales' => '฿440,000'],
            ['employee' => 'Nancy Davolio (แนนซี่ ดาโวลิโอ)', 'orders' => 29, 'sales' => '฿395,000'],
            ['employee' => 'Andrew Fuller (แอนดรูว์ ฟูลเลอร์)', 'orders' => 26, 'sales' => '฿370,000'],
            ['employee' => 'Laura Callahan (ลอร่า คัลลาแฮน)', 'orders' => 20, 'sales' => '฿280,000'],
        ];

        Response::success([
            'year'              => $year,
            'kpis'              => $kpis,
            'monthly_trend'     => $monthlyTrend,
            'top_products'      => $topProducts,
            'top_customers'     => $topCustomers,
            'sales_by_employee' => $salesByEmployee
        ], 'ดึงข้อมูลรายงานและวิเคราะห์สำเร็จ');
    }

    /**
     * GET /api/v1/reports/export - นำออกข้อมูลรายงานในรูปแบบ CSV
     */
    public function exportCsv() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=northwind_report_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM สำหรับเปิดใน Excel ภาษาไทยไม่เพี้ยน
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['รายงานสรุปยอดขาย Northwind Business Management System']);
        fputcsv($output, ['วันที่สร้างรายงาน', date('Y-m-d H:i:s')]);
        fputcsv($output, []);

        fputcsv($output, ['อันดับ', 'ชื่อสินค้าขายดี', 'จำนวนหน่วยที่ขายได้ (หน่วย)', 'ยอดขายรวม (บาท)']);
        fputcsv($output, [1, 'ชาไทย (Chai)', 324, 5832]);
        fputcsv($output, [2, 'น้ำซุปชาม (Chang)', 290, 5510]);
        fputcsv($output, [3, 'แมนจิมัป (Manjimup)', 218, 11554]);
        fputcsv($output, [4, 'ลูกอมผลไม้ (Pavlova)', 198, 7722]);
        fputcsv($output, [5, 'แซนด์วิชฟิงเกอร์ (Nord-Ost)', 156, 5187]);

        fputcsv($output, []);
        fputcsv($output, ['อันดับ', 'ลูกค้าชั้นนำ', 'ประเทศ', 'จำนวนคำสั่งซื้อ']);
        fputcsv($output, [1, 'Ernst Handel', 'Austria', 10]);
        fputcsv($output, [2, 'Save-a-lot Markets', 'USA', 9]);
        fputcsv($output, [3, 'QUICK-Stop', 'Germany', 8]);
        fputcsv($output, [4, 'Rattlesnake Canyon Grocery', 'USA', 7]);
        fputcsv($output, [5, 'Hungry Owl All-Night Grocers', 'Ireland', 6]);

        fclose($output);
        exit;
    }
}

