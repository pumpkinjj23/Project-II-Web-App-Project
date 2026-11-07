# 🛒 Northwind BMS — Business Management System (Full Web Application)
### รายวิชาโครงงานเว็บแอปพลิเคชัน (Project-II Web App Full) | CPE66

ระบบเว็บแอปพลิเคชันจัดการข้อมูลธุรกิจ Northwind ครบวงจร พัฒนาตามข้อกำหนดทั้ง 4 ข้อในสไลด์อย่างครบถ้วน 100%

---

## 📋 สรุปความสอดคล้องตามข้อกำหนด 4 ด้านในสไลด์

### 1. ข้อกำหนดที่ 1: Technology Stack
- ✅ **Front-End**: Bootstrap 5 Framework + Custom Theme Design Tokens (รองรับ Responsive ทุกขนาดหน้าจอ Desktop / Tablet / Mobile)
- ✅ **Back-End**: PHP 8.2+ RESTful API Architecture แบ่ง Controller, Core Router, Response และ JWT ชัดเจน
- ✅ **Database Connection**: PDO แบบ OOP Style ผ่านคลาส `Database` (`$db = new Database(); $conn = $db->getConnection();`)
- ✅ **SQL Security**: ใช้ SQL Prepared Statements และ Parameter Binding (`$stmt = $conn->prepare($sql); $stmt->execute([...]);`) ป้องกัน SQL Injection 100%

### 2. ข้อกำหนดที่ 2: หน้าจอที่ต้องพัฒนา (ครบทั้ง 12 หน้าจอ)
| ลำดับ | หน้าจอ | รายละเอียดการทำงาน |
|---|---|---|
| **01** | **เข้าสู่ระบบ** | Login Form พร้อมระบบความปลอดภัยด้วย JWT Authentication (HS256) |
| **02** | **แดชบอร์ด** | 4 KPI Cards (คำสั่งซื้อ, สินค้า, ลูกค้า, ผู้จัดจำหน่าย), กราฟยอดขายรายเดือน, หมวดหมู่ยอดนิยม, คำสั่งซื้อล่าสุด |
| **03** | **รายการสินค้า** | ค้นหาชื่อ/รหัสสินค้า, กรองตามหมวดหมู่และผู้จัดจำหน่าย, ตารางสินค้าพร้อมสถานะ Badge และ Pagination |
| **04** | **เพิ่ม/แก้ไขสินค้า** | Form พร้อม Validation ทั้งฝั่ง Client และ Server, ดึง Dropdown จาก API, แจ้งเตือนสินค้าราคา > ฿10,000 |
| **05** | **หมวดหมู่สินค้า** | Grid Cards 9 หมวดหมู่สินค้า พร้อมไอคอน, คำอธิบาย, จำนวนสินค้า และระบบ CRUD |
| **06** | **คำสั่งซื้อ** | รายการคำสั่งซื้อ 196 รายการ พร้อมตัวกรองสถานะ (จัดส่งแล้ว, กำลังดำเนินการ, ยกเลิก) |
| **07** | **รายละเอียดคำสั่งซื้อ** | ข้อมูลลูกค้า, ข้อมูลขนส่ง, ตารางรายการสินค้า (Line Items), ยอดรวมสุทธิ และปุ่มพิมพ์ใบเสร็จ |
| **08** | **ลูกค้า** | ทะเบียนลูกค้า 91 รายการจาก 21 ประเทศ พร้อมตัวกรองประเทศ และระบบ CRUD |
| **09** | **ผู้จัดจำหน่าย** | ทะเบียนผู้จัดจำหน่าย 29 บริษัท พร้อมเบอร์โทรและจำนวนสินค้า |
| **10** | **พนักงาน** | การ์ดข้อมูลพนักงาน 10 คน พร้อมรูป Avatar ตัวอักษรสีเฉพาะตัว, ตำแหน่ง และประวัติ |
| **11** | **บริษัทขนส่ง** | การ์ดบริษัทขนส่ง 3 บริษัท (Speedy Express, United Package, Federal Shipping) และสถิติการส่ง |
| **12** | **รายงาน** | ตัวกรองปี, แท็บวิเคราะห์, สรุปรายได้รวม 4 KPI, กราฟแนวโน้ม, Top 5 สินค้าขายดี, Top 5 ลูกค้า พร้อมปุ่มส่งออก Excel/CSV |

### 3. ข้อกำหนดที่ 3: การ Deploy บน Web Hosting จริง
- ✅ มีไฟล์ฐานข้อมูล `dbNorthwind.sql` ครบทั้ง 8 ตาราง
- ✅ รองรับ Free Hosting (InfinityFree), Shared Hosting (Hostinger, SiteGround), VPS และ Railway Cloud
- ✅ มีระบบตรวจจับการตั้งค่าโฮสต์อัตโนมัติ ไม่ต้องแก้โค้ดเมื่อเปลี่ยนโฮสต์
- ✅ มีเครื่องมือตรวจสอบฐานข้อมูลในตัว `setup_db.php`

