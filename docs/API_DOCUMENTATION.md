# 📑 เอกสารข้อกำหนดระบบ RESTful API (API Documentation)
## โครงงานพัฒนาระบบจัดการข้อมูลธุรกิจ Northwind Business Management System (Northwind BMS)
**หลักสูตรวิศวกรรมคอมพิวเตอร์ (CPE66)**

---

## 📌 1. ภาพรวมสถาปัตยกรรม (Architecture Overview)

ระบบ Northwind BMS พัฒนาด้วยสถาปัตยกรรม **Decoupled RESTful API Architecture** แยกส่วนการทำงานระหว่าง **Frontend (Bootstrap 5 + Vanilla JavaScript ES6+)** และ **Backend (PHP 8.2+ RESTful API + PDO OOP Style)** อย่างชัดเจน

### 1.1 Technology Stack (ตามข้อกำหนดที่ 1)
- **Front-End**: Bootstrap 5 Framework (Responsive UI, Components, Grid System)
- **Back-End**: PHP RESTful API Architecture
- **Database Connection**: PDO แบบ OOP Style (`$db = new Database(); $conn = $db->getConnection();`)
- **SQL Security**: SQL Prepared Statements พร้อม Parameter Binding ป้องกัน SQL Injection 100%
- **Authentication**: JSON Web Token (JWT HS256) พร้อมระบบตรวจสอบ Bearer Token ใน HTTP Headers

### 1.2 Base URL
```text
Localhost: http://localhost/CPE66/Project-II  Web App Project Full/api
Cloud/Hosting: https://your-domain.com/api
```

### 1.3 มาตรฐานการตอบกลับ (Standard JSON Response Format)
ทุก Endpoint ของระบบจะส่งข้อมูลกลับในรูปแบบ JSON ตามมาตรฐาน ดังนี้:

```json
{
  "status": true,
  "success": true,
  "code": 200,
  "message": "ดำเนินการสำเร็จ",
  "data": { ... }
}
```

---

## 🔐 2. Authentication API (JWT)

### 2.1 เข้าสู่ระบบ (Login)
- **URL**: `POST /api/v1/auth/login`
- **Headers**: `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "email": "admin@northwind.co.th",
    "password": "admin123"
  }
  ```
- **Response 200 (Success)**:
  ```json
  {
    "status": true,
    "success": true,
    "code": 200,
    "message": "เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ วชิรา ล.",
    "data": {
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "token_type": "Bearer",
      "expires_in": 86400,
      "user": {
        "id": 1,
        "name": "วชิรา ล.",
        "email": "admin@northwind.co.th",
        "role": "ผู้ดูแลระบบ (Administrator)"
      }
    }
  }
  ```

---

## 📦 3. Products API (`tb_products`)

### 3.1 ดึงรายการสินค้าและค้นหา/กรองข้อมูล (GET List)
- **URL**: `GET /api/v1/products`
- **Query Parameters**:
  - `search` (string, optional): คำค้นหาชื่อสินค้าหรือรหัสสินค้า
  - `category_id` (int, optional): รหัสหมวดหมู่สินค้า
  - `supplier_id` (int, optional): รหัสผู้จัดจำหน่าย
  - `min_price` (float, optional): ราคาขั้นต่ำ
  - `max_price` (float, optional): ราคาสูงสุด
  - `page` (int, default: 1): เลขหน้า
  - `limit` (int, default: 25): จำนวนรายการต่อหน้า
- **Response 200 (Success)**:
  ```json
  {
    "status": true,
    "total": 79,
    "page": 1,
    "limit": 25,
    "totalPages": 4,
    "data": [
      {
        "i_ProductID": 1,
        "c_ProductName": "ชาไทย (Chai)",
        "i_SupplierID": 1,
        "i_CategoryID": 1,
        "c_Unit": "10 กล่อง × 20 ถุง",
        "i_Price": 18.00,
        "c_CategoryName": "เครื่องดื่ม",
        "c_SupplierName": "Exotic Liquids",
        "status": "ใช้งานอยู่",
        "badge_class": "badge-green"
      }
    ]
  }
  ```

### 3.2 ดึงข้อมูลสินค้ารายชิ้น (GET Single)
- **URL**: `GET /api/v1/products/{id}`
- **Path Parameter**: `id` (int) - รหัสสินค้า
- **Response 200 (Success)**:
  ```json
  {
    "status": true,
    "data": {
      "i_ProductID": 1,
      "c_ProductName": "ชาไทย (Chai)",
      "i_SupplierID": 1,
      "i_CategoryID": 1,
      "c_Unit": "10 กล่อง × 20 ถุง",
      "i_Price": 18.00,
      "c_CategoryName": "เครื่องดื่ม",
      "c_SupplierName": "Exotic Liquids",
      "status": "ใช้งานอยู่"
    }
  }
  ```

