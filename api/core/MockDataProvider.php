<?php
/**
 * MockDataProvider.php - Fallback Data Provider
 * ให้บริการข้อมูลตัวอย่างที่สอดคล้องกับ Northwind BMS — หน้าจอระบบ.html
 * สำหรับกรณีที่ยังไม่ได้เปิด MySQL Server หรือกำลังเปิดทดสอบ UI
 */

class MockDataProvider {
    public static function getCategories() {
        return [
            ['i_CategoryID' => 1, 'c_CategoryName' => 'เครื่องดื่ม (Beverages)', 'c_Description' => 'Soft drinks, coffees, teas, beers', 'product_count' => 12, 'icon' => '🍹'],
            ['i_CategoryID' => 2, 'c_CategoryName' => 'เครื่องปรุงรส (Condiments)', 'c_Description' => 'Sweet and savory sauces, relishes', 'product_count' => 13, 'icon' => '🧂'],
            ['i_CategoryID' => 3, 'c_CategoryName' => 'ขนมหวาน (Confections)', 'c_Description' => 'Desserts, candies, sweet breads', 'product_count' => 7, 'icon' => '🍬'],
            ['i_CategoryID' => 4, 'c_CategoryName' => 'ผลิตภัณฑ์นม (Dairy)', 'c_Description' => 'Cheeses, butter, cream', 'product_count' => 10, 'icon' => '🧀'],
            ['i_CategoryID' => 5, 'c_CategoryName' => 'ธัญพืช/ซีเรียล (Grains)', 'c_Description' => 'Breads, crackers, pasta', 'product_count' => 7, 'icon' => '🌾'],
            ['i_CategoryID' => 6, 'c_CategoryName' => 'เนื้อสัตว์ (Meat/Poultry)', 'c_Description' => 'Prepared meats', 'product_count' => 6, 'icon' => '🥩'],
            ['i_CategoryID' => 7, 'c_CategoryName' => 'อาหารพิเศษ (Produce)', 'c_Description' => 'Dried fruit and bean curd', 'product_count' => 12, 'icon' => '🍄'],
            ['i_CategoryID' => 8, 'c_CategoryName' => 'อาหารทะเล (Seafood)', 'c_Description' => 'Seaweed and fish', 'product_count' => 8, 'icon' => '🐟'],
            ['i_CategoryID' => 9, 'c_CategoryName' => 'กีฬา (Sports)', 'c_Description' => 'Sporting goods', 'product_count' => 1, 'icon' => '👟']
        ];
    }

    public static function getSuppliers() {
        return [
            ['i_SupplierID' => 1, 'c_SupplierName' => 'Exotic Liquids', 'c_ContactName' => 'Charlotte Cooper', 'c_Address' => '49 Gilbert St.', 'c_City' => 'London', 'c_PostalCode' => 'EC1 4SD', 'c_Country' => 'UK', 'c_Phone' => '(171) 555-2222', 'product_count' => 3],
            ['i_SupplierID' => 2, 'c_SupplierName' => 'New Orleans Cajun Delights', 'c_ContactName' => 'Shelley Burke', 'c_Address' => 'P.O. Box 78934', 'c_City' => 'New Orleans', 'c_PostalCode' => '70117', 'c_Country' => 'USA', 'c_Phone' => '(100) 555-4822', 'product_count' => 4],
            ['i_SupplierID' => 3, 'c_SupplierName' => "Grandma Kelly's Homestead", 'c_ContactName' => 'Regina Murphy', 'c_Address' => '707 Oxford Rd.', 'c_City' => 'Ann Arbor', 'c_PostalCode' => '48104', 'c_Country' => 'USA', 'c_Phone' => '(313) 555-5735', 'product_count' => 3],
            ['i_SupplierID' => 4, 'c_SupplierName' => 'Tokyo Traders', 'c_ContactName' => 'Yoshi Nagase', 'c_Address' => '9-8 Sekimai Musashino-shi', 'c_City' => 'Tokyo', 'c_PostalCode' => '100', 'c_Country' => 'Japan', 'c_Phone' => '(03) 3555-5011', 'product_count' => 3],
            ['i_SupplierID' => 5, 'c_SupplierName' => 'Cooperativa de Quesos', 'c_ContactName' => 'Antonio Taquería', 'c_Address' => 'Calle del Rosal 4', 'c_City' => 'Oviedo', 'c_PostalCode' => '33007', 'c_Country' => 'Spain', 'c_Phone' => '(98) 598 76 54', 'product_count' => 4],
            ['i_SupplierID' => 6, 'c_SupplierName' => "Mayumi's", 'c_ContactName' => 'Mayumi Ohno', 'c_Address' => '92 Setsuko Chuo-ku', 'c_City' => 'Osaka', 'c_PostalCode' => '545', 'c_Country' => 'Japan', 'c_Phone' => '(06) 431-7877', 'product_count' => 3],
            ['i_SupplierID' => 7, 'c_SupplierName' => 'Pavlova, Ltd.', 'c_ContactName' => 'Ian Devling', 'c_Address' => '74 Rose St. Moonie Ponds', 'c_City' => 'Melbourne', 'c_PostalCode' => '3058', 'c_Country' => 'Australia', 'c_Phone' => '(03) 444-2343', 'product_count' => 5],
            ['i_SupplierID' => 8, 'c_SupplierName' => 'Specialty Biscuits, Ltd.', 'c_ContactName' => 'Peter Wilson', 'c_Address' => '29 King\'s Way', 'c_City' => 'Manchester', 'c_PostalCode' => 'M14 GSD', 'c_Country' => 'UK', 'c_Phone' => '(161) 555-4448', 'product_count' => 4]
        ];
    }

