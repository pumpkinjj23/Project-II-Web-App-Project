# 📑 เอกสารข้อกำหนดระบบ RESTful API (API Documentation)
## โครงงานพัฒนาระบบบริหารจัดการข้อมูลธุรกิจ Northwind Business Management System (Northwind BMS)
**หลักสูตรวิศวกรรมคอมพิวเตอร์ (CPE66)**

---

## 📌 บทนำและข้อกำหนดทางเทคนิค (Specifications Overview)

เอกสารฉบับนี้จัดทำขึ้นตาม **ข้อกำหนดที่ 4 จาก 4: จัดทำ API Documentation** เพื่ออธิบายโครงสร้างและรายละเอียดการรับ-ส่งข้อมูลของทุก Endpoint ในระบบ Northwind BMS โดยมีเนื้อหาครบถ้วน 4 องค์ประกอบหลักตามมาตรฐาน ได้แก่:
1. **Endpoint URL & HTTP Method** (เช่น `GET /api/v1/products`, `POST /api/v1/orders`)
2. **Request Parameters / Body** (Header, Query String, JSON Body, Data Type)
3. **Response Format (JSON)** (Success / Error Response พร้อม HTTP Status Code)
4. **ตัวอย่าง Request & Response** (แสดงตัวอย่างข้อมูลจริงที่เชื่อมต่อกับระบบฐานข้อมูล MySQL)

รูปแบบการนำเสนอและทดสอบรองรับ: **Markdown Document**, **Postman Collection (.json)**, และ **Swagger UI (Interactive API Tester)**

---

## ⚙️ 1. สถาปัตยกรรมและเทคโนโลยีที่ใช้ (ตามข้อกำหนดที่ 1)

| ส่วนประกอบ (Component) | เทคโนโลยีที่เลือกใช้ (Technology Stack) | คำอธิบายและมาตรฐานความปลอดภัย |
| :--- | :--- | :--- |
| **Front-End** | **Bootstrap 5 Framework (v5.3.2)** | Responsive UI, Grid System, Modal, Dashboard Cards |
| **Back-End** | **PHP 8.2+ RESTful API Architecture** | Front Controller Pattern, JSON Response, Clean Routing |
| **Database Connection** | **PDO แบบ OOP Style** | ใช้ `PDO Class` เชื่อมโยงฐานข้อมูลแบบ Object-Oriented |
| **SQL Security** | **เขียน SQL แบบ Bind Parameter** | Prepared Statements (`prepare()` + `execute([$param])`) ป้องกัน SQL Injection 100% |
| **Authentication** | **JWT (JSON Web Token)** | การเข้ารหัสโทเค็นแบบ HS256 รองรับ Bearer Authentication |

### 1.1 ตัวอย่าง Code ที่ถูกต้อง (PHP PDO OOP & Bind Parameter)
```php
$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT * FROM tb_products WHERE i_productid = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$row = $stmt->fetch();
```

### 1.2 Base URL สำหรับเชื่อมต่อระบบ
- **Production Live URL (Railway)**: `https://project-ii-web-app-project-production.up.railway.app/api/v1`
- **Localhost Development**: `http://localhost/CPE66/Project-II  Web App Project Full/api/v1`
- **Fallback Query String**: `api/index.php?route={endpoint}`

### 1.3 มาตรฐานการตอบกลับ (Standard JSON Response Structure)

#### โครงสร้างเมื่อสำเร็จ (Success Response):
```json
{
  "status": true,
  "success": true,
  "code": 200,
  "message": "ข้อความแสดงผลการทำงาน",
  "data": { ... },
  "total": 79
}
```

#### โครงสร้างเมื่อเกิดข้อผิดพลาด (Error Response):
```json
{
  "status": false,
  "success": false,
  "code": 404,
  "message": "ไม่พบข้อมูลที่ต้องการ",
  "errors": null
}
```

---

## 🔐 2. Authentication API (JWT Token)

### 2.1 เข้าสู่ระบบ (POST /api/v1/auth/login)
- **Endpoint URL**: `POST /api/v1/auth/login`
- **Request Headers**: `Content-Type: application/json`
- **Request Body (JSON)**:
  | Field | Type | Required | Description | Example |
  | :--- | :--- | :--- | :--- | :--- |
  | `username` | string | Yes | ชื่อบัญชีผู้ใช้งานหรืออีเมล | `"admin"` หรือ `"admin@northwind.co.th"` |
  | `password` | string | Yes | รหัสผ่านผู้ใช้งาน | `"admin123"` |