### 3.3 เพิ่มสินค้าใหม่ (POST Create)
- **URL**: `POST /api/v1/products`
- **Headers**: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "c_ProductName": "กาแฟอาราบิก้าคั่วเข้ม",
    "i_CategoryID": 1,
    "i_SupplierID": 2,
    "c_Unit": "12 ถุง × 250 กรัม",
    "i_Price": 185.00
  }
  ```
- **Response 201 (Created)**:
  ```json
  {
    "status": true,
    "code": 201,
    "message": "บันทึกสินค้าใหม่สำเร็จ รหัส #104",
    "data": {
      "i_ProductID": 104,
      "c_ProductName": "กาแฟอาราบิก้าคั่วเข้ม",
      "i_Price": 185.00
    }
  }
  ```

### 3.4 แก้ไขข้อมูลสินค้า (PUT Update)
- **URL**: `PUT /api/v1/products/{id}`
- **Request Body**:
  ```json
  {
    "c_ProductName": "ชาเขียวมัทฉะแท้",
    "i_CategoryID": 1,
    "i_SupplierID": 1,
    "c_Unit": "20 ซอง",
    "i_Price": 150.00
  }
  ```

### 3.5 ลบสินค้า (DELETE)
- **URL**: `DELETE /api/v1/products/{id}`
- **Response 200**:
  ```json
  {
    "status": true,
    "message": "ลบสินค้ารหัส #104 สำเร็จ"
  }
  ```

---

## 🗂 4. Categories API (`tb_categories`)
- `GET /api/v1/categories`: รายการหมวดหมู่ทั้งหมดพร้อมจำนวนสินค้าที่ผูกอยู่
- `GET /api/v1/categories/{id}`: ข้อมูลหมวดหมู่รายตัว
- `POST /api/v1/categories`: เพิ่มหมวดหมู่ใหม่
- `PUT /api/v1/categories/{id}`: อัปเดตหมวดหมู่
- `DELETE /api/v1/categories/{id}`: ลบหมวดหมู่

---

## 🛒 5. Orders & Details API (`tb_orders`, `tb_orderdetails`)
- `GET /api/v1/orders`: รายการคำสั่งซื้อ พร้อมการกรองสถานะ (จัดส่งแล้ว, กำลังดำเนินการ, ยกเลิก)
- `GET /api/v1/orders/{id}`: ดูรายละเอียดคำสั่งซื้อ ข้อมูลลูกค้า ผู้จัดส่ง พนักงาน และรายการสินค้า (Line Items)
- `POST /api/v1/orders`: สร้างคำสั่งซื้อใหม่พร้อมรายการสินค้า
- `PUT /api/v1/orders/{id}`: อัปเดตสถานะคำสั่งซื้อ
- `DELETE /api/v1/orders/{id}`: ลบคำสั่งซื้อ

---

## 👥 6. Customers API (`tb_customers`)
- `GET /api/v1/customers`: ทะเบียนลูกค้า 91 รายการ กรองตามประเทศและชื่อ
- `GET /api/v1/customers/{id}`: ข้อมูลลูกค้า
- `POST /api/v1/customers`: เพิ่มลูกค้าใหม่
- `PUT /api/v1/customers/{id}`: แก้ไขข้อมูลลูกค้า
- `DELETE /api/v1/customers/{id}`: ลบลูกค้า

---

## 🏭 7. Suppliers API (`tb_suppliers`)
- `GET /api/v1/suppliers`: ทะเบียนผู้จัดจำหน่าย 29 ราย
- `GET /api/v1/suppliers/{id}`: ข้อมูลผู้จัดจำหน่าย
- `POST /api/v1/suppliers`: เพิ่มผู้จัดจำหน่ายใหม่
- `PUT /api/v1/suppliers/{id}`: แก้ไขผู้จัดจำหน่าย
- `DELETE /api/v1/suppliers/{id}`: ลบผู้จัดจำหน่าย

---

## 👤 8. Employees API (`tb_employees`)
- `GET /api/v1/employees`: รายชื่อพนักงาน 10 คน
- `GET /api/v1/employees/{id}`: ข้อมูลพนักงาน
- `POST /api/v1/employees`: เพิ่มพนักงานใหม่
- `PUT /api/v1/employees/{id}`: แก้ไขพนักงาน
- `DELETE /api/v1/employees/{id}`: ลบพนักงาน

---

## 🚚 9. Shippers API (`tb_shippers`)
- `GET /api/v1/shippers`: บริษัทขนส่ง 3 บริษัท (Speedy Express, United Package, Federal Shipping)
- `GET /api/v1/shippers/{id}`: ข้อมูลบริษัทขนส่ง
- `POST /api/v1/shippers`: เพิ่มบริษัทขนส่ง
- `PUT /api/v1/shippers/{id}`: แก้ไขบริษัทขนส่ง
- `DELETE /api/v1/shippers/{id}`: ลบบริษัทขนส่ง

---

## 📈 10. Reports & Analytics API
- `GET /api/v1/reports/summary`: ข้อมูลสรุปยอดขายรายปี, กราฟยอดขายรายเดือน, 5 สินค้าขายดี, 5 ลูกค้ายอดซื้อสูงสุด
- `GET /api/v1/reports/export`: ส่งออกรายงานสรุปยอดขายเป็นไฟล์ CSV (UTF-8 with BOM รองรับ Excel ภาษาไทย)

