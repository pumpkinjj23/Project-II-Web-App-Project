<?php
/**
 * setup_db.php - Northwind Database Auto-Installer & Diagnostic Tool
 * ช่วยนำเข้าไฟล์ dbNorthwind.sql และตรวจสอบสถานะฐานข้อมูลทั้ง 8 ตาราง
 */

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/inc/Database.php';

$db = new Database();
$conn = $db->getConnection();
$config = $db->getConnectionInfo();
$error = $db->getLastError();

$message = '';
$messageType = '';

// หากมีการกดปุ่มนำเข้าฐานข้อมูล
if (isset($_POST['action']) && $_POST['action'] === 'import') {
    if ($conn) {
        $sqlFile = __DIR__ . '/dbNorthwind.sql';
        if (file_exists($sqlFile)) {
            try {
                $sqlContent = file_get_contents($sqlFile);
                // แยก query ตาม semicolon
                $conn->exec($sqlContent);
                $message = "นำเข้าฐานข้อมูลสำเร็จเรียบร้อยแล้ว!";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "เกิดข้อผิดพลาดในการนำเข้า: " . $e->getMessage();
                $messageType = "danger";
            }
        } else {
            $message = "ไม่พบไฟล์ dbNorthwind.sql";
            $messageType = "warning";
        }
    } else {
        $message = "ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาเปิดโปรแกรม MAMP หรือ XAMPP ก่อน";
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Northwind Database Diagnostic & Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 14px; }
        .badge-table { background: #3b82f6; font-size: 13px; padding: 6px 12px; border-radius: 8px; }
    </style>
</head>
<body>
<div class="container" style="max-width: 900px;">
    <div class="card p-4 shadow-lg">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:#2563eb;border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;">NW</div>
                <div>
                    <h3 class="fw-bold mb-0 text-white">เครื่องมือจัดการฐานข้อมูล Northwind (CPE66)</h3>
                    <p class="text-secondary mb-0 small">PDO OOP Database Connection & Schema Diagnostic</p>
                </div>
            </div>
            <a href="index.html" class="btn btn-outline-light">🚀 เปิดแอปพลิเคชัน</a>
        </div>
        <hr class="border-secondary">

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary">
                    <div class="text-secondary small">สถานะการเชื่อมต่อ MySQL</div>
                    <div class="fs-5 fw-bold mt-1">
                        <?php if ($conn): ?>
                            <span class="text-success">✅ เชื่อมต่อสำเร็จ</span>
                        <?php else: ?>
                            <span class="text-danger">❌ ยังไม่ได้เชื่อมต่อ</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary">
                    <div class="text-secondary small">โฮสต์ / ฐานข้อมูล</div>
                    <div class="fs-6 fw-bold mt-1 text-info">
                        <?= $config ? htmlspecialchars($config['host'] . ':' . $config['port'] . ' (' . $config['db'] . ')') : 'N/A' ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($conn): ?>
            <h5 class="fw-bold mb-3 text-white">📋 ตรวจสอบ 8 ตารางหลักของ Northwind:</h5>
            <?php
            $targetTables = [
                'tb_products'     => 'สินค้า (Products)',
                'tb_categories'   => 'หมวดหมู่สินค้า (Categories)',
                'tb_customers'    => 'ลูกค้า (Customers)',
                'tb_orders'       => 'คำสั่งซื้อ (Orders)',
                'tb_orderdetails' => 'รายละเอียดคำสั่งซื้อ (Order Details)',
                'tb_suppliers'    => 'ผู้จัดจำหน่าย (Suppliers)',
                'tb_employees'    => 'พนักงาน (Employees)',
                'tb_shippers'     => 'บริษัทขนส่ง (Shippers)'
            ];

            $existingTables = [];
            try {
                $existingTables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e) {}

            echo "<div class='table-responsive'><table class='table table-dark table-bordered align-middle'>";
            echo "<thead><tr><th>ชื่อตาราง</th><th>รายละเอียด</th><th>สถานะในฐานข้อมูล</th><th>จำนวนแถว (Rows)</th></tr></thead><tbody>";

            $allExist = true;
            foreach ($targetTables as $tbl => $label) {
                $exists = in_array(strtolower($tbl), array_map('strtolower', $existingTables));
                if (!$exists) $allExist = false;

                $rowCount = 0;
                if ($exists) {
                    try {
                        $rowCount = (int)$conn->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
                    } catch (Exception $e) {}
                }

                echo "<tr>";
                echo "<td><strong class='text-primary'>`{$tbl}`</strong></td>";
                echo "<td>{$label}</td>";
                echo "<td>" . ($exists ? "<span class='badge bg-success'>พบตารางใน DB ✅</span>" : "<span class='badge bg-danger'>ไม่พบตาราง ❌</span>") . "</td>";
                echo "<td><span class='badge bg-secondary fs-6'>{$rowCount} แถว</span></td>";
                echo "</tr>";
            }
            echo "</tbody></table></div>";
            ?>

            <div class="mt-4 p-3 rounded-3 bg-dark bg-opacity-25 border border-secondary d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1">นำเข้าหรือรีเซ็ตข้อมูลจาก dbNorthwind.sql</h6>
                    <p class="text-secondary small mb-0">หากต้องการติดตั้งฐานข้อมูลใหม่ทั้งหมดให้กดปุ่มนี้</p>
                </div>
                <form method="POST" onsubmit="return confirm('ต้องการนำเข้าฐานข้อมูล Northwind หรือไม่?');">
                    <input type="hidden" name="action" value="import">
                    <button type="submit" class="btn btn-warning fw-bold">📥 นำเข้าไฟล์ dbNorthwind.sql</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <h5 class="fw-bold">⚠️ ยังไม่สามารถเชื่อมต่อกับ MySQL Server</h5>
                <p><strong>ข้อความผิดพลาด:</strong> <?= htmlspecialchars($error ?: 'Connection failed') ?></p>
                <hr>
                <p class="mb-1"><strong>วิธีเปิดใช้งาน MySQL:</strong></p>
                <ol class="mb-0">
                    <li>เปิดโปรแกรม <strong>MAMP</strong> หรือ <strong>XAMPP</strong></li>
                    <li>กดปุ่ม <strong>"Start Servers"</strong> เพื่อเริ่ม MySQL Server</li>
                    <li>รีเฟรชหน้านี้อีกครั้ง หรือคลิก <a href="index.html" class="alert-link">เปิดแอปพลิเคชัน</a> ซึ่งมีระบบ Fallback Demo Data อัตโนมัติ</li>
                </ol>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