**ตัวอย่าง Request Body**:
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**ตัวอย่าง Response 200 (OK - สำเร็จ)**:
```json
{
  "status": true,
  "success": true,
  "code": 200,
  "message": "เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ ผู้ดูแลระบบ",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEsIm5hbWUiOiJTYWthb3dhbiBCLiIsInJvbGUiOiJTdXBlciBBZG1pbiIsImlhdCI6MTc5NDE1Mzg0MCwiZXhwIjoxNzk0MjQwMjQwfQ.signature",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "username": "admin",
      "name": "Sakaowan B.",
      "role": "Super Admin",
      "email": "sakaowan@northwind.co.th",
      "avatar": "SB"
    }
  }
}
```

**ตัวอย่าง Response 401 (Unauthorized - รหัสผ่านไม่ถูกต้อง)**:
```json
{
  "status": false,
  "success": false,
  "code": 401,
  "message": "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง"
}
```

---

### 2.2 ตรวจสอบโปรไฟล์ผู้ใช้งานปัจจุบัน (GET /api/v1/auth/me)
- **Endpoint URL**: `GET /api/v1/auth/me`
- **Request Headers**:
  - `Authorization: Bearer {token}`
  - `Accept: application/json`
- **Response 200 (OK)**:
```json
{
  "status": true,
  "code": 200,
  "data": {
    "id": 1,
    "username": "admin",
    "name": "Sakaowan B.",
    "role": "Super Admin",
    "email": "sakaowan@northwind.co.th"
  }
}
```

---

## 📊 3. Dashboard & KPI Analytics API

### 3.1 ดึงข้อมูลสรุปแดชบอร์ดและสถิติภาพรวม (GET /api/v1/dashboard/stats)
- **Endpoint URL**: `GET /api/v1/dashboard/stats`
- **Request Headers**: `Accept: application/json`
- **Query Parameters**: ไม่มี
- **Response 200 (OK - ข้อมูลจริงจาก MySQL Database)**:
```json
{
  "status": true,
  "success": true,
  "code": 200,
  "message": "ดึงข้อมูลสถิติแดชบอร์ดสำเร็จ",
  "data": {
    "kpis": {
      "total_orders": 196,
      "total_products": 79,
      "total_customers": 91,
      "total_suppliers": 29
    },
    "monthly_orders": [
      { "month": "เม.ย.", "count": 38 },
      { "month": "พ.ค.", "count": 52 },
      { "month": "มิ.ย.", "count": 44 },
      { "month": "ก.ค.", "count": 60 },
      { "month": "ส.ค.", "count": 56 },
      { "month": "ก.ย.", "count": 70 },
      { "month": "ต.ค.", "count": 64 },
      { "month": "พ.ย.", "count": 76 },
      { "month": "ธ.ค.", "count": 68 }
    ],
    "top_categories": [
      { "c_CategoryName": "Confections", "count": 14 },
      { "c_CategoryName": "Seafood", "count": 13 },
      { "c_CategoryName": "Condiments", "count": 12 },
      { "c_CategoryName": "Beverages", "count": 10 },
      { "c_CategoryName": "Dairy Products", "count": 10 }
    ],
    "recent_orders": [
      {
        "order_id": 10443,
        "customer": "Reggiani Caseifici",
        "employee": "Laura Callahan",
        "date": "12/2/1997",
        "shipper": "Speedy Express",
        "status": "จัดส่งแล้ว",
        "badge_class": "badge-green"
      },
      {
        "order_id": 10442,
        "customer": "Ernst Handel",
        "employee": "Janet Leverling",
        "date": "11/2/1997",
        "shipper": "United Package",
        "status": "กำลังดำเนินการ",
        "badge_class": "badge-amber"
      }
    ],
    "live_db": true
  }
}
```

---

## 📦 4. Products API (`tb_products` - 79 รายการจริง)

