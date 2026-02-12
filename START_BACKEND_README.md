# HƯỚNG DẪN KHỞI ĐỘNG BACKEND LARAVEL

## 🚀 CÁCH ĐÚNG ĐỂ KHỞI ĐỘNG BACKEND

### ⚠️ QUAN TRỌNG: Không chỉ chạy `php artisan serve`!

Khi chạy đơn giản `php artisan serve`, server sẽ:

- ❌ Chỉ bind vào `127.0.0.1` (localhost)
- ❌ KHÔNG thể truy cập từ điện thoại/thiết bị khác
- ❌ KHÔNG thể truy cập từ mạng LAN
- ❌ App mobile sẽ KHÔNG kết nối được!

### ✅ CÁCH ĐÚNG:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Tại sao?**

- ✅ Bind vào `0.0.0.0` → Lắng nghe TẤT CẢ network interfaces
- ✅ Truy cập được từ `localhost`, `127.0.0.1`, `192.168.x.x`
- ✅ Điện thoại/thiết bị trong cùng mạng có thể kết nối
- ✅ App mobile kết nối được qua IP máy tính

---

## 📋 CÁC BƯỚC KHỞI ĐỘNG ĐẦY ĐỦ

### 1. Kiểm tra chuẩn bị

```bash
# Kiểm tra PHP version (cần >= 8.1)
php --version

# Kiểm tra Composer
composer --version

# Kiểm tra MySQL/MariaDB đang chạy
# Mở XAMPP Control Panel → Start Apache + MySQL
```

### 2. Cài đặt dependencies (Lần đầu tiên)

```bash
# Install PHP packages
composer install

# Copy file .env
copy .env.example .env

# Generate application key
php artisan key:generate

# Tạo symbolic link cho storage
php artisan storage:link
```

### 3. Cấu hình Database

Sửa file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mountain_booking_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Chạy Migration & Seeder

```bash
# Tạo database (nếu chưa có)
# Vào phpMyAdmin tạo database: mountain_booking_db

# Chạy migrations
php artisan migrate

# Chạy seeders (tạo dữ liệu mẫu)
php artisan db:seed --class=TourSeeder
```

### 5. Khởi động Backend ✅

**Cách 1: Chạy file batch (KHUYÊN DÙNG)**

```bash
# Double click hoặc chạy:
START-BACKEND.bat
```

**Cách 2: Chạy command thủ công**

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Cách 3: Với XAMPP (Alternative)**

```bash
# Nếu muốn dùng port 80 thông qua Apache
# Cấu hình Virtual Host trong XAMPP
```

---

## 🔍 KIỂM TRA BACKEND ĐÃ CHẠY ĐÚNG

### 1. Kiểm tra từ máy tính (localhost)

```bash
# Test API health
curl http://localhost:8000/api/health

# Test API tours
curl http://localhost:8000/api/tours

# Hoặc mở browser:
# http://localhost:8000
# http://localhost:8000/api/tours
```

### 2. Lấy IP máy tính

```bash
# Windows
ipconfig

# Tìm IPv4 Address của WiFi/Ethernet
# Ví dụ: 192.168.0.102
```

### 3. Kiểm tra từ điện thoại

```bash
# Mở browser trên điện thoại (cùng WiFi)
# http://192.168.0.102:8000/api/tours

# Nếu thấy JSON data → Backend hoạt động đúng!
```

### 4. Kiểm tra server đang bind đúng

```bash
# Check port 8000
netstat -ano | findstr :8000

# Kết quả đúng:
TCP    0.0.0.0:8000           0.0.0.0:0              LISTENING

# ❌ SAI nếu thấy:
TCP    127.0.0.1:8000         0.0.0.0:0              LISTENING
# → Chỉ localhost, không truy cập được từ mạng!
```

---

## 🛠️ CÁC FILE BATCH HỖ TRỢ

### 1. START-BACKEND.bat ⭐ KHUYÊN DÙNG

**Chức năng:**

- ✅ Khởi động backend với host `0.0.0.0`
- ✅ Port `8000`
- ✅ Hiển thị thông tin truy cập
- ✅ Cảnh báo nếu port đã bị chiếm

**Cách dùng:**

```bash
# Double click hoặc:
START-BACKEND.bat
```

### 2. CHECK-BACKEND.bat (Có thể tạo thêm)

**Chức năng:**

- Kiểm tra backend đã chạy chưa
- Hiển thị IP để truy cập
- Test API endpoints

---

## 🐛 TROUBLESHOOTING

### Lỗi: "Port 8000 already in use"

**Nguyên nhân:** Port 8000 đã bị chiếm

**Giải pháp:**

```bash
# Tìm process đang dùng port 8000
netstat -ano | findstr :8000

# Kill process (thay <PID> bằng số thực tế)
taskkill /PID <PID> /F

# Hoặc dùng port khác:
php artisan serve --host=0.0.0.0 --port=8001
```

### Lỗi: "SQLSTATE[HY000] [1049] Unknown database"

