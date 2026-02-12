# 🚂 HƯỚNG DẪN DEPLOY LÊN RAILWAY.APP

## CHUẨN BỊ ✅

Files đã được tạo sẵn:

- ✅ `Procfile` - Railway start command
- ✅ `nixpacks.toml` - Build configuration
- ✅ `.env.railway` - Environment template

---

## BƯỚC 1: TẠO GITHUB REPOSITORY

```bash
cd d:\xampp\htdocs\mountain_booking\mountain_booking_web

# Initialize Git (nếu chưa có)
git init
git add .
git commit -m "Initial commit - Ready for Railway deployment"

# Tạo repo mới trên GitHub: https://github.com/new
# Đặt tên: mountain-booking-api

# Push code lên GitHub
git remote add origin https://github.com/YOUR_USERNAME/mountain-booking-api.git
git branch -M main
git push -u origin main
```

**Thay `YOUR_USERNAME` bằng username GitHub của bạn!**

---

## BƯỚC 2: DEPLOY TRÊN RAILWAY

### 2.1. Tạo Account & Project

1. Truy cập: **https://railway.app**
2. Sign up với GitHub
3. Click **"Start a New Project"**
4. Chọn **"Deploy from GitHub repo"**
5. Authorize Railway truy cập GitHub
6. Chọn repository: **mountain-booking-api**
7. Click **"Deploy Now"**

### 2.2. Add MySQL Database

1. Trong Railway project dashboard, click **"+ New"**
2. Chọn **"Database"** → **"Add MySQL"**
3. Đợi ~30 giây để Railway provision database
4. MySQL sẽ xuất hiện bên cạnh Laravel service

### 2.3. Config Environment Variables

**Cách 1: Copy từ file `.env.railway`** (Khuyên dùng)

1. Click vào Laravel service
2. Tab **"Variables"** → Click **"RAW Editor"**
3. Copy toàn bộ nội dung file `.env.railway` paste vào
4. Thay các biến MySQL:

```env
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

**Cách 2: Add từng biến thủ công**

Click **"+ New Variable"** và thêm từng cặp key-value từ file `.env.railway`

### 2.4. Generate Public Domain

1. Tab **"Settings"** của Laravel service
2. Scroll xuống **"Networking"**
3. Click **"Generate Domain"**
4. Copy URL (VD: `https://mountain-booking-api-production.up.railway.app`)

### 2.5. Update APP_URL

1. Quay lại tab **"Variables"**
2. Tìm biến `APP_URL`
3. Thay bằng domain vừa generate (VD: `https://mountain-booking-api-production.up.railway.app`)
4. Save

---

## BƯỚC 3: RUN DATABASE MIGRATIONS

### Option A: Dùng Railway CLI (Recommended)

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Link project
cd d:\xampp\htdocs\mountain_booking\mountain_booking_web
railway link

# Chọn project "mountain-booking-api" từ list

# Run migrations
railway run php artisan migrate --force

# Seed data mẫu
railway run php artisan db:seed --force

# Kiểm tra database
railway run php artisan db:show
```

### Option B: Import Database trực tiếp

1. **Export local database:**

```bash
cd d:\xampp\htdocs\mountain_booking
mysqldump -u root mountain_booking > mountain_booking_export.sql
```

2. **Get Railway MySQL credentials:**

- Click vào MySQL service trong Railway
- Tab **"Connect"**
- Copy từng thông tin: Host, Port, User, Password, Database

3. **Import vào Railway:**

```bash
mysql -h containers-us-west-xxx.railway.app -P 6543 -u root -p railway < mountain_booking_export.sql
```

Nhập password khi được hỏi.

---

## BƯỚC 4: TEST API

### Test cơ bản:

```bash
# Thay YOUR_DOMAIN bằng domain Railway của bạn
curl https://YOUR_DOMAIN/api/tours

