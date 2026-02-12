# 🚀 QUICK START - BACKEND LARAVEL

## TL;DR - Khởi động nhanh

```bash
# Lần đầu tiên
1. Mở XAMPP → Start MySQL
2. Tạo database: mountain_booking_db
3. Double click: SETUP-BACKEND.bat
4. Double click: START-BACKEND.bat

# Lần sau
1. Mở XAMPP → Start MySQL
2. Double click: START-BACKEND.bat
```

---

## 📁 CÁC FILE QUAN TRỌNG

### 1. **START-BACKEND.bat** ⭐

Khởi động server backend

```bash
# Chức năng:
- Start Laravel server với host 0.0.0.0:8000
- Truy cập được từ mạng LAN
- App mobile kết nối được

# Cách dùng:
Double click hoặc chạy: START-BACKEND.bat
```

### 2. **CHECK-BACKEND.bat** 🔍

Kiểm tra backend đã chạy đúng chưa

```bash
# Chức năng:
- Check port 8000 có đang listen không
- Hiển thị IP để truy cập từ mobile
- Test API endpoints

# Cách dùng:
Double click hoặc chạy: CHECK-BACKEND.bat
```

### 3. **SETUP-BACKEND.bat** ⚙️

Setup backend lần đầu tiên

```bash
# Chức năng:
- Composer install
- Copy .env
- Generate key
- Run migrations
- Seed data
- Storage link

# Cách dùng:
Double click hoặc chạy: SETUP-BACKEND.bat
```

### 4. **START_BACKEND_README.md** 📖

Hướng dẫn chi tiết đầy đủ

---

## 🎯 CÁC SCENARIO SỬ DỤNG

### Scenario 1: Lần đầu setup project

```bash
1. Clone/copy project về máy
2. Mở XAMPP Control Panel
3. Start Apache + MySQL
4. Vào phpMyAdmin (http://localhost/phpmyadmin)
5. Tạo database mới: mountain_booking_db
6. Chạy: SETUP-BACKEND.bat
7. Chạy: START-BACKEND.bat
8. Chạy: CHECK-BACKEND.bat (để kiểm tra)
```

### Scenario 2: Khởi động hàng ngày

```bash
1. Mở XAMPP → Start MySQL
2. Chạy: START-BACKEND.bat
3. Done! Backend đang chạy
```

### Scenario 3: Debug khi app không kết nối được

```bash
1. Chạy: CHECK-BACKEND.bat
2. Xem IP hiển thị (ví dụ: 192.168.0.102)
3. Mở browser điện thoại
4. Vào: http://192.168.0.102:8000/api/tours
5. Nếu thấy JSON → Backend OK
6. Nếu không → Check WiFi, Firewall
```

### Scenario 4: Reset database

```bash
1. Stop backend (Ctrl+C)
2. Chạy:
   php artisan migrate:fresh --seed
3. Khởi động lại: START-BACKEND.bat
```

---

## 🔧 COMMANDS THƯỜNG DÙNG

### Migrations

```bash
# Chạy migrations
php artisan migrate

# Reset và chạy lại
php artisan migrate:fresh

# Rollback
php artisan migrate:rollback

# Fresh + Seed
php artisan migrate:fresh --seed
```

### Seeders

```bash
# Chạy tất cả seeders
php artisan db:seed

# Chạy seeder cụ thể
php artisan db:seed --class=TourSeeder

# Refresh autoload
composer dump-autoload
```

### Cache & Config

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache config (production)
php artisan config:cache
php artisan route:cache
```

### Artisan helpers

```bash
# List routes
php artisan route:list

# Check routes API
php artisan route:list --path=api

# Tinker (Laravel console)
php artisan tinker
```

---

## 📊 DATABASE

### Tạo database

```sql
CREATE DATABASE mountain_booking_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

### Tables chính

- `users` - Người dùng
- `tours` - Tours leo núi
- `bookings` - Đặt tour
- `personal_access_tokens` - Sanctum tokens

### Dữ liệu mẫu (Seeder)

- 8 tours: Fansipan, Tà Xùa, Bạch Mộc Lương Tử, Yên Tử, Phan Xi Păng, Lảo Thẩn, Chúa Pú Luông, Tà Chì Nhù

---

## 🌐 API ENDPOINTS

### Public endpoints