    public static function getEmployees() {
        return [
            ['i_EmployeeID' => 1, 'c_FirstName' => 'Nancy', 'c_LastName' => 'Davolio', 'c_BirthDate' => '1968-12-08', 'c_Title' => 'พนักงานขาย (Sales Representative)', 'c_Photo' => 'EmpID1.pic', 'c_Notes' => 'BA in psychology from Colorado State University.'],
            ['i_EmployeeID' => 2, 'c_FirstName' => 'Andrew', 'c_LastName' => 'Fuller', 'c_BirthDate' => '1952-02-19', 'c_Title' => 'ผู้จัดการฝ่ายขาย (Vice President, Sales)', 'c_Photo' => 'EmpID2.pic', 'c_Notes' => 'Ph.D. in international marketing.'],
            ['i_EmployeeID' => 3, 'c_FirstName' => 'Janet', 'c_LastName' => 'Leverling', 'c_BirthDate' => '1963-08-30', 'c_Title' => 'พนักงานขาย (Sales Representative)', 'c_Photo' => 'EmpID3.pic', 'c_Notes' => 'BS degree in chemistry from Boston College.'],
            ['i_EmployeeID' => 4, 'c_FirstName' => 'Margaret', 'c_LastName' => 'Peacock', 'c_BirthDate' => '1958-09-19', 'c_Title' => 'พนักงานขาย (Sales Representative)', 'c_Photo' => 'EmpID4.pic', 'c_Notes' => 'BA in English literature.'],
            ['i_EmployeeID' => 5, 'c_FirstName' => 'Steven', 'c_LastName' => 'Buchanan', 'c_BirthDate' => '1955-03-04', 'c_Title' => 'ผู้จัดการฝ่ายขาย (Sales Manager)', 'c_Photo' => 'EmpID5.pic', 'c_Notes' => 'Graduate of St. Andrews University.'],
            ['i_EmployeeID' => 6, 'c_FirstName' => 'Michael', 'c_LastName' => 'Suyama', 'c_BirthDate' => '1963-07-02', 'c_Title' => 'พนักงานขาย (Sales Representative)', 'c_Photo' => 'EmpID6.pic', 'c_Notes' => 'MA in economics from Sussex University.'],
            ['i_EmployeeID' => 7, 'c_FirstName' => 'Robert', 'c_LastName' => 'King', 'c_BirthDate' => '1960-05-29', 'c_Title' => 'พนักงานขาย (Sales Representative)', 'c_Photo' => 'EmpID7.pic', 'c_Notes' => 'Peace Corps veteran and traveler.'],
            ['i_EmployeeID' => 8, 'c_FirstName' => 'Laura', 'c_LastName' => 'Callahan', 'c_BirthDate' => '1958-01-09', 'c_Title' => 'ผู้ช่วยฝ่ายขาย (Inside Sales Coordinator)', 'c_Photo' => 'EmpID8.pic', 'c_Notes' => 'BA in psychology from University of Washington.'],
            ['i_EmployeeID' => 9, 'c_FirstName' => 'Anne', 'c_LastName' => 'Dodsworth', 'c_BirthDate' => '1969-07-02', 'c_Title' => 'พนักงานขาย (Sales Representative)', 'c_Photo' => 'EmpID9.pic', 'c_Notes' => 'BA degree in English from St. Lawrence College.'],
            ['i_EmployeeID' => 10, 'c_FirstName' => 'Adam', 'c_LastName' => 'West', 'c_BirthDate' => '1928-09-19', 'c_Title' => 'ที่ปรึกษาอาวุโส (Senior Consultant)', 'c_Photo' => 'EmpID10.pic', 'c_Notes' => 'An old chum and executive adviser.']
        ];
    }

