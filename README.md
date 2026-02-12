# 🏔️ Mountain Booking - Backend API

Backend API Laravel cho ứng dụng đặt tour leo núi Mountain Booking.

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://mysql.com)
[![Railway](https://img.shields.io/badge/Deploy-Railway-blueviolet.svg)](https://railway.app)

---

## 📋 Mục lục

- [Tính năng](#-tính-năng)
- [Tech Stack](#-tech-stack)
- [Cài đặt Local](#-cài-đặt-local)
- [Deploy lên Railway](#-deploy-lên-railway)
- [API Endpoints](#-api-endpoints)
- [Database Schema](#-database-schema)
- [Troubleshooting](#-troubleshooting)

---

## ✨ Tính năng

- ✅ **Authentication**: Register, Login, Logout với Laravel Sanctum
- ✅ **Tour Management**: Browse, search, filter tours
- ✅ **Booking System**: Đặt tour, thanh toán, quản lý booking
- ✅ **Payment Integration**: VNPay, Cash on arrival
- ✅ **Coupon System**: Mã giảm giá, tự động tính discount
- ✅ **Review & Rating**: Đánh giá tour sau khi hoàn thành
- ✅ **Admin Panel**: Quản lý tours, bookings, users
- ✅ **Role-based Access**: Admin, Super Admin, Booking Manager, Content Manager
- ✅ **API Resources**: Chuẩn REST API với pagination

---

## 🛠️ Tech Stack

- **Framework**: Laravel 12
- **Database**: MySQL 8.0
- **Authentication**: Laravel Sanctum (Token-based)
- **Deployment**: Railway.app
- **Version Control**: Git & GitHub

---

## 💻 Cài đặt Local

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Git

### Installation Steps

```bash
# 1. Clone repository
git clone https://github.com/YOUR_USERNAME/mountain-booking-api.git
cd mountain-booking-api

# 2. Install dependencies
composer install

# 3. Setup environment
copy .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure database trong .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mountain_booking
DB_USERNAME=root
DB_PASSWORD=

# 6. Create database
mysql -u root -p
CREATE DATABASE mountain_booking;
exit

# 7. Run migrations & seeders
php artisan migrate --seed

# 8. Start development server
php artisan serve
```

Server sẽ chạy tại: **http://localhost:8000**

Test API: **http://localhost:8000/api/tours**

---

## 🚂 Deploy lên Railway

### Quick Deploy (3 phút)

**1️⃣ Push lên GitHub:**

```bash
# Chạy script tự động
PUSH_TO_GITHUB.bat
```

**2️⃣ Deploy trên Railway:**

- Truy cập: https://railway.app
- New Project → **Deploy from GitHub repo**
- Chọn repo: **mountain-booking-api**
- Add **MySQL** database
- Config **Environment Variables** (copy từ `.env.railway`)
- Generate **Domain**
- ✅ Done!

**3️⃣ Run migrations:**

```bash
npm install -g @railway/cli
railway login
railway link
railway run php artisan migrate --force --seed
```

### 📖 Hướng dẫn chi tiết

Xem file: **[RAILWAY_DEPLOYMENT_GUIDE.md](RAILWAY_DEPLOYMENT_GUIDE.md)**

---

## 🔌 API Endpoints

Base URL Production: `https://your-app.railway.app/api`  
Base URL Local: `http://localhost:8000/api`

### Authentication

| Method | Endpoint           | Description        | Auth Required |
| ------ | ------------------ | ------------------ | ------------- |
| POST   | `/register`        | Đăng ký tài khoản  | ❌            |
| POST   | `/login`           | Đăng nhập          | ❌            |
| POST   | `/logout`          | Đăng xuất          | ✅            |
| GET    | `/user`            | Lấy thông tin user | ✅            |
| PUT    | `/user`            | Cập nhật profile   | ✅            |
| POST   | `/change-password` | Đổi mật khẩu       | ✅            |

### Tours

| Method | Endpoint                | Description                  | Auth Required |
| ------ | ----------------------- | ---------------------------- | ------------- |
| GET    | `/tours`                | Danh sách tours (pagination) | ❌            |
| GET    | `/tours/{id}`           | Chi tiết tour                | ❌            |
| GET    | `/tours/{id}/schedules` | Lịch khởi hành               | ❌            |
| GET    | `/tours/{id}/reviews`   | Đánh giá tour                | ❌            |

**Query Parameters:**

- `search` - Tìm kiếm theo tên tour
- `difficulty` - Lọc theo độ khó (easy, moderate, challenging, expert)
- `min_price`, `max_price` - Lọc theo giá
- `duration` - Lọc theo số ngày
- `featured` - Chỉ lấy tours nổi bật

### Bookings

| Method | Endpoint                | Description                | Auth Required |
| ------ | ----------------------- | -------------------------- | ------------- |
| GET    | `/bookings`             | Danh sách booking của user | ✅            |
| POST   | `/bookings`             | Tạo booking mới            | ✅            |
| GET    | `/bookings/{id}`        | Chi tiết booking           | ✅            |
| PUT    | `/bookings/{id}/cancel` | Hủy booking                | ✅            |

### Coupons

| Method | Endpoint            | Description          | Auth Required |
| ------ | ------------------- | -------------------- | ------------- |
| POST   | `/coupons/validate` | Kiểm tra mã giảm giá | ✅            |

### Reviews

| Method | Endpoint        | Description | Auth Required |
| ------ | --------------- | ----------- | ------------- |
| POST   | `/reviews`      | Viết review | ✅            |
| PUT    | `/reviews/{id}` | Sửa review  | ✅            |
| DELETE | `/reviews/{id}` | Xóa review  | ✅            |

---

## 🗄️ Database Schema

### Main Tables

**users**

- id, name, email, password
- phone, date_of_birth, gender
- emergency_contact, role
- timestamps

**tours**

- id, title, description
- location, duration, difficulty
- price, max_participants
- featured, is_active
- timestamps

**tour_schedules**

- id, tour_id, start_date, end_date
- available_slots, status
- timestamps

**bookings**

- id, user_id, tour_id, schedule_id
- number_of_participants, total_price
- status, payment_method
- timestamps

**payments**

- id, booking_id, amount
- payment_method, status
- transaction_id
- timestamps

**coupons**

- id, code, discount_type, discount_value
- min_purchase, max_discount
- valid_from, valid_until
- usage_limit, times_used
- timestamps

**reviews**

- id, user_id, tour_id
- rating, comment
- timestamps

---

## 🔐 Authentication

Project sử dụng **Laravel Sanctum** với token-based authentication.

### Flow:

1. User register/login
2. Backend tạo token
3. Mobile app lưu token trong localStorage
4. Mọi request gửi kèm header: `Authorization: Bearer {token}`
5. Backend verify token qua Sanctum middleware

### Example:

```bash
# Login
curl -X POST https://your-app.railway.app/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password123"}'

# Response
{
  "user": {...},
  "token": "1|xxxxxxxxxxxxxx"
}

# Use token
curl https://your-app.railway.app/api/user \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxx"
```

---

## 👥 Roles & Permissions

| Role                | Quyền                           |
| ------------------- | ------------------------------- |
| **User**            | Browse tours, booking, review   |
| **Content Manager** | Quản lý tours, blogs            |
| **Booking Manager** | Quản lý bookings, payments      |
| **Admin**           | Full access trừ system settings |
| **Super Admin**     | Full system access              |

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=TourTest

# With coverage
php artisan test --coverage
```

---

## 🐛 Troubleshooting

### Lỗi: "500 Internal Server Error"

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Lỗi: "Database connection failed"

Kiểm tra file `.env`:

- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` đúng chưa
- MySQL service đã chạy chưa

### Lỗi: "CORS Policy"

Cấu hình trong `.env`:

```env
SANCTUM_STATEFUL_DOMAINS=*
```

Hoặc update [`config/sanctum.php`](config/sanctum.php)

---

## 📁 Project Structure

```
mountain_booking_web/
├── app/
│   ├── Console/               # Commands & Scheduling
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/          # API Controllers
│   │   ├── Middleware/       # Auth, Admin middleware
│   │   └── Resources/        # API Resources (JSON transform)
│   └── Models/               # Eloquent Models
├── config/                   # Configuration files
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Sample data seeders
├── routes/
│   ├── api.php              # API routes
│   ├── web.php              # Web routes
│   └── console.php          # Console routes
├── storage/                 # Files, logs, cache
├── Procfile                 # Railway start command
├── nixpacks.toml           # Railway build config
├── .env.railway            # Environment template for Railway
└── composer.json           # PHP dependencies
```

---

## 🔄 Deployment Updates

Sau khi có thay đổi code:

```bash
# 1. Commit & push
git add .
git commit -m "Update feature X"
git push origin main

# 2. Railway tự động deploy (30-60 giây)

# 3. Nếu có migration mới
railway run php artisan migrate --force
```

---

## 📊 Monitoring

### Railway Dashboard

- **Logs**: Real-time logs
- **Metrics**: CPU, Memory usage
- **Deployments**: History & rollback

### Health Check

```
GET /up
```

Returns `200 OK` nếu service healthy

---

## 🌐 Related Projects

- **Mobile App**: [`../mountain_booking_app/`](../mountain_booking_app/)
- **UI Prototypes**: [`../LeoNuiUi/`](../LeoNuiUi/)

---

## 📞 Support

- **Issues**: GitHub Issues
- **Railway Support**: https://discord.gg/railway
- **Laravel Docs**: https://laravel.com/docs

---

## 📝 License

MIT License - Free to use

---

## 👨‍💻 Author

**Mountain Booking Team**  
📅 Last Updated: February 2026  
🚀 Version: 1.0.0

---

## 🎯 Quick Links

- 📚 [Railway Deployment Guide](RAILWAY_DEPLOYMENT_GUIDE.md)
- 🔧 [Local Development Guide](README_RAILWAY.md)
- 🚀 [Push to GitHub Script](PUSH_TO_GITHUB.bat)

---

**Happy Coding! 🏔️**
