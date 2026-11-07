<?php
/**
 * api/index.php - RESTful API Front Controller (Northwind BMS)
 * สถาปัตยกรรม RESTful API ตามข้อกำหนด Slide 1, 2, 4, 5
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/JWT.php';

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/CategoryController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/CustomerController.php';
require_once __DIR__ . '/controllers/SupplierController.php';
require_once __DIR__ . '/controllers/EmployeeController.php';
require_once __DIR__ . '/controllers/ShipperController.php';
require_once __DIR__ . '/controllers/ReportController.php';

$router = new Router();

// ==========================================
// 1. Authentication Endpoints (ข้อกำหนด 01)
// ==========================================
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/auth/me', [AuthController::class, 'me']);
$router->post('/auth/logout', [AuthController::class, 'logout']);

// ==========================================
// 2. Dashboard Endpoints (ข้อกำหนด 02)
// ==========================================
$router->get('/dashboard/stats', [DashboardController::class, 'stats']);

// ==========================================
// 3. Products Endpoints (ข้อกำหนด 03, 04)
// ==========================================
$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/{id}', [ProductController::class, 'show']);
$router->post('/products', [ProductController::class, 'store']);
$router->put('/products/{id}', [ProductController::class, 'update']);
$router->post('/products/{id}', [ProductController::class, 'update']);
$router->delete('/products/{id}', [ProductController::class, 'destroy']);
$router->post('/products/delete/{id}', [ProductController::class, 'destroy']);

// ==========================================
// 4. Categories Endpoints (ข้อกำหนด 05)
// ==========================================
$router->get('/categories', [CategoryController::class, 'index']);
$router->get('/categories/{id}', [CategoryController::class, 'show']);
$router->post('/categories', [CategoryController::class, 'store']);
$router->put('/categories/{id}', [CategoryController::class, 'update']);
$router->delete('/categories/{id}', [CategoryController::class, 'destroy']);
$router->post('/categories/delete/{id}', [CategoryController::class, 'destroy']);

// ==========================================
// 5. Orders & Details Endpoints (ข้อกำหนด 06, 07)
// ==========================================
$router->get('/orders', [OrderController::class, 'index']);
$router->get('/orders/{id}', [OrderController::class, 'show']);
$router->post('/orders', [OrderController::class, 'store']);
$router->put('/orders/{id}', [OrderController::class, 'update']);
$router->delete('/orders/{id}', [OrderController::class, 'destroy']);
$router->post('/orders/delete/{id}', [OrderController::class, 'destroy']);

// ==========================================
// 6. Customers Endpoints (ข้อกำหนด 08)
// ==========================================
$router->get('/customers', [CustomerController::class, 'index']);
$router->get('/customers/{id}', [CustomerController::class, 'show']);
$router->post('/customers', [CustomerController::class, 'store']);
$router->put('/customers/{id}', [CustomerController::class, 'update']);
$router->delete('/customers/{id}', [CustomerController::class, 'destroy']);
$router->post('/customers/delete/{id}', [CustomerController::class, 'destroy']);

// ==========================================
// 7. Suppliers Endpoints (ข้อกำหนด 09)
// ==========================================
$router->get('/suppliers', [SupplierController::class, 'index']);
$router->get('/suppliers/{id}', [SupplierController::class, 'show']);
$router->post('/suppliers', [SupplierController::class, 'store']);
$router->put('/suppliers/{id}', [SupplierController::class, 'update']);
$router->delete('/suppliers/{id}', [SupplierController::class, 'destroy']);
$router->post('/suppliers/delete/{id}', [SupplierController::class, 'destroy']);

// ==========================================
// 8. Employees Endpoints (ข้อกำหนด 10)
// ==========================================
$router->get('/employees', [EmployeeController::class, 'index']);
$router->get('/employees/{id}', [EmployeeController::class, 'show']);
$router->post('/employees', [EmployeeController::class, 'store']);
$router->put('/employees/{id}', [EmployeeController::class, 'update']);
$router->delete('/employees/{id}', [EmployeeController::class, 'destroy']);
$router->post('/employees/delete/{id}', [EmployeeController::class, 'destroy']);

// ==========================================
// 9. Shippers Endpoints (ข้อกำหนด 11)
// ==========================================
$router->get('/shippers', [ShipperController::class, 'index']);
$router->get('/shippers/{id}', [ShipperController::class, 'show']);
$router->post('/shippers', [ShipperController::class, 'store']);
$router->put('/shippers/{id}', [ShipperController::class, 'update']);
$router->delete('/shippers/{id}', [ShipperController::class, 'destroy']);
$router->post('/shippers/delete/{id}', [ShipperController::class, 'destroy']);

// ==========================================
// 10. Reports Endpoints (ข้อกำหนด 12)
// ==========================================
$router->get('/reports/summary', [ReportController::class, 'summary']);
$router->get('/reports/export', [ReportController::class, 'exportCsv']);

// ==========================================
// 11. System Health & Ping
// ==========================================
$router->get('/ping', function() {
    Response::success([
        'status'    => 'online',
        'api'       => 'Northwind BMS RESTful API v1.0',
        'timestamp' => date('Y-m-d H:i:s')
    ], 'API Server is running normally');
});

$router->get('/health', function() {
    $db = new Database();
    $conn = $db->getConnection();
    $connected = $db->isConnected();
    $tables = [];

    if ($connected) {
        try {
            $stmt = $conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {}
    }

    Response::success([
        'api_status'       => 'online',
        'database_status'  => $connected ? 'connected' : 'offline',
        'database_config'  => $db->getConnectionInfo(),
        'table_count'      => count($tables),
        'tables'           => $tables,
        'timestamp'        => date('Y-m-d H:i:s')
    ], 'System Diagnostic Report');
});

// เริ่มต้น Dispatch Request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);