    public static function getShippers() {
        return [
            ['i_ShipperID' => 1, 'c_ShipperName' => 'Speedy Express', 'c_Phone' => '(503) 555-9831', 'delivery_count' => 54, 'icon' => '🚀', 'status' => 'พร้อมให้บริการ'],
            ['i_ShipperID' => 2, 'c_ShipperName' => 'United Package', 'c_Phone' => '(503) 555-3199', 'delivery_count' => 74, 'icon' => '📦', 'status' => 'พร้อมให้บริการ'],
            ['i_ShipperID' => 3, 'c_ShipperName' => 'Federal Shipping', 'c_Phone' => '(503) 555-9931', 'delivery_count' => 68, 'icon' => '🛳', 'status' => 'พร้อมให้บริการ']
        ];
    }

    public static function getProducts() {
        return [
            ['i_ProductID' => 1, 'c_ProductName' => 'ชาไทย (Chai)', 'i_SupplierID' => 1, 'i_CategoryID' => 1, 'c_Unit' => '10 กล่อง × 20 ถุง', 'i_Price' => 18.00, 'c_CategoryName' => 'เครื่องดื่ม', 'c_SupplierName' => 'Exotic Liquids', 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 2, 'c_ProductName' => 'น้ำซุปชาม (Chang)', 'i_SupplierID' => 1, 'i_CategoryID' => 1, 'c_Unit' => '24 — 12 ออนซ์', 'i_Price' => 19.00, 'c_CategoryName' => 'เครื่องปรุงรส', 'c_SupplierName' => 'Tokyo Traders', 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 3, 'c_ProductName' => 'น้ำเชื่อมโป๊ยกั๊ก (Aniseed)', 'i_SupplierID' => 1, 'i_CategoryID' => 2, 'c_Unit' => '12 — 550 มล.', 'i_Price' => 10.00, 'c_CategoryName' => 'เครื่องปรุงรส', 'c_SupplierName' => 'Exotic Liquids', 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 4, 'c_ProductName' => 'เครื่องเทศเคจุนเชฟแอนตัน', 'i_SupplierID' => 2, 'i_CategoryID' => 2, 'c_Unit' => '48 — 6 ออนซ์', 'i_Price' => 22.00, 'c_CategoryName' => 'เครื่องปรุงรส', 'c_SupplierName' => 'New Orleans Cajun Delights', 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 5, 'c_ProductName' => 'กัมโบ้มิกซ์เชฟแอนตัน', 'i_SupplierID' => 2, 'i_CategoryID' => 2, 'c_Unit' => '36 กล่อง', 'i_Price' => 21.35, 'c_CategoryName' => 'เครื่องปรุงรส', 'c_SupplierName' => 'New Orleans Cajun Delights', 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 6, 'c_ProductName' => 'แยมบอยเซนเบอร์รี่', 'i_SupplierID' => 3, 'i_CategoryID' => 2, 'c_Unit' => '12 — 8 ออนซ์', 'i_Price' => 25.00, 'c_CategoryName' => 'เครื่องปรุงรส', 'c_SupplierName' => "Grandma Kelly's Homestead", 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 7, 'c_ProductName' => 'ลูกแพร์อบแห้งออร์แกนิก', 'i_SupplierID' => 3, 'i_CategoryID' => 7, 'c_Unit' => '12 — 1 ปอนด์', 'i_Price' => 30.00, 'c_CategoryName' => 'อาหารพิเศษ', 'c_SupplierName' => "Grandma Kelly's Homestead", 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 8, 'c_ProductName' => 'ซอสแครนเบอร์รี', 'i_SupplierID' => 3, 'i_CategoryID' => 2, 'c_Unit' => '12 — 12 ออนซ์', 'i_Price' => 40.00, 'c_CategoryName' => 'เครื่องปรุงรส', 'c_SupplierName' => "Grandma Kelly's Homestead", 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 9, 'c_ProductName' => 'เนื้อมิชิโกเบ', 'i_SupplierID' => 4, 'i_CategoryID' => 6, 'c_Unit' => '18 — 500 กรัม', 'i_Price' => 97.00, 'c_CategoryName' => 'เนื้อสัตว์', 'c_SupplierName' => 'Tokyo Traders', 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 10, 'c_ProductName' => 'อิคุระ (ไข่ปลาแซลมอน)', 'i_SupplierID' => 4, 'i_CategoryID' => 8, 'c_Unit' => '12 — 200 มล.', 'i_Price' => 31.00, 'c_CategoryName' => 'อาหารทะเล', 'c_SupplierName' => 'Tokyo Traders', 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 17, 'c_ProductName' => 'ลูกอมผลไม้รวม', 'i_SupplierID' => 7, 'i_CategoryID' => 3, 'c_Unit' => '16 — 500 กรัม', 'i_Price' => 39.00, 'c_CategoryName' => 'ขนมหวาน', 'c_SupplierName' => 'Pavlova, Ltd.', 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 43, 'c_ProductName' => 'กาแฟอิโปห์ (Ipoh Coffee)', 'i_SupplierID' => 6, 'i_CategoryID' => 1, 'c_Unit' => '16 — 500 กรัม', 'i_Price' => 600.00, 'c_CategoryName' => 'เครื่องดื่ม', 'c_SupplierName' => "Mayumi's", 'status' => 'ตรวจสอบ'],
            ['i_ProductID' => 51, 'c_ProductName' => 'แมนจิมัป (Manjimup)', 'i_SupplierID' => 7, 'i_CategoryID' => 2, 'c_Unit' => '2 กล่อง × 12 ขวด', 'i_Price' => 53.00, 'c_CategoryName' => 'เครื่องปรุงรส', 'c_SupplierName' => 'Pavlova, Ltd.', 'status' => 'ใช้งานอยู่'],
            ['i_ProductID' => 63, 'c_ProductName' => 'แซนด์วิชฟิงเกอร์แช่แข็ง', 'i_SupplierID' => 5, 'i_CategoryID' => 8, 'c_Unit' => '15 กล่อง × 10 ชิ้น', 'i_Price' => 33.25, 'c_CategoryName' => 'อาหารทะเล', 'c_SupplierName' => 'Cooperativa de Quesos', 'status' => 'ไม่ใช้งาน'],
            ['i_ProductID' => 102, 'c_ProductName' => 'NIKE รองเท้าแอร์แมกซ์', 'i_SupplierID' => 8, 'i_CategoryID' => 9, 'c_Unit' => '1 คู่', 'i_Price' => 25000.00, 'c_CategoryName' => 'กีฬา', 'c_SupplierName' => 'Specialty Biscuits, Ltd.', 'status' => 'ตรวจสอบ']
        ];
    }