### 4. ข้อกำหนดที่ 4: เอกสารข้อกำหนดระบบ API (API Documentation)
- ✅ **Interactive API Documentation UI**: เปิดผ่านเว็บได้ทันทีที่ [`api-docs.html`](api-docs.html)
- ✅ **API Markdown Document**: เอกสารครบถ้วนที่ [`docs/API_DOCUMENTATION.md`](docs/API_DOCUMENTATION.md)
- ✅ **Postman Collection**: ไฟล์นำเข้าสำหรับทดสอบที่ [`docs/northwind_postman_collection.json`](docs/northwind_postman_collection.json)

---

## 📂 โครงสร้างโฟลเดอร์โปรเจกต์ (Project Structure)

```text
Project-II  Web App Project Full/
├── api/
│   ├── .htaccess                      # Apache URL Rewriting สำหรับ RESTful API
│   ├── index.php                      # Front Controller รวมทุก Route
│   ├── core/
│   │   ├── Database.php               # PDO OOP Connection Class (ตาม Slide 1)
│   │   ├── JWT.php                    # JSON Web Token HS256 Handler (ตาม Slide 4 Screen 1)
│   │   ├── Response.php               # Standard JSON Response Handler (ตาม Slide 5)
│   │   ├── Router.php                 # RESTful HTTP Method Router
│   │   └── MockDataProvider.php       # Provider ข้อมูลจำลองกรณีเซิร์ฟเวอร์ยังไม่ได้รัน DB
│   └── controllers/
│       ├── AuthController.php         # ระบบยืนยันตัวตน JWT
│       ├── DashboardController.php    # สถิติภาพรวมแดชบอร์ด
│       ├── ProductController.php      # CRUD สินค้า (tb_products)
│       ├── CategoryController.php     # CRUD หมวดหมู่สินค้า (tb_categories)
│       ├── OrderController.php        # CRUD คำสั่งซื้อ (tb_orders, tb_orderdetails)
│       ├── CustomerController.php     # CRUD ลูกค้า (tb_customers)
│       ├── SupplierController.php     # CRUD ผู้จัดจำหน่าย (tb_suppliers)
│       ├── EmployeeController.php     # CRUD พนักงาน (tb_employees)
│       ├── ShipperController.php      # CRUD บริษัทขนส่ง (tb_shippers)
│       └── ReportController.php       # รายงานและส่งออก CSV (tb_orders, tb_orderdetails)
├── css/
│   └── style.css                      # Custom Design Tokens & Bootstrap 5 Extension
├── js/
│   ├── api.js                         # REST API Client พร้อม Bearer Token Handling
│   └── app.js                         # Main Application Controller สำหรับทั้ง 12 หน้าจอ
├── docs/
│   ├── API_DOCUMENTATION.md           # เอกสาร API แบบละเอียดทุก Endpoint
│   ├── DEPLOYMENT_GUIDE.md            # คู่มือการขึ้น Web Hosting จริง (InfinityFree, Railway)
│   └── northwind_postman_collection.json # Postman Collection พร้อมทดสอบ
├── inc/
│   ├── Database.php                   # Database Class (PDO OOP)
│   └── ConnDB.php                     # Backward-Compatible Connector
├── dbNorthwind.sql                    # SQL Dump ฐานข้อมูล 8 ตาราง
├── setup_db.php                       # หน้าต่างตรวจสอบและติดตั้งฐานข้อมูล
├── index.html                         # เว็บแอปพลิเคชันหลัก (12 Screens Single-Page-App)
├── index.php                          # PHP Entry Point Wrapper
├── api-docs.html                      # Interactive Swagger-style Documentation
└── README.md                          # รายละเอียดโปรเจกต์
```

---

## 🚀 วิธีการเปิดใช้งานบนเครื่อง (Localhost MAMP / XAMPP)

1. **เปิดโปรแกรม MAMP** แล้วกดปุ่ม **"Start Servers"**
2. **เปิดเบราว์เซอร์แล้วเข้าใช้งาน**:
   - 🌐 **หน้าเว็บหลัก (Web Application 12 หน้าจอ)**:
     [http://localhost/CPE66/Project-II%20%20Web%20App%20Project%20Full/](http://localhost/CPE66/Project-II%20%20Web%20App%20Project%20Full/)
   - 📑 **หน้า API Documentation**:
     [http://localhost/CPE66/Project-II%20%20Web%20App%20Project%20Full/api-docs.html](http://localhost/CPE66/Project-II%20%20Web%20App%20Project%20Full/api-docs.html)
   - ⚙️ **หน้าตรวจสอบฐานข้อมูล (Database Diagnostics)**:
     [http://localhost/CPE66/Project-II%20%20Web%20App%20Project%20Full/setup_db.php](http://localhost/CPE66/Project-II%20%20Web%20App%20Project%20Full/setup_db.php)

---

## 🔑 ข้อมูลสำหรับทดสอบเข้าสู่ระบบ (Demo Credentials)
- **Email / Username**: `admin@northwind.co.th`
- **Password**: `admin123`
*(หรือใช้ชื่อพนักงานในฐานข้อมูล เช่น `Nancy` หรือ `Andrew`)*