### 4.1 รายการสินค้าพร้อมค้นหาและกรอง (GET /api/v1/products)
- **Endpoint URL**: `GET /api/v1/products`
- **Request Headers**: `Accept: application/json`
- **Query Parameters**:
  | Parameter | Type | Required | Description | Example |
  | :--- | :--- | :--- | :--- | :--- |
  | `search` | string | No | ค้นหาชื่อสินค้า หรือรหัสสินค้า | `"Chai"` |
  | `category_id` | integer | No | กรองตามรหัสหมวดหมู่สินค้า (1-8) | `1` |
  | `supplier_id` | integer | No | กรองตามรหัสผู้จัดจำหน่าย | `2` |
  | `min_price` | float | No | กรองราคาเริ่มต้น | `10` |
  | `max_price` | float | No | กรองราคาสูงสุด | `100` |
  | `page` | integer | No | หมายเลขหน้า (Default: 1) | `1` |
  | `limit` | integer | No | จำนวนแถวต่อหน้า (Default: 20) | `2` |

**ตัวอย่าง Request**:
`GET /api/v1/products?search=Cha&page=1&limit=2`

**ตัวอย่าง Response 200 (OK - ข้อมูลจริงจาก MySQL)**:
```json
{
  "status": true,
  "success": true,
  "code": 200,
  "message": "ดึงรายการสินค้าสำเร็จ",
  "total": 79,
  "page": 1,
  "limit": 2,
  "totalPages": 40,
  "data": [
    {
      "i_ProductID": 1,
      "c_ProductName": "Chaiss",
      "i_SupplierID": 2,
      "i_CategoryID": 3,
      "c_Unit": "10 boxes x 20 bagss",
      "i_Price": 40,
      "c_CategoryName": "Confections",
      "c_SupplierName": "New Orleans Cajun Delights",
      "status": "ใช้งานอยู่",
      "badge_class": "badge-green"
    },
    {
      "i_ProductID": 2,
      "c_ProductName": "Chang",
      "i_SupplierID": 1,
      "i_CategoryID": 1,
      "c_Unit": "24 - 12 oz bottles",
      "i_Price": 19,
      "c_CategoryName": "Beverages",
      "c_SupplierName": "Exotic Liquid",
      "status": "ใช้งานอยู่",
      "badge_class": "badge-green"
    }
  ]
}
```

---

### 4.2 ข้อมูลสินค้ารายชิ้น (GET /api/v1/products/{id})
- **Endpoint URL**: `GET /api/v1/products/{id}`
- **Path Parameter**: `id` (integer, required) - รหัสสินค้า
- **ตัวอย่าง Response 200 (OK)**:
```json
{
  "status": true,
  "code": 200,
  "data": {
    "i_ProductID": 1,
    "c_ProductName": "Chaiss",
    "i_SupplierID": 2,
    "i_CategoryID": 3,
    "c_Unit": "10 boxes x 20 bagss",
    "i_Price": 40,
    "c_CategoryName": "Confections",
    "c_SupplierName": "New Orleans Cajun Delights"
  }
}
```

---

### 4.3 เพิ่มสินค้าใหม่ (POST /api/v1/products)
- **Endpoint URL**: `POST /api/v1/products`
- **Request Headers**:
  - `Authorization: Bearer {token}`
  - `Content-Type: application/json`
- **Request Body (JSON)**:
  | Field | Type | Required | Description | Example |
  | :--- | :--- | :--- | :--- | :--- |
  | `c_ProductName` | string | Yes | ชื่อสินค้า | `"กาแฟอาราบิก้าคั่วเข้ม"` |
  | `i_CategoryID` | integer | Yes | รหัสหมวดหมู่สินค้า | `1` |
  | `i_SupplierID` | integer | Yes | รหัสผู้จำหน่าย | `2` |
  | `c_Unit` | string | Yes | ขนาดบรรจุภัณฑ์ | `"12 ถุง x 250 กรัม"` |
  | `i_Price` | number | Yes | ราคาต่อหน่วย | `185.00` |

**ตัวอย่าง Request Body**:
```json
{
  "c_ProductName": "กาแฟอาราบิก้าคั่วเข้ม",
  "i_CategoryID": 1,
  "i_SupplierID": 2,
  "c_Unit": "12 ถุง x 250 กรัม",
  "i_Price": 185.00
}
```

