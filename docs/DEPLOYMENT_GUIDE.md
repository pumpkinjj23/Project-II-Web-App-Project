# ☁️ คู่มือการ Deploy ระบบ Northwind BMS ขึ้น Hosting จริง
## ข้อกำหนดที่ 3 จาก 4: Deploy ระบบบน Web Hosting จริง พร้อมส่ง Public URL

---

## 📋 Checklist ก่อนส่งงาน (ตรวจสอบให้ครบตามสไลด์)
- [x] **ระบบทำงานได้จาก URL จริง**: เข้าถึงผ่านอินเทอร์เน็ตได้โดยไม่ต้องรัน Localhost
- [x] **Login เข้าสู่ระบบได้**: ทดสอบล็อกอินด้วย JWT Authentication
- [x] **ข้อมูลแสดงผลถูกต้องจาก DB**: ข้อมูล 8 ตารางดึงจาก MySQL
- [x] **CRUD ทำงานได้ครบถ้วน**: สามารถเพิ่ม แก้ไข ค้นหา และลบข้อมูลได้
- [x] **ส่ง URL ให้อาจารย์ตรวจ**: ตัวอย่าง `https://your-domain.com/northwind/`

---

## 🚀 ทางเลือกที่ 1: Deploy บน Free Hosting (InfinityFree)

1. สมัครสมาชิกที่ [InfinityFree.com](https://infinityfree.com/)
2. สร้าง Account ใหม่ และเลือก Subdomain ฟรี เช่น `cpe66-northwind.infinityfreeapp.com`
3. เข้าไปยัง **Control Panel (cPanel)**:
   - เปิด **MySQL Databases** -> สร้างฐานข้อมูลชื่อ `db_northwind`
   - เปิด **phpMyAdmin** -> เลือกฐานข้อมูล `db_northwind` -> กดแท็บ **Import** -> เลือกไฟล์ `dbNorthwind.sql` แล้วกด Go
4. อัปโหลดไฟล์โปรเจกต์:
   - เปิด **Online File Manager** หรือใช้โปรแกรม **FileZilla (FTP)**
   - นำไฟล์ทั้งหมดในโฟลเดอร์ `Project-II  Web App Project Full` ไปวางในโฟลเดอร์ `htdocs/`
5. ทดสอบเปิด URL: `http://cpe66-northwind.infinityfreeapp.com/`

---

## 🚀 ทางเลือกที่ 2: Deploy บน Railway Cloud (PHP + MySQL Service)

โปรเจกต์นี้รองรับ Railway 100% ผ่าน Environment Variables อัตโนมัติ

1. สมัครสมาชิกที่ [Railway.com](https://railway.com/)
2. สร้าง New Project -> **Add MySQL Database**
   - ในแท็บ Data ของ MySQL -> กดปุ่ม Import -> เลือกไฟล์ `dbNorthwind.sql`
3. เพิ่ม **GitHub Repo** หรือ Deploy Web Service:
   - อัปโหลดโค้ดนี้ขึ้น GitHub
   - ใน Railway กด **New Service** -> **GitHub Repo**
   - ไปที่แท็บ **Variables** ของ Web Service -> กด **Add Reference** -> เลือกเชื่อมต่อกับตัวแปรจาก MySQL Service (`MYSQLHOST`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQLPORT`)
4. ในแท็บ **Settings** -> ส่วน **Networking** -> กด **Generate Domain**
5. ระบบจะสร้าง Public URL ทันที เช่น `https://northwind-bms-production.up.railway.app/`

---

## 🚀 ทางเลือกที่ 3: Deploy บน Shared Hosting ทั่วไป (Hostinger / SiteGround / VPS)

1. เข้า cPanel หรือ DirectAdmin ของโฮสติ้ง
2. สร้างฐานข้อมูล MySQL และนำเข้า `dbNorthwind.sql` ผ่าน phpMyAdmin
3. อัปโหลดไฟล์โปรเจกต์ทั้งหมดไปยัง `public_html/` หรือ `public_html/northwind/`
4. ตรวจสอบไฟล์ `.htaccess` ว่าเปิดใช้งาน `mod_rewrite` แล้ว
5. หากใช้พอร์ตหรือรหัสผ่านเฉพาะ ให้ตั้งค่าใน `inc/Database.php` หรือระบุ Environment Variable

