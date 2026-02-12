# 🚀 QUICK START - DEPLOY LÊN RAILWAY

## TÓM TẮT 3 BƯỚC

### 1️⃣ PUSH LÊN GITHUB

```bash
cd mountain_booking_web
PUSH_TO_GITHUB.bat
```

→ Tạo repo "mountain-booking-api" trên GitHub trước

### 2️⃣ DEPLOY RAILWAY

- Truy cập: https://railway.app
- New Project → Deploy from GitHub
- Chọn repo → Add MySQL
- Config Variables (copy từ `.env.railway`)
- Generate Domain

### 3️⃣ UPDATE APP

```bash
UPDATE_MOBILE_APP.bat
# Nhập Railway URL
```

---

## 📚 DOCUMENTS

- 📘 **[README.md](README.md)** - Tổng quan project
- 📗 **[RAILWAY_DEPLOYMENT_GUIDE.md](RAILWAY_DEPLOYMENT_GUIDE.md)** - Hướng dẫn chi tiết
- 📋 **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Checklist đầy đủ

---

## 🔧 SCRIPTS

| File                    | Mô tả                         |
| ----------------------- | ----------------------------- |
| `PUSH_TO_GITHUB.bat`    | Push code lên GitHub          |
| `UPDATE_MOBILE_APP.bat` | Cập nhật URL trong mobile app |
| `railway-deploy.sh`     | Deploy script (Linux/Mac)     |

---

## 📁 FILES CHO RAILWAY

| File            | Mục đích             |
| --------------- | -------------------- |
| `Procfile`      | Start command        |
| `nixpacks.toml` | Build config         |
| `.env.railway`  | Environment template |

---

## ⚡ TROUBLESHOOTING NHANH

**500 Error:**

```bash
railway logs
railway run php artisan config:clear
```

**DB Error:**
Check Variables: `DB_HOST`, `DB_PASSWORD`, etc.

**CORS Error:**
Set `SANCTUM_STATEFUL_DOMAINS=*`

---

## 🎯 RAILWAY URL

Sau khi deploy, Railway sẽ cho URL kiểu:

```
https://mountain-booking-api-production-xxxx.up.railway.app
```

API endpoint:

```
https://mountain-booking-api-production-xxxx.up.railway.app/api
```

---

## ✅ SUCCESS INDICATORS

- ✅ `curl https://YOUR_URL/up` → 200 OK
- ✅ `curl https://YOUR_URL/api/tours` → JSON list
- ✅ Mobile app login thành công
- ✅ Booking được tạo

---

**Need help?** Đọc [RAILWAY_DEPLOYMENT_GUIDE.md](RAILWAY_DEPLOYMENT_GUIDE.md)
