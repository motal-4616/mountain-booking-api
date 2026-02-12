@echo off
chcp 65001 >nul
echo ============================================
echo    SETUP BACKEND - LẦN ĐẦU TIÊN
echo    MOUNTAIN BOOKING API
echo ============================================
echo.
echo Script này sẽ thực hiện:
echo [1] Cài đặt dependencies (composer install)
echo [2] Copy .env file (nếu chưa có)
echo [3] Generate application key
echo [4] Chạy migrations (tạo tables)
echo [5] Chạy seeders (dữ liệu mẫu)
echo [6] Tạo symbolic link storage
echo.
echo ⚠️  LƯU Ý:
echo     - Cần có Composer đã cài đặt
echo     - Cần XAMPP MySQL đang chạy
echo     - Đã tạo database: mountain_booking_db
echo.
pause
echo.

cd /d "%~dp0"

echo ============================================
echo [1/6] Cài đặt PHP dependencies...
echo ============================================
echo.

if not exist "vendor\" (
    echo Installing Composer packages...
    call composer install
    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo ❌ Composer install failed!
        echo Vui lòng kiểm tra Composer đã cài đặt chưa.
        pause
        exit /b 1
    )
    echo ✅ Composer install successful!
) else (
    echo ✅ Dependencies đã có sẵn (vendor folder exists)
)
echo.

echo ============================================
echo [2/6] Cấu hình .env file...
echo ============================================
echo.

if not exist ".env" (
    if exist ".env.example" (
        echo Copying .env.example to .env...
        copy .env.example .env
        echo ✅ .env file created!
        echo.
        echo ⚠️  VUI LÒNG CẬP NHẬT .env:
        echo     DB_DATABASE=mountain_booking_db
        echo     DB_USERNAME=root
        echo     DB_PASSWORD= (để trống nếu dùng XAMPP)
        echo.
    ) else (
        echo ❌ .env.example not found!
        pause
        exit /b 1
    )
) else (
    echo ✅ .env file đã tồn tại
)
echo.

echo ============================================
echo [3/6] Generate Application Key...
echo ============================================
echo.

php artisan key:generate
if %ERRORLEVEL% EQU 0 (
    echo ✅ Application key generated!
) else (
    echo ❌ Key generation failed!
)
echo.

echo ============================================
echo [4/6] Chạy Database Migrations...
echo ============================================
echo.
echo ⚠️  Đảm bảo đã tạo database: mountain_booking_db
echo     MySQL/MariaDB đang chạy trong XAMPP
echo.
pause
echo.

php artisan migrate --force
if %ERRORLEVEL% EQU 0 (
    echo ✅ Migrations completed!
) else (
    echo ❌ Migration failed!
    echo.
    echo Vui lòng kiểm tra:
    echo  1. XAMPP MySQL đang chạy
    echo  2. Database 'mountain_booking_db' đã được tạo
    echo  3. Thông tin trong .env đúng
    echo.
    pause
    exit /b 1
)
echo.

echo ============================================
echo [5/6] Chạy Database Seeders...
echo ============================================
echo.

echo Seeding tours data...
php artisan db:seed --class=TourSeeder
if %ERRORLEVEL% EQU 0 (
    echo ✅ Tours seeded successfully! (8 tours)
) else (
    echo ⚠️  Tour seeder warning (có thể đã có data)
)
echo.

echo Refreshing autoload...
composer dump-autoload
echo.

echo ============================================
echo [6/6] Tạo Storage Symbolic Link...
echo ============================================
echo.

php artisan storage:link
if %ERRORLEVEL% EQU 0 (
    echo ✅ Storage link created!
) else (
    echo ⚠️  Storage link warning (có thể đã tồn tại)
)
echo.

echo ============================================
echo    SETUP HOÀN THÀNH! ✅
echo ============================================
echo.
echo 🎉 Backend đã được cấu hình xong!
echo.
echo 📋 Các bước tiếp theo:
echo    1. Kiểm tra file .env (database config)
echo    2. Chạy START-BACKEND.bat để khởi động server
echo    3. Chạy CHECK-BACKEND.bat để kiểm tra
echo    4. Test API: http://localhost:8000/api/tours
echo.
echo 📊 Database đã có:
echo    - Tables: users, tours, bookings, etc.
echo    - 8 tours mẫu (Fansipan, Tà Xùa, etc.)
echo    - Sanctum authentication tables
echo.
echo 🚀 Khởi động backend:
echo    START-BACKEND.bat
echo.
pause