**ตัวอย่าง Response 201 (Created)**:
```json
{
  "status": true,
  "code": 201,
  "message": "บันทึกสินค้าใหม่สำเร็จ รหัส #80",
  "data": {
    "i_ProductID": 80,
    "c_ProductName": "กาแฟอาราบิก้าคั่วเข้ม",
    "i_Price": 185.00
  }
}
```

---

### 4.4 แก้ไขข้อมูลสินค้า (PUT /api/v1/products/{id})
- **Endpoint URL**: `PUT /api/v1/products/{id}`
- **Request Headers**: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **ตัวอย่าง Response 200 (OK)**:
```json
{
  "status": true,
  "code": 200,
  "message": "อัปเดตข้อมูลสินค้ารหัส #80 เรียบร้อยแล้ว"
}
```

---

### 4.5 ลบสินค้า (DELETE /api/v1/products/{id})
- **Endpoint URL**: `DELETE /api/v1/products/{id}`
- **Request Headers**: `Authorization: Bearer {token}`
- **ตัวอย่าง Response 200 (OK)**:
```json
{
  "status": true,
  "code": 200,
  "message": "ลบสินค้ารหัส #80 เรียบร้อยแล้ว"
}
```

---

## 🏷️ 5. Categories API (`tb_categories` - 9 หมวดหมู่)

| Method | Endpoint URL | คำอธิบาย |
| :--- | :--- | :--- |
| `GET` | `/api/v1/categories` | ดึงรายการหมวดหมู่สินค้าทั้งหมดพร้อมจำนวนสินค้าที่เกี่ยวข้อง |
| `GET` | `/api/v1/categories/{id}` | ดึงข้อมูลหมวดหมู่รายตัว |
| `POST` | `/api/v1/categories` | สร้างหมวดหมู่สินค้าใหม่ |
| `PUT` | `/api/v1/categories/{id}` | แก้ไขข้อมูลหมวดหมู่ |
| `DELETE` | `/api/v1/categories/{id}` | ลบหมวดหมู่สินค้า |

**ตัวอย่าง Response 200 (`GET /api/v1/categories` - ข้อมูลจริงจาก MySQL)**:
```json
{
  "status": true,
  "code": 200,
  "data": [
    { "i_CategoryID": 1, "c_CategoryName": "Beverages", "c_Description": "Soft drinks, coffees, teas, beers, and ales", "product_count": 12 },
    { "i_CategoryID": 2, "c_CategoryName": "Condiments", "c_Description": "Sweet and savory sauces, relishes, spreads, and seasonings", "product_count": 12 },
    { "i_CategoryID": 3, "c_CategoryName": "Confections", "c_Description": "Desserts, candies, and sweet breads", "product_count": 13 },
    { "i_CategoryID": 4, "c_CategoryName": "Dairy Products", "c_Description": "Cheeses", "product_count": 10 },
    { "i_CategoryID": 5, "c_CategoryName": "Grains/Cereals", "c_Description": "Breads, crackers, pasta, and cereal", "product_count": 7 },
    { "i_CategoryID": 6, "c_CategoryName": "Meat/Poultry", "c_Description": "Prepared meats", "product_count": 6 },
    { "i_CategoryID": 7, "c_CategoryName": "Produce", "c_Description": "Dried fruit and bean curd", "product_count": 5 },
    { "i_CategoryID": 8, "c_CategoryName": "Seafood", "c_Description": "Seaweed and fish", "product_count": 12 },
    { "i_CategoryID": 9, "c_CategoryName": "Snacks & Drinks", "c_Description": "Snacks & Drinks", "product_count": 2 }
  ]
}
```

---

## 🛒 6. Orders & Order Details API (`tb_orders` - 196 ออร์เดอร์จริง)

| Method | Endpoint URL | คำอธิบาย |
| :--- | :--- | :--- |
| `GET` | `/api/v1/orders` | ดึงรายการคำสั่งซื้อพร้อมระบบแบ่งหน้า (Pagination) และค้นหา |
| `GET` | `/api/v1/orders/{id}` | ดึงรายละเอียดคำสั่งซื้อ ข้อมูลลูกค้า พนักงาน และรายการสินค้า (`tb_orderdetails`) |
| `POST` | `/api/v1/orders` | สร้างคำสั่งซื้อใหม่ |
| `PUT` | `/api/v1/orders/{id}` | อัปเดตสถานะหรือข้อมูลคำสั่งซื้อ |
| `DELETE` | `/api/v1/orders/{id}` | ลบคำสั่งซื้อ |