    public static function getCustomers() {
        return [
            ['i_customerid' => 1, 'c_customername' => 'Alfreds Futterkiste', 'c_contactname' => 'Maria Anders', 'c_address' => 'Obere Str. 57', 'c_city' => 'Berlin', 'c_postalcode' => '12209', 'c_country' => 'Germany'],
            ['i_customerid' => 2, 'c_customername' => 'Ana Trujillo Emparedados', 'c_contactname' => 'Ana Trujillo', 'c_address' => 'Avda. de la Constitución 2222', 'c_city' => 'México D.F.', 'c_postalcode' => '05021', 'c_country' => 'Mexico'],
            ['i_customerid' => 3, 'c_customername' => 'Antonio Moreno Taquería', 'c_contactname' => 'Antonio Moreno', 'c_address' => 'Mataderos 2312', 'c_city' => 'México D.F.', 'c_postalcode' => '05023', 'c_country' => 'Mexico'],
            ['i_customerid' => 4, 'c_customername' => 'Around the Horn', 'c_contactname' => 'Thomas Hardy', 'c_address' => '120 Hanover Sq.', 'c_city' => 'London', 'c_postalcode' => 'WA1 1DP', 'c_country' => 'UK'],
            ['i_customerid' => 5, 'c_customername' => 'Berglunds snabbköp', 'c_contactname' => 'Christina Berglund', 'c_address' => 'Berguvsvägen 8', 'c_city' => 'Luleå', 'c_postalcode' => 'S-958 22', 'c_country' => 'Sweden'],
            ['i_customerid' => 10, 'c_customername' => 'Ernst Handel', 'c_contactname' => 'Roland Mendel', 'c_address' => 'Kirchgasse 6', 'c_city' => 'Graz', 'c_postalcode' => '8010', 'c_country' => 'Austria'],
            ['i_customerid' => 65, 'c_customername' => 'QUICK-Stop', 'c_contactname' => 'Horst Kloss', 'c_address' => 'Taucherstraße 10', 'c_city' => 'Cunewalde', 'c_postalcode' => '01307', 'c_country' => 'Germany'],
            ['i_customerid' => 71, 'c_customername' => 'Save-a-lot Markets', 'c_contactname' => 'Jose Pavarotti', 'c_address' => '187 Suffolk St.', 'c_city' => 'Boise', 'c_postalcode' => '83720', 'c_country' => 'USA'],
            ['i_customerid' => 75, 'c_customername' => 'Rattlesnake Canyon Grocery', 'c_contactname' => 'Paula Wilson', 'c_address' => '2817 Milton Dr.', 'c_city' => 'Albuquerque', 'c_postalcode' => '87110', 'c_country' => 'USA'],
            ['i_customerid' => 37, 'c_customername' => 'Hungry Owl All-Night Grocers', 'c_contactname' => 'Patricia McKenna', 'c_address' => '8 Johnstown Road', 'c_city' => 'Cork', 'c_postalcode' => 'Co. Cork', 'c_country' => 'Ireland']
        ];
    }

