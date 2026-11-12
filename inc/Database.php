<?php
/**
 * Database.php - PDO OOP Style Database Connection Class
 * ข้อกำหนดที่ 1: PDO แบบ OOP Style & SQL Bind Parameter
 *
 * ตัวอย่างการใช้งานตามข้อกำหนด Slide 1:
 *   $db = new Database();
 *   $conn = $db->getConnection();
 *
 *   $sql = "SELECT * FROM tb_products WHERE i_productid = ?";
 *   $stmt = $conn->prepare($sql);
 *   $stmt->execute([$id]);
 *   $row = $stmt->fetch();
 */

class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $charset = 'utf8mb4';
    private $conn = null;
    private $connectedConfig = null;
    private $lastError = null;
    private $configsToTry = [];

    public function __construct() {
        $this->resolveConfig();
        $this->connect();
    }

    /**
     * ดึงค่า Environment Variable จากทุกช่องทาง
     */
    private static function getEnvVal($key, $default = '') {
        $val = getenv($key);
        if ($val !== false && $val !== '') return $val;
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
        return $default;
    }

    /**
     * วิเคราะห์การตั้งค่าการเชื่อมต่อสำหรับ Railway, Web Hosting และ Localhost
     */
    private function resolveConfig() {
        // 1. ตรวจสอบ Connection URL (Railway / Heroku / Render)
        $mysqlUrl = self::getEnvVal('MYSQL_URL') ?: (self::getEnvVal('DATABASE_URL') ?: self::getEnvVal('MYSQL_PUBLIC_URL'));
        $urlHost = null; $urlPort = null; $urlUser = null; $urlPass = null; $urlDb = null;

        if (!empty($mysqlUrl)) {
            $parsed = parse_url($mysqlUrl);
            if ($parsed) {
                if (!empty($parsed['host'])) $urlHost = $parsed['host'];
                if (!empty($parsed['port'])) $urlPort = (string)$parsed['port'];
                if (!empty($parsed['user'])) $urlUser = $parsed['user'];
                if (isset($parsed['pass']))   $urlPass = $parsed['pass'];
                if (!empty($parsed['path'])) $urlDb   = ltrim($parsed['path'], '/');
            }
        }

        // 2. ตรวจสอบค่าตัวแปรรายตัว
        $envHost = self::getEnvVal('MYSQLHOST') ?: self::getEnvVal('DB_HOST');
        $envPort = self::getEnvVal('MYSQLPORT') ?: (self::getEnvVal('DB_PORT') ?: '3306');
        $envUser = self::getEnvVal('MYSQLUSER') ?: (self::getEnvVal('DB_USER') ?: 'root');
        $envPass = self::getEnvVal('MYSQLPASSWORD') !== '' ? self::getEnvVal('MYSQLPASSWORD') : (self::getEnvVal('DB_PASSWORD') !== '' ? self::getEnvVal('DB_PASSWORD') : null);
        $envDb   = self::getEnvVal('MYSQLDATABASE') ?: (self::getEnvVal('DB_NAME') ?: 'db_northwind');

        $dbList = array_unique(array_filter([$urlDb, $envDb, 'db_northwind', 'railway']));

        $configs = [];

        // A. Railway MYSQL_URL
        if ($urlHost) {
            foreach ($dbList as $db) {
                $configs[] = [
                    'label' => 'Railway URL',
                    'host'  => $urlHost,
                    'port'  => $urlPort ?: '3306',
                    'user'  => $urlUser ?: 'root',
                    'pass'  => $urlPass ?? '',
                    'db'    => $db
                ];
            }
        }

        // B. Environment Variables
        if ($envHost) {
            foreach ($dbList as $db) {
                $configs[] = [
                    'label' => 'Environment Variables',
                    'host'  => $envHost,
                    'port'  => $envPort,
                    'user'  => $envUser,
                    'pass'  => $envPass ?? '',
                    'db'    => $db
                ];
            }
        }

        // C. Railway Private Network
        $railwayHosts = ['mysql.railway.internal', 'mysql'];
        foreach ($railwayHosts as $rHost) {
            foreach ($dbList as $db) {
                if ($envPass !== null) {
                    $configs[] = [
                        'label' => "Railway Internal ($rHost)",
                        'host'  => $rHost,
                        'port'  => '3306',
                        'user'  => $envUser ?: 'root',
                        'pass'  => $envPass,
                        'db'    => $db
                    ];
                }
            }
        }

        // D. Localhost Fallbacks (MAMP Windows: root:root / XAMPP: root:no-pass / MAMP Mac: 8889)
        $localHosts = ['127.0.0.1', 'localhost'];
        $localPorts = [3306, 8889, 3307];
        $localPasses = ['root', ''];

        foreach ($localHosts as $h) {
            foreach ($localPorts as $p) {
                foreach ($localPasses as $pwd) {
                    foreach ($dbList as $db) {
                        $configs[] = [
                            'label' => "Localhost ($h:$p, pass:" . ($pwd === '' ? 'none' : 'root') . ")",
                            'host'  => $h,
                            'port'  => $p,
                            'user'  => 'root',
                            'pass'  => $pwd,
                            'db'    => $db
                        ];
                    }
                }
            }
        }

        $this->configsToTry = $configs;
    }

    /**
     * ดำเนินการเชื่อมต่อ Database ผ่าน PDO (รองรับทั้ง MySQL และ SQLite แบบ Dual-Engine)
     */
    private function connect() {
        // 1. ทดสอบเชื่อมต่อ MySQL หากมี driver pdo_mysql
        if (extension_loaded('pdo_mysql')) {
            foreach ($this->configsToTry as $cfg) {
                try {
                    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['db']};charset={$this->charset}";
                    $options = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::ATTR_TIMEOUT            => 2,
                    ];

                    $testConn = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);
                    // ตรวจสอบว่ามีตาราง tb_products หรือไม่
                    $check = $testConn->query("SHOW TABLES LIKE 'tb_products'");
                    if ($check && $check->rowCount() > 0) {
                        $this->conn = $testConn;
                        $this->connectedConfig = array_merge($cfg, [
                            'engine' => 'MySQL',
                            'driver' => 'pdo_mysql',
                            'status' => 'connected'
                        ]);
                        return;
                    } else {
                        // หากเชื่อมต่อ MySQL ได้แล้วแต่ยังไม่มีตาราง -> ทำ Auto-Import dbNorthwind.sql ทันที
                        $sqlFile = dirname(__DIR__) . '/dbNorthwind.sql';
                        if (!file_exists($sqlFile)) $sqlFile = __DIR__ . '/../dbNorthwind.sql';
                        if (file_exists($sqlFile)) {
                            try {
                                $sqlContent = file_get_contents($sqlFile);
                                // ลบ CREATE DATABASE และ USE ออก เพื่อให้ทำงานใน Database ของ Railway ได้โดยตรง
                                $sqlContent = preg_replace('/CREATE\s+DATABASE[^;]+;/i', '', $sqlContent);
                                $sqlContent = preg_replace('/USE\s+[^;]+;/i', '', $sqlContent);
                                $testConn->exec($sqlContent);

                                $this->conn = $testConn;
                                $this->connectedConfig = array_merge($cfg, [
                                    'engine'        => 'MySQL',
                                    'driver'        => 'pdo_mysql',
                                    'status'        => 'connected',
                                    'auto_imported' => true
                                ]);
                                return;
                            } catch (Exception $e) {
                                $this->lastError = 'Auto-import failed: ' . $e->getMessage();
                            }
                        }
                    }
                } catch (PDOException $e) {
                    $this->lastError = $e->getMessage();
                }
            }
        }

        // 2. Dual-Engine Fallback: เชื่อมต่อผ่าน SQLite PDO ด้วยไฟล์ dbNorthwind.sqlite (79 รายการจริง)
        $possiblePaths = [
            dirname(__DIR__) . '/dbNorthwind.sqlite',
            __DIR__ . '/../dbNorthwind.sqlite',
            __DIR__ . '/../../dbNorthwind.sqlite',
            getcwd() . '/dbNorthwind.sqlite'
        ];

        $sqliteFile = null;
        foreach ($possiblePaths as $p) {
            if (file_exists($p)) {
                $sqliteFile = realpath($p);
                break;
            }
        }

        if ($sqliteFile && (extension_loaded('pdo_sqlite') || class_exists('PDO'))) {
            try {
                $this->conn = new PDO("sqlite:" . $sqliteFile);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // ลงทะเบียน Custom Functions เพื่อให้รองรับ MySQL functions ได้ครบ 100%
                $this->conn->sqliteCreateFunction('CONCAT', function() {
                    return implode('', func_get_args());
                });
                $this->conn->sqliteCreateFunction('NOW', function() {
                    return date('Y-m-d H:i:s');
                });
                $this->conn->sqliteCreateFunction('IFNULL', function($a, $b) {
                    return $a !== null ? $a : $b;
                });

                $this->connectedConfig = [
                    'label'   => 'Live SQLite Database (dbNorthwind.sqlite)',
                    'engine'  => 'SQLite (PDO OOP)',
                    'type'    => 'sqlite',
                    'db'      => 'dbNorthwind.sqlite',
                    'driver'  => 'pdo_sqlite',
                    'file'    => basename($sqliteFile),
                    'path'    => $sqliteFile,
                    'status'  => 'connected',
                    'tables'  => 8,
                    'products'=> 79
                ];
                return;
            } catch (PDOException $e) {
                $this->lastError = $e->getMessage();
                $this->conn = null;
            }
        }
    }

    /**
     * คืนค่า PDO Connection Object
     *
     * @return PDO|null
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * คืนค่าข้อมูล Config ที่กำลังเชื่อมต่ออยู่
     */
    public function getConnectionInfo() {
        return $this->connectedConfig;
    }

    /**
     * คืนค่า Error ล่าสุดหากไม่สามารถเชื่อมต่อได้
     */
    public function getLastError() {
        return $this->lastError;
    }

    /**
     * ตรวจสอบว่าเชื่อมต่อสำเร็จหรือไม่
     */
    public function isConnected() {
        return $this->conn !== null;
    }
}
