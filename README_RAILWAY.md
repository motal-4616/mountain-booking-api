# Mountain Booking Backend - Railway Deployment

Backend API cho ứng dụng Mountain Booking, deploy trên Railway.app

## 🚀 Quick Start (Local Development)

```bash
# 1. Install dependencies
composer install

# 2. Setup environment
copy .env.example .env
php artisan key:generate

# 3. Setup database
# Tạo database 'mountain_booking' trong MySQL
php artisan migrate --seed

# 4. Start server
php artisan serve
```

API sẽ chạy tại: `http://localhost:8000`

## 🚂 Deploy lên Railway

### Prerequisites

- Git đã cài đặt
- GitHub account
- Railway account (free tier OK)

### Deployment Steps

**1. Push code lên GitHub:**

```bash
# Chạy script tự động
PUSH_TO_GITHUB.bat

# Hoặc thủ công:
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/mountain-booking-api.git
git push -u origin main
```

**2. Deploy trên Railway:**

Đọc hướng dẫn chi tiết trong file: **[RAILWAY_DEPLOYMENT_GUIDE.md](RAILWAY_DEPLOYMENT_GUIDE.md)**

Hoặc làm theo:

- Truy cập https://railway.app
- New Project → Deploy from GitHub
- Chọn repo `mountain-booking-api`
- Add MySQL database
- Config environment variables (copy từ `.env.railway`)
- Generate domain
- Run migrations

**3. Quick deploy với Railway CLI:**

```bash
npm install -g @railway/cli
railway link
railway run php artisan migrate --force
railway run php artisan db:seed --force
```

## 📁 Project Structure

```
mountain_booking_web/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/   # API Controllers
│   │   ├── Middleware/        # Auth, Admin middleware
│   │   └── Resources/         # API Resources
│   └── Models/                # Eloquent Models
├── config/                    # Configuration files
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Sample data
├── routes/
│   ├── api.php               # API routes
│   └── web.php               # Web routes
├── Procfile                  # Railway start command
├── nixpacks.toml            # Railway build config
└── .env.railway             # Environment template
```

## 🔌 API Endpoints

Base URL: `https://your-app.railway.app/api`

### Authentication

```
POST   /register              # Register new user
POST   /login                 # Login
POST   /logout                # Logout (auth required)
GET    /user                  # Get current user (auth required)
```

### Tours

```
GET    /tours                 # List all tours (pagination, filters)
GET    /tours/{id}            # Tour details
GET    /tours/{id}/schedules  # Tour schedules
GET    /tours/{id}/reviews    # Tour reviews
```

### Bookings

```
GET    /bookings              # User's bookings
POST   /bookings              # Create booking
GET    /bookings/{id}         # Booking details
PUT    /bookings/{id}/cancel  # Cancel booking
```

### Coupons

```
POST   /coupons/validate      # Validate coupon code
```

Full API docs: Check Postman collection (nếu có)

## 🗄️ Database

- **MySQL** (production on Railway)
- **MySQL/MariaDB** (local development)

### Main Tables:

- users
- tours
- tour_schedules
- bookings
- payments
- coupons
- reviews
- user_follows
- blogs

## 🔐 Authentication

- Laravel Sanctum (Token-based)
- Token expires: Never (configurable in `config/sanctum.php`)
- Admin roles: admin, super_admin, booking_manager, content_manager

## 🛠️ Development Commands

```bash
# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed data
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Create migration
php artisan make:migration create_tablename_table

# Create model
php artisan make:model ModelName -m

# Create controller
php artisan make:controller Api/ControllerName
```

## 📊 Monitoring

### Railway Dashboard

- View logs: `railway logs --follow`
- Restart: Railway Dashboard → Service → Restart
- Metrics: CPU, Memory usage trong dashboard

### Health Check

```bash
curl https://your-app.railway.app/up
```

## 🐛 Troubleshooting

### 500 Error

```bash
# Check logs
railway logs

# Clear Laravel cache
railway run php artisan config:clear
railway run php artisan cache:clear
```

### Database Connection Error

- Kiểm tra environment variables
- Đảm bảo MySQL service đang chạy
- Verify DB credentials

### CORS Issues

- Set `SANCTUM_STATEFUL_DOMAINS=*` trong Railway Variables
- Restart service

## 📦 Production Checklist

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Database migrated & seeded
- [ ] Environment variables configured
- [ ] Domain generated
- [ ] API tested
- [ ] Mobile app updated với production URL

## 💰 Costs

- **Railway Free Tier**: $5 credit/month
- **Hobby Plan**: $5/month
- **Pro Plan**: $20/month

## 📞 Support

- Railway Discord: https://discord.gg/railway
- Laravel Docs: https://laravel.com/docs
- Project Issues: GitHub Issues

## 🔗 Links

- **Frontend Mobile App**: `../mountain_booking_app/`
- **UI Prototypes**: `../LeoNuiUi/`
- **Deployment Guide**: [RAILWAY_DEPLOYMENT_GUIDE.md](RAILWAY_DEPLOYMENT_GUIDE.md)

---

**Created by:** Mountain Booking Team  
**Last Updated:** February 2026  
**Version:** 1.0.0
