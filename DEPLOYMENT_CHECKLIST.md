# ✅ RAILWAY DEPLOYMENT CHECKLIST

## PHASE 1: CHUẨN BỊ CODE

- [ ] Kiểm tra code chạy tốt local
- [ ] Test API endpoints với Postman/curl
- [ ] Database có data mẫu
- [ ] File `.env` configured đúng

## PHASE 2: PUSH LÊN GITHUB

- [ ] Tạo GitHub repository: https://github.com/new
    - Tên: `mountain-booking-api`
    - Public hoặc Private
    - ❌ KHÔNG chọn "Initialize with README"

- [ ] Chạy script push code:

    ```bash
    PUSH_TO_GITHUB.bat
    ```

- [ ] Verify code đã lên GitHub
    - Mở repo URL trong browser
    - Check files đã đầy đủ

## PHASE 3: DEPLOY RAILWAY

- [ ] Đăng ký Railway: https://railway.app
    - Sign up với GitHub

- [ ] Tạo project mới:
    - Click "Start a New Project"
    - Chọn "Deploy from GitHub repo"
    - Authorize Railway
    - Chọn repo `mountain-booking-api`

- [ ] Add MySQL Database:
    - Click "+ New" trong project
    - Chọn "Database" → "Add MySQL"
    - Đợi ~30 giây

## PHASE 4: CONFIGURATION

- [ ] Config Environment Variables:
    - Click vào Laravel service
    - Tab "Variables"
    - Click "RAW Editor"
    - Copy toàn bộ từ file `.env.railway`
    - Paste vào
    - Update MySQL variables:
        ```
        DB_HOST=${{MySQL.MYSQLHOST}}
        DB_PORT=${{MySQL.MYSQLPORT}}
        DB_DATABASE=${{MySQL.MYSQLDATABASE}}
        DB_USERNAME=${{MySQL.MYSQLUSER}}
        DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
        ```

- [ ] Generate Domain:
    - Tab "Settings"
    - Section "Networking"
    - Click "Generate Domain"
    - Copy URL: `_______________________________`

- [ ] Update APP_URL:
    - Quay lại tab "Variables"
    - Sửa `APP_URL` = domain vừa generate
    - Save

## PHASE 5: DATABASE SETUP

Chọn 1 trong 2 cách:

### Cách A: Railway CLI (Khuyên dùng)

- [ ] Install Railway CLI:

    ```bash
    npm install -g @railway/cli
    ```

- [ ] Login & Link:

    ```bash
    railway login
    railway link
    ```

- [ ] Run migrations:
    ```bash
    railway run php artisan migrate --force
    railway run php artisan db:seed --force
    ```

### Cách B: Import Database

- [ ] Export local DB:

    ```bash
    mysqldump -u root mountain_booking > db_export.sql
    ```

- [ ] Get Railway MySQL credentials từ dashboard

- [ ] Import:
    ```bash
    mysql -h [HOST] -P [PORT] -u [USER] -p [DATABASE] < db_export.sql
    ```

## PHASE 6: TESTING

- [ ] Test health endpoint:

    ```bash
    curl https://YOUR_DOMAIN/up
    ```

    ✅ Should return `200 OK`

- [ ] Test tours API:

    ```bash
    curl https://YOUR_DOMAIN/api/tours
    ```

    ✅ Should return JSON list

- [ ] Test login API:

    ```bash
    curl -X POST https://YOUR_DOMAIN/api/login \
      -H "Content-Type: application/json" \
      -d '{"email":"admin@test.com","password":"password123"}'
    ```

    ✅ Should return user + token

- [ ] Check logs in Railway dashboard
    - No errors
    - Successful requests logged

## PHASE 7: UPDATE MOBILE APP

- [ ] Chạy script update:

    ```bash
    UPDATE_MOBILE_APP.bat
    ```

    Nhập Railway URL khi được hỏi

- [ ] Hoặc manual update:
    - File: `mountain_booking_app/src/environments/environment.ts`
    - File: `mountain_booking_app/src/environments/environment.prod.ts`
    - Thay `apiUrl` bằng Railway URL + `/api`

- [ ] Test local:
    ```bash
    cd mountain_booking_app
    ionic serve
    ```

    - Test login
    - Test browse tours
    - Test booking flow

## PHASE 8: BUILD APK

- [ ] Build production:

    ```bash
    cd mountain_booking_app
    ionic build --prod
    ```

- [ ] Sync Capacitor:

    ```bash
    npx cap sync
    ```

- [ ] Build APK:

    ```bash
    npm run build-apk
    ```

    Hoặc:

    ```bash
    build-apk-full.bat
    ```

- [ ] Install APK lên điện thoại

- [ ] Test toàn bộ app:
    - [ ] Login/Register
    - [ ] Browse tours
    - [ ] View tour details
    - [ ] Create booking
    - [ ] Payment
    - [ ] Profile
    - [ ] Settings

## PHASE 9: PRODUCTION READY

- [ ] Set production environment:
    - `APP_ENV=production`
    - `APP_DEBUG=false`

- [ ] Setup monitoring:
    - Railway logs
    - Error tracking

- [ ] Database backup strategy

- [ ] Custom domain (optional):
    - Buy domain
    - Point DNS to Railway
    - Update in Railway settings

- [ ] Security checklist:
    - [ ] Strong DB password
    - [ ] API rate limiting
    - [ ] CORS configured
    - [ ] HTTPS enabled (Railway auto)

## PHASE 10: LAUNCH

- [ ] Thông báo cho users

- [ ] Monitor first week:
    - [ ] Check logs daily
    - [ ] Monitor performance
    - [ ] Fix bugs nếu có

- [ ] Collect feedback

- [ ] Plan updates

---

## 📝 QUICK REFERENCE

**Railway URL**: `_______________________________`

**API Base URL**: `_______________________________/api`

**MySQL Host**: `_______________________________`

**Last Deployed**: `_______________________________`

---

## 🚨 ROLLBACK PLAN

Nếu có vấn đề:

1. Railway Dashboard → Deployments
2. Click vào deployment trước đó
3. Click "Redeploy"

Hoặc:

```bash
git revert HEAD
git push origin main
# Railway auto deploy version cũ
```

---

## 📞 SUPPORT

- Railway Discord: https://discord.gg/railway
- Railway Docs: https://docs.railway.app
- Laravel Docs: https://laravel.com/docs

---

**Good luck! 🚀**

Print checklist này ra và tick từng bước để đảm bảo không bỏ sót!