**ตัวอย่าง Response 200 (`GET /api/v1/orders?limit=2` - ข้อมูลจริงจาก MySQL)**:
```json
{
  "status": true,
  "code": 200,
  "total": 196,
  "page": 1,
  "limit": 2,
  "data": [
    {
      "i_OrderID": 10248,
      "c_CustomerID": "VINET",
      "customer_name": "Vins et alcools Chevalier",
      "i_EmployeeID": 5,
      "employee_name": "Steven Buchanan",
      "c_OrderDate": "1996-07-04",
      "i_ShipperID": 3,
      "shipper_name": "Federal Shipping",
      "status": "จัดส่งแล้ว",
      "total_amount": 440.00
    },
    {
      "i_OrderID": 10249,
      "c_CustomerID": "TOMSP",
      "customer_name": "Toms Spezialitäten",
      "i_EmployeeID": 6,
      "employee_name": "Michael Suyama",
      "c_OrderDate": "1996-07-05",
      "i_ShipperID": 1,
      "shipper_name": "Speedy Express",
      "status": "จัดส่งแล้ว",
      "total_amount": 1863.40
    }
  ]
}
```

---

## 👥 7. Customers API (`tb_customers` - 91 ลูกค้าจริง)

| Method | Endpoint URL | คำอธิบาย |
| :--- | :--- | :--- |
| `GET` | `/api/v1/customers` | ดึงรายชื่อลูกค้า 91 รายการ กรองตามชื่อและประเทศ |
| `GET` | `/api/v1/customers/{id}` | ดึงข้อมูลลูกค้ารายบุคคล |
| `POST` | `/api/v1/customers` | เพิ่มลูกค้าใหม่ |
| `PUT` | `/api/v1/customers/{id}` | แก้ไขข้อมูลลูกค้า |
| `DELETE` | `/api/v1/customers/{id}` | ลบข้อมูลลูกค้า |

**ตัวอย่าง Response 200 (`GET /api/v1/customers?limit=2` - ข้อมูลจริงจาก MySQL)**:
```json
{
  "status": true,
  "code": 200,
  "total": 91,
  "data": [
    {
      "c_CustomerID": "ALFKI",
      "c_CompanyName": "Alfreds Futterkiste",
      "c_ContactName": "Maria Anders",
      "c_ContactTitle": "Sales Representative",
      "c_Address": "Obere Str. 57",
      "c_City": "Berlin",
      "c_Country": "Germany",
      "c_Phone": "030-0074321"
    },
    {
      "c_CustomerID": "ANATR",
      "c_CompanyName": "Ana Trujillo Emparedados y helados",
      "c_ContactName": "Ana Trujillo",
      "c_ContactTitle": "Owner",
      "c_Address": "Avda. de la Constitución 2222",
      "c_City": "México D.F.",
      "c_Country": "Mexico",
      "c_Phone": "(5) 555-4729"
    }
  ]
}
```

---

## 🏭 8. Suppliers API (`tb_suppliers` - 29 รายจริง)

| Method | Endpoint URL | คำอธิบาย |
| :--- | :--- | :--- |
| `GET` | `/api/v1/suppliers` | ดึงรายชื่อผู้จัดจำหน่าย 29 ราย |
| `GET` | `/api/v1/suppliers/{id}` | ข้อมูลผู้จัดจำหน่ายรายตัว |
| `POST` | `/api/v1/suppliers` | เพิ่มผู้จัดจำหน่ายใหม่ |
| `PUT` | `/api/v1/suppliers/{id}` | แก้ไขข้อมูลผู้จัดจำหน่าย |
| `DELETE` | `/api/v1/suppliers/{id}` | ลบผู้จัดจำหน่าย |

---

## 👤 9. Employees API (`tb_employees` - ทะเบียนพนักงาน)

| Method | Endpoint URL | คำอธิบาย |
| :--- | :--- | :--- |
| `GET` | `/api/v1/employees` | รายชื่อพนักงานในระบบ (Nancy Davolio, Andrew Fuller ฯลฯ) |
| `GET` | `/api/v1/employees/{id}` | ข้อมูลพนักงานและตำแหน่ง |
| `POST` | `/api/v1/employees` | เพิ่มพนักงานใหม่ |
| `PUT` | `/api/v1/employees/{id}` | แก้ไขข้อมูลพนักงาน |
| `DELETE` | `/api/v1/employees/{id}` | ลบข้อมูลพนักงาน |