    public static function getOrders() {
        return [
            [
                'i_OrderID' => 10443,
                'i_CustomerID' => 10,
                'c_CustomerName' => 'Ernst Handel',
                'i_EmployeeID' => 9,
                'c_EmployeeName' => 'Anne Dodsworth (แอนน์ ด็อดส์เวิร์ท)',
                'c_OrderDate' => '1997-12-02',
                'i_ShipperID' => 3,
                'c_ShipperName' => 'Federal Shipping',
                'status' => 'จัดส่งแล้ว',
                'items_count' => 2,
                'total_amount' => 368.00,
                'items' => [
                    ['i_ProductID' => 1, 'c_ProductName' => 'ชาไทย (Chai)', 'i_Quantity' => 12, 'i_Price' => 18.00, 'total' => 216.00],
                    ['i_ProductID' => 2, 'c_ProductName' => 'น้ำซุปชาม (Chang)', 'i_Quantity' => 8, 'i_Price' => 19.00, 'total' => 152.00]
                ]
            ],
            [
                'i_OrderID' => 10442,
                'i_CustomerID' => 10,
                'c_CustomerName' => 'Ernst Handel',
                'i_EmployeeID' => 2,
                'c_EmployeeName' => 'Andrew Fuller (แอนดรูว์ ฟูลเลอร์)',
                'c_OrderDate' => '1997-12-01',
                'i_ShipperID' => 2,
                'c_ShipperName' => 'United Package',
                'status' => 'กำลังดำเนินการ',
                'items_count' => 3,
                'total_amount' => 540.00,
                'items' => [
                    ['i_ProductID' => 3, 'c_ProductName' => 'น้ำเชื่อมโป๊ยกั๊ก', 'i_Quantity' => 15, 'i_Price' => 10.00, 'total' => 150.00],
                    ['i_ProductID' => 6, 'c_ProductName' => 'แยมบอยเซนเบอร์รี่', 'i_Quantity' => 10, 'i_Price' => 25.00, 'total' => 250.00],
                    ['i_ProductID' => 7, 'c_ProductName' => 'ลูกแพร์อบแห้งออร์แกนิก', 'i_Quantity' => 4, 'i_Price' => 30.00, 'total' => 120.00]
                ]
            ],
            [
                'i_OrderID' => 10441,
                'i_CustomerID' => 4,
                'c_CustomerName' => 'Around the Horn',
                'i_EmployeeID' => 7,
                'c_EmployeeName' => 'Robert King (โรเบิร์ต คิง)',
                'c_OrderDate' => '1997-11-29',
                'i_ShipperID' => 1,
                'c_ShipperName' => 'Speedy Express',
                'status' => 'จัดส่งแล้ว',
                'items_count' => 1,
                'total_amount' => 220.00,
                'items' => [
                    ['i_ProductID' => 4, 'c_ProductName' => 'เครื่องเทศเคจุนเชฟแอนตัน', 'i_Quantity' => 10, 'i_Price' => 22.00, 'total' => 220.00]
                ]
            ],
            [
                'i_OrderID' => 10440,
                'i_CustomerID' => 71,
                'c_CustomerName' => 'Save-a-lot Markets',
                'i_EmployeeID' => 3,
                'c_EmployeeName' => 'Janet Leverling (แจเน็ต เลฟเวอร์ลิง)',
                'c_OrderDate' => '1997-11-28',
                'i_ShipperID' => 2,
                'c_ShipperName' => 'United Package',
                'status' => 'จัดส่งแล้ว',
                'items_count' => 4,
                'total_amount' => 895.50,
                'items' => [
                    ['i_ProductID' => 5, 'c_ProductName' => 'กัมโบ้มิกซ์เชฟแอนตัน', 'i_Quantity' => 20, 'i_Price' => 21.35, 'total' => 427.00],
                    ['i_ProductID' => 8, 'c_ProductName' => 'ซอสแครนเบอร์รี', 'i_Quantity' => 8, 'i_Price' => 40.00, 'total' => 320.00],
                    ['i_ProductID' => 10, 'c_ProductName' => 'อิคุระ (ไข่ปลาแซลมอน)', 'i_Quantity' => 4, 'i_Price' => 31.00, 'total' => 124.00],
                    ['i_ProductID' => 3, 'c_ProductName' => 'น้ำเชื่อมโป๊ยกั๊ก', 'i_Quantity' => 2, 'i_Price' => 10.00, 'total' => 20.00]
                ]
            ],
            [
                'i_OrderID' => 10439,
                'i_CustomerID' => 2,
                'c_CustomerName' => 'Ana Trujillo Emparedados',
                'i_EmployeeID' => 5,
                'c_EmployeeName' => 'Steven Buchanan (สตีเวน บิวแชน)',
                'c_OrderDate' => '1997-11-28',
                'i_ShipperID' => 3,
                'c_ShipperName' => 'Federal Shipping',
                'status' => 'ยกเลิก',
                'items_count' => 2,
                'total_amount' => 180.00,
                'items' => [
                    ['i_ProductID' => 1, 'c_ProductName' => 'ชาไทย', 'i_Quantity' => 10, 'i_Price' => 18.00, 'total' => 180.00]
                ]
            ],
            [
                'i_OrderID' => 10248,
                'i_CustomerID' => 1,
                'c_CustomerName' => 'Alfreds Futterkiste',
                'i_EmployeeID' => 5,
                'c_EmployeeName' => 'Steven Buchanan (สตีเวน บิวแชน)',
                'c_OrderDate' => '1996-07-04',
                'i_ShipperID' => 3,
                'c_ShipperName' => 'Federal Shipping',
                'status' => 'จัดส่งแล้ว',
                'items_count' => 3,
                'total_amount' => 440.00,
                'items' => [
                    ['i_ProductID' => 11, 'c_ProductName' => 'Queso Cabrales', 'i_Quantity' => 12, 'i_Price' => 21.00, 'total' => 252.00],
                    ['i_ProductID' => 42, 'c_ProductName' => 'Singaporean Hokkien Fried Mee', 'i_Quantity' => 10, 'i_Price' => 14.00, 'total' => 140.00],
                    ['i_ProductID' => 72, 'c_ProductName' => 'Mozzarella di Giovanni', 'i_Quantity' => 5, 'i_Price' => 34.80, 'total' => 174.00]
                ]
            ]
        ];
    }
}

