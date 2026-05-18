# CleanCheck — ระบบ Checklist ทำความสะอาด
**Stack: HTML/CSS/JS + PHP + SQLite**

## โครงสร้างไฟล์
```
cleaning-app/
├── index.php      ← Admin: จัดการจุด Checkpoint + สร้าง QR
├── report.php     ← Admin: รายงานรายวัน / รายเดือน
├── scan.php       ← หน้าแม่บ้านสแกน QR แล้วกรอก Checklist
├── api.php        ← Backend API (SQLite)
├── style.css      ← CSS หลัก
└── database.db    ← สร้างอัตโนมัติเมื่อรันครั้งแรก
```

## วิธีติดตั้ง

### ต้องการ
- PHP 7.4+ พร้อม `sqlite3` extension

### รันด้วย PHP Built-in Server
```bash
cd cleaning-app
php -S localhost:8080
```
เปิด http://localhost:8080/index.php

### รันบน Apache/XAMPP
วางโฟลเดอร์ใน `htdocs/` แล้วเปิด http://localhost/cleaning-app/

---

## Flow การใช้งาน

### Admin (หัวหน้า)
1. เปิด `index.php`
2. กด **เพิ่มจุด** → ตั้งชื่อ / ตำแหน่ง / รายการ Checklist
3. กดปุ่ม **📱 QR** เพื่อดู QR Code → ดาวน์โหลดแล้วพิมพ์ติดไว้ที่จุด
4. ดูรายงานที่ `report.php`

### แม่บ้าน
1. สแกน QR Code ด้วยมือถือ
2. กรอกชื่อ + ตำแหน่ง
3. Tick รายการที่ทำเสร็จ
4. กด **บันทึก**

---

## API Endpoints

| action | Method | คำอธิบาย |
|--------|--------|----------|
| list_checkpoints | GET | ดึงจุดทั้งหมด |
| get_checkpoint | GET + token | ดึงจุด + รายการ |
| create_checkpoint | POST | สร้างจุดใหม่ |
| update_checkpoint | POST | แก้ไขจุด |
| delete_checkpoint | POST | ลบจุด |
| toggle_checkpoint | POST | เปิด/ปิดจุด |
| submit | POST | บันทึก checklist |
| report_daily | GET + date | รายงานรายวัน |
| report_monthly | GET + month | รายงานรายเดือน |
| report_detail | GET + sid | รายละเอียด submission |