```
GET  /api/health          - Health check
GET  /api/tours           - Danh sách tours
GET  /api/tours/{id}      - Chi tiết tour
POST /api/register        - Đăng ký
POST /api/login           - Đăng nhập
```

### Protected endpoints (cần token)

```
GET  /api/user            - Thông tin user
POST /api/logout          - Đăng xuất
GET  /api/bookings        - Lịch sử bookings
POST /api/bookings        - Tạo booking mới
```

### Test endpoints

```bash
# Health check
curl http://localhost:8000/api/health

# Get tours
curl http://localhost:8000/api/tours

# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "0912345678",
    "password": "12345678",
    "password_confirmation": "12345678"
  }'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "12345678"
  }'
```

---

## 🐛 TROUBLESHOOTING

### Port 8000 đã bị dùng

```bash
# Tìm process
netstat -ano | findstr :8000

# Kill process
taskkill /PID <PID> /F

# Hoặc dùng port khác
php artisan serve --host=0.0.0.0 --port=8001
```

### Database connection error

```bash
# Check:
1. MySQL đang chạy (XAMPP)
2. Database đã tạo: mountain_booking_db
3. File .env đúng config
4. Test connection: php artisan migrate
```

### Sanctum error

```bash
# Publish sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Migrate
php artisan migrate
```

### App không kết nối được backend

```bash
# Checklist:
1. Backend chạy với --host=0.0.0.0 ✅
2. Kiểm tra IP: ipconfig
3. Test browser điện thoại: http://IP:8000/api/tours
4. App config: API_URL đúng IP
5. capacitor.config.ts: androidScheme: 'http'
6. AndroidManifest.xml: usesCleartextTraffic="true"
```

---

## 📝 FILES CẤU HÌNH

### .env

```env
APP_NAME="Mountain Booking API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mountain_booking_db
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,192.168.0.102
```

### config/cors.php

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

---

## 🔐 AUTHENTICATION (Sanctum)

### Flow

```
1. Register → Nhận token
2. Login → Nhận token
3. Dùng token cho protected endpoints
4. Logout → Revoke token
```

### Request với token

```bash
curl -H "Authorization: Bearer <token>" \
  http://localhost:8000/api/user
```

### Token trong Laravel

```php
// Tạo token
$token = $user->createToken('mobile-app')->plainTextToken;

// Revoke token hiện tại
$request->user()->currentAccessToken()->delete();

// Revoke tất cả tokens
$user->tokens()->delete();
```

---

## 📞 LƯU Ý QUAN TRỌNG

### ⚠️ KHÔNG BAO GIỜ

- ❌ KHÔNG chỉ chạy `php artisan serve` (thiếu --host)
- ❌ KHÔNG commit file .env lên git
- ❌ KHÔNG dùng APP_DEBUG=true trên production
- ❌ KHÔNG để database password trống trên production

### ✅ NÊN LÀM

- ✅ Luôn dùng `--host=0.0.0.0` để app mobile kết nối được
- ✅ Check backend bằng CHECK-BACKEND.bat trước khi test app
- ✅ Backup database định kỳ
- ✅ Dùng .env.example làm template
- ✅ Test API bằng browser/Postman trước

---

## 🎓 ĐỌC THÊM

- [START_BACKEND_README.md](START_BACKEND_README.md) - Hướng dẫn chi tiết
- [Laravel Documentation](https://laravel.com/docs)
- [Sanctum Documentation](https://laravel.com/docs/sanctum)

---

## ✅ CHECKLIST

### Setup lần đầu

- [ ] XAMPP đã cài + MySQL chạy
- [ ] Composer đã cài
- [ ] Database `mountain_booking_db` đã tạo
- [ ] Chạy SETUP-BACKEND.bat
- [ ] File .env đã config đúng
- [ ] Migrations chạy thành công
- [ ] Seeders chạy thành công

### Khởi động hàng ngày

- [ ] MySQL đang chạy
- [ ] Chạy START-BACKEND.bat
- [ ] Check bằng CHECK-BACKEND.bat
- [ ] Test API: http://localhost:8000/api/tours

### Kết nối mobile app

- [ ] Backend bind 0.0.0.0 (không phải 127.0.0.1)
- [ ] Lấy IP máy tính: ipconfig
- [ ] Máy tính và điện thoại cùng WiFi
- [ ] Test browser điện thoại: http://IP:8000/api/tours
- [ ] App config API_URL đúng
- [ ] Rebuild APK sau khi đổi config

---

**🚀 Happy Coding!**