**Nguyên nhân:** Database chưa được tạo

**Giải pháp:**

```bash
# Vào phpMyAdmin (http://localhost/phpmyadmin)
# Tạo database mới: mountain_booking_db
# Hoặc chạy SQL:
CREATE DATABASE mountain_booking_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Lỗi: "No application encryption key"

**Nguyên nhân:** Thiếu APP_KEY trong .env

**Giải pháp:**

```bash
php artisan key:generate
```

### Lỗi: "Class 'TourSeeder' not found"

**Nguyên nhân:** Seeder chưa được autoload

**Giải pháp:**

```bash
composer dump-autoload
php artisan db:seed --class=TourSeeder
```

### Frontend không kết nối được Backend

**Checklist:**

1. ✅ Backend đang chạy với `--host=0.0.0.0`
2. ✅ Port 8000 mở (kiểm tra Firewall)
3. ✅ Máy tính và điện thoại cùng WiFi
4. ✅ IP đúng trong file `.env` của app mobile
5. ✅ `androidScheme: 'http'` trong capacitor.config.ts
6. ✅ `usesCleartextTraffic="true"` trong AndroidManifest.xml

**Test:**

```bash
# 1. Test từ máy tính
curl http://localhost:8000/api/tours

# 2. Test từ điện thoại browser
# Mở: http://192.168.0.102:8000/api/tours
# (Thay IP thực tế)

# 3. Nếu browser hiển thị JSON → Backend OK
# 4. Nếu app vẫn lỗi → Kiểm tra app config
```

---

## 📊 CÁC ENDPOINT API QUAN TRỌNG

```bash
# Health check
GET http://localhost:8000/api/health

# Get all tours
GET http://localhost:8000/api/tours

# Get tour detail
GET http://localhost:8000/api/tours/{id}

# Register
POST http://localhost:8000/api/register
Body: { name, email, phone, password, password_confirmation }

# Login
POST http://localhost:8000/api/login
Body: { email, password }

# Logout (cần token)
POST http://localhost:8000/api/logout
Header: Authorization: Bearer {token}

# Get user profile (cần token)
GET http://localhost:8000/api/user
Header: Authorization: Bearer {token}
```

---

## 🔐 SANCTUM AUTHENTICATION

Backend sử dụng Laravel Sanctum cho authentication:

### 1. Register/Login flow

```bash
# 1. User register → Nhận token
POST /api/register

# 2. User login → Nhận token
POST /api/login
Response: { token: "1|xxxx", user: {...} }

# 3. Dùng token cho các request khác
Header: Authorization: Bearer 1|xxxx
```

### 2. Token expiration

- Default token không expire
- Có thể config trong `config/sanctum.php`

### 3. Logout

```bash
# Revoke token hiện tại
POST /api/logout
Header: Authorization: Bearer {token}
```

---

## 📝 LOGS & DEBUGGING

### 1. Laravel logs

```bash
# Xem logs
tail -f storage/logs/laravel.log

# Hoặc mở file:
storage/logs/laravel-YYYY-MM-DD.log
```

### 2. Query logs

```php
// Thêm vào AppServiceProvider.php để log queries
DB::listen(function($query) {
    Log::info($query->sql);
    Log::info($query->bindings);
});
```

### 3. Debug mode

```env
# .env
APP_DEBUG=true
LOG_LEVEL=debug
```

---

## 🎯 TÓM TẮT NHANH

**Khởi động backend đúng cách:**

```bash
# Cách 1: File batch
START-BACKEND.bat

# Cách 2: Command
php artisan serve --host=0.0.0.0 --port=8000
```

**Kiểm tra:**

```bash
# Localhost
http://localhost:8000/api/tours

# Từ mạng (thay IP thực tế)
http://192.168.0.102:8000/api/tours

# Netstat check
netstat -ano | findstr :8000
# → Phải thấy 0.0.0.0:8000 LISTENING
```

**Lưu ý:**

- ⚠️ KHÔNG chỉ chạy `php artisan serve`
- ✅ PHẢI thêm `--host=0.0.0.0`
- ✅ Kiểm tra IP máy tính bằng `ipconfig`
- ✅ Máy tính và điện thoại cùng WiFi
- ✅ Test bằng browser trước khi test app

---

## 🆘 HỖ TRỢ

Nếu vẫn gặp vấn đề:

1. Kiểm tra log: `storage/logs/laravel.log`
2. Kiểm tra XAMPP: Apache + MySQL đang chạy
3. Kiểm tra Firewall: Port 8000 có mở không
4. Kiểm tra WiFi: Cùng mạng với điện thoại
5. Test API bằng Postman hoặc curl
6. Kiểm tra file `.env` đã config đúng

**File quan trọng:**

- `.env` - Cấu hình database, app
- `START-BACKEND.bat` - Script khởi động
- `routes/api.php` - Định nghĩa API routes
- `app/Http/Controllers/` - Controllers xử lý logic
