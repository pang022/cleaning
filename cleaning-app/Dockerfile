FROM php:8.2-apache

# คัดลอกไฟล์ทั้งหมดเข้าไปในคอนเทนเนอร์ตามปกติ
COPY . /var/www/html/

# 🔥 บรรทัดไม้ตาย: เปลี่ยนสิทธิ์เจ้าของไฟล์และโฟลเดอร์ทั้งหมดให้เป็นของ Apache (www-data)
# เพื่อให้ SQLite สามารถเขียนไฟล์ database.db และสร้างไฟล์ในโหมด WAL ได้
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80