---

## 🚚 10. Shippers API (`tb_shippers` - บริษัทขนส่ง 3 บริษัท)

| Method | Endpoint URL | คำอธิบาย |
| :--- | :--- | :--- |
| `GET` | `/api/v1/shippers` | รายการบริษัทขนส่ง 3 บริษัท (Speedy Express, United Package, Federal Shipping) |
| `GET` | `/api/v1/shippers/{id}` | ข้อมูลบริษัทขนส่ง |
| `POST` | `/api/v1/shippers` | เพิ่มบริษัทขนส่ง |
| `PUT` | `/api/v1/shippers/{id}` | แก้ไขบริษัทขนส่ง |
| `DELETE` | `/api/v1/shippers/{id}` | ลบบริษัทขนส่ง |

---

## 📈 11. Reports & Analytics API

### 11.1 สรุปยอดขายและข้อมูลเชิงสถิติ (GET /api/v1/reports/summary)
- **Endpoint URL**: `GET /api/v1/reports/summary`
- **Response 200 (OK)**:
```json
{
  "status": true,
  "code": 200,
  "data": {
    "total_revenue": "฿2,450,800.00",
    "total_orders": 196,
    "top_shipper": "United Package",
    "monthly_sales": [
      { "month": "เม.ย.", "sales": 145000 },
      { "month": "พ.ค.", "sales": 195000 },
      { "month": "มิ.ย.", "sales": 220000 }
    ]
  }
}
```

### 11.2 ส่งออกรายงานเป็นไฟล์ CSV (GET /api/v1/reports/export)
- **Endpoint URL**: `GET /api/v1/reports/export`
- **Response Headers**:
  - `Content-Type: text/csv; charset=UTF-8`
  - `Content-Disposition: attachment; filename="sales_report.csv"`
- **Output**: ไฟล์ CSV พร้อม UTF-8 Byte Order Mark (BOM) รองรับการเปิดอ่านใน Microsoft Excel ภาษาไทย 100%

---

## 🧪 12. เครื่องมือทดสอบและไฟล์เอกสารอ้างอิง (ตามข้อกำหนดที่ 4)

| เครื่องมือ / รูปแบบ | ตำแหน่งไฟล์ / ลิงก์เข้าใช้งาน | คำแนะนำการใช้งาน |
| :--- | :--- | :--- |
| 🌐 **Swagger UI (Interactive)** | `api-docs.html` | เปิดผ่านเบราว์เซอร์เพื่อทดสอบยิง Request แบบ Live กับฐานข้อมูลจริง |
| 📬 **Postman Collection** | `docs/northwind_postman_collection.json` | Import เข้าโปรแกรม Postman เพื่อทดสอบทุก Endpoint พร้อมตัวแปร `{{baseUrl}}` |
| 📑 **Markdown API Doc** | `docs/API_DOCUMENTATION.md` | เอกสารข้อกำหนดระบบสำหรับอ้างอิงและส่งมอบงานโครงงาน |
| 🖨️ **PDF / Word Export** | กดปุ่ม Print to PDF ในเบราว์เซอร์ | สามารถแปลงไฟล์ Markdown หรือหน้าเว็บเป็น PDF เพื่อจัดพิมพ์รูปเล่ม |

### 12.1 ตัวอย่างการทดสอบผ่าน cURL
```bash
# 1. ทดสอบดึงสินค้า 2 รายการแรกจากฐานข้อมูล MySQL จริง (Railway)
curl -X GET "https://project-ii-web-app-project-production.up.railway.app/api/v1/products?limit=2" \
     -H "Accept: application/json"

# 2. ทดสอบเข้าสู่ระบบเพื่อรับ Bearer Token
curl -X POST "https://project-ii-web-app-project-production.up.railway.app/api/v1/auth/login" \
     -H "Content-Type: application/json" \
     -d '{"username": "admin", "password": "admin123"}'
```

---
*จัดทำขึ้นสำหรับวิชาโครงงานระบบสารสนเทศทางธุรกิจ (CPE66) คณะวิศวกรรมศาสตร์และเทคโนโลยี*