# Nên trả về JSON list tours
```

### Test authentication:

```bash
curl -X POST https://YOUR_DOMAIN/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password123"}'
```

### Test trong browser:

Mở: `https://YOUR_DOMAIN/api/tours`

Nên thấy JSON response với danh sách tours.

---

## BƯỚC 5: UPDATE MOBILE APP

### 5.1. Update Environment Files

**File: `mountain_booking_app/src/environments/environment.ts`**

```typescript
export const environment = {
    production: false,
    apiUrl: "https://YOUR_RAILWAY_DOMAIN/api",
};
```

**File: `mountain_booking_app/src/environments/environment.prod.ts`**

```typescript
export const environment = {
    production: true,
    apiUrl: "https://YOUR_RAILWAY_DOMAIN/api",
};
```

### 5.2. Rebuild Mobile App

```bash
cd d:\xampp\htdocs\mountain_booking\mountain_booking_app

# Build production
ionic build --prod

# Sync với Capacitor
npx cap sync

# Build APK
npm run build-apk
```

### 5.3. Test App

1. Install APK vào điện thoại
2. Test login
3. Test browse tours
4. Test booking

---

## 🔧 TROUBLESHOOTING

### Lỗi "500 Internal Server Error"

**Check logs:**

```bash
railway logs
```

**Nguyên nhân thường gặp:**

1. **APP_KEY chưa đúng:**

```bash
# Generate key mới
php artisan key:generate --show

# Copy output vào Railway Variables
```

2. **Database chưa migrate:**

```bash
railway run php artisan migrate --force
```

3. **Cache cũ:**

```bash
railway run php artisan config:clear
railway run php artisan cache:clear
railway run php artisan route:clear
```

### Lỗi "CORS Policy"

Check CORS config trong Railway Variables:

```env
SANCTUM_STATEFUL_DOMAINS=*
```

Hoặc restart service:

- Railway Dashboard → Service → Settings → **Restart**

### Lỗi "Database connection failed"

Check MySQL variables:

```env
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

Đảm bảo MySQL service đang chạy (màu xanh trong dashboard).

### Build failed

Check `nixpacks.toml` syntax và Procfile đúng format.

Xem build logs trong Railway dashboard.

---

## 📊 MONITOR & MAINTAIN

### View Logs

```bash
railway logs --follow
```

### Redeploy

```bash
git add .
git commit -m "Update something"
git push origin main
# Railway auto deploy
```

### Database Backup

```bash
railway run php artisan db:backup
```

### Scale Up (nếu cần)

- Railway Settings → Resources → Adjust RAM/CPU

---

## 💰 PRICING

- **Free Tier**: $5 credit/month (~500 hours)
- **Hobby Plan**: $5/month (unlimited projects)
- **Pro Plan**: $20/month (more resources)

---

## ✅ CHECKLIST HOÀN THÀNH

- [ ] Push code lên GitHub
- [ ] Tạo Railway project
- [ ] Deploy từ GitHub repo
- [ ] Add MySQL database
- [ ] Config environment variables
- [ ] Generate domain
- [ ] Update APP_URL
- [ ] Run migrations & seeders
- [ ] Test API endpoints
- [ ] Update mobile app environments
- [ ] Build & test APK
- [ ] Test toàn bộ tính năng

---

## 🎯 PRODUCTION CHECKLIST

Trước khi cho user thật dùng:

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Setup custom domain (optional)
- [ ] Enable HTTPS (Railway tự động)
- [ ] Setup monitoring/alerting
- [ ] Regular database backups
- [ ] Test performance

---

## 📞 SUPPORT

Nếu gặp vấn đề:

1. Check Railway Discord: https://discord.gg/railway
2. Railway Docs: https://docs.railway.app
3. Laravel Docs: https://laravel.com/docs

---

**Railway URL của bạn sẽ là:**
`https://mountain-booking-api-production-xxxx.up.railway.app`

Good luck! 🚀
