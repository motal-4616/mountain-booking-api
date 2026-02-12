@echo off
chcp 65001 >nul
title Mountain Booking - Database & Schedule Commands
color 0A

:MENU
cls
echo ╔══════════════════════════════════════════════════════════╗
echo ║     Mountain Booking - Database ^& Schedule Commands     ║
echo ╠══════════════════════════════════════════════════════════╣
echo ║                                                          ║
echo ║  === DATABASE ===                                        ║
echo ║  1. Chạy tất cả migration (migrate)                     ║
echo ║  2. Chạy migration mới nhất                             ║
echo ║  3. Rollback migration cuối                              ║
echo ║  4. Reset toàn bộ DB + migrate lại (fresh)              ║
echo ║  5. Reset DB + migrate + seed dữ liệu mẫu              ║
echo ║  6. Xem trạng thái migration                            ║
echo ║  7. Chạy seeder                                          ║
echo ║                                                          ║
echo ║  === SCHEDULE ===                                        ║
echo ║  8. Chạy schedule thủ công (1 lần)                      ║
echo ║  9. Chạy schedule liên tục (worker)                     ║
echo ║  10. Cập nhật booking hoàn thành (thủ công)             ║
echo ║  11. Kiểm tra lịch trình (thủ công)                     ║
echo ║  12. Kiểm tra số người tối thiểu (thủ công)            ║
echo ║                                                          ║
echo ║  === CACHE ===                                           ║
echo ║  13. Xóa tất cả cache                                   ║
echo ║  14. Xóa cache config                                    ║
echo ║  15. Xóa cache route                                     ║
echo ║                                                          ║
echo ║  0. Thoát                                                ║
echo ║                                                          ║
echo ╚══════════════════════════════════════════════════════════╝
echo.

set /p choice="Chọn lệnh (0-15): "

if "%choice%"=="1" goto MIGRATE
if "%choice%"=="2" goto MIGRATE_LATEST
if "%choice%"=="3" goto ROLLBACK
if "%choice%"=="4" goto FRESH
if "%choice%"=="5" goto FRESH_SEED
if "%choice%"=="6" goto STATUS
if "%choice%"=="7" goto SEED
if "%choice%"=="8" goto SCHEDULE_RUN
if "%choice%"=="9" goto SCHEDULE_WORK
if "%choice%"=="10" goto UPDATE_COMPLETED
if "%choice%"=="11" goto CHECK_SCHEDULES
if "%choice%"=="12" goto CHECK_MIN_PARTICIPANTS
if "%choice%"=="13" goto CLEAR_ALL
if "%choice%"=="14" goto CLEAR_CONFIG
if "%choice%"=="15" goto CLEAR_ROUTE
if "%choice%"=="0" goto EXIT

echo Lựa chọn không hợp lệ!
pause
goto MENU

:MIGRATE
echo.
echo === Chạy tất cả migration ===
cd /d "%~dp0"
php artisan migrate
echo.
pause
goto MENU

:MIGRATE_LATEST
echo.
echo === Chạy migration mới nhất ===
cd /d "%~dp0"
php artisan migrate
echo.
echo Nếu muốn tạo migration mới:
echo   php artisan make:migration ten_migration --table=ten_bang
echo.
pause
goto MENU

:ROLLBACK
echo.
echo === Rollback migration cuối ===
cd /d "%~dp0"
php artisan migrate:rollback --step=1
echo.
pause
goto MENU

:FRESH
echo.
echo === Reset toàn bộ DB + migrate lại ===
echo CẢNH BÁO: Sẽ xóa toàn bộ dữ liệu!
set /p confirm="Bạn có chắc chắn? (y/n): "
if /i "%confirm%"=="y" (
    cd /d "%~dp0"
    php artisan migrate:fresh
)
echo.
pause
goto MENU

:FRESH_SEED
echo.
echo === Reset DB + migrate + seed dữ liệu mẫu ===
echo CẢNH BÁO: Sẽ xóa toàn bộ dữ liệu và tạo lại!
set /p confirm="Bạn có chắc chắn? (y/n): "
if /i "%confirm%"=="y" (
    cd /d "%~dp0"
    php artisan migrate:fresh --seed
)
echo.
pause
goto MENU

:STATUS
echo.
echo === Trạng thái migration ===
cd /d "%~dp0"
php artisan migrate:status
echo.
pause
goto MENU

:SEED
echo.
echo === Chạy seeder ===
cd /d "%~dp0"
php artisan db:seed
echo.
pause
goto MENU

:SCHEDULE_RUN
echo.
echo === Chạy schedule 1 lần ===
echo (Chạy tất cả task đến hạn ngay bây giờ)
cd /d "%~dp0"
php artisan schedule:run
echo.
pause
goto MENU

:SCHEDULE_WORK
echo.
echo === Chạy schedule liên tục (worker) ===
echo (Nhấn Ctrl+C để dừng)
echo.
echo Các schedule đã đăng ký:
echo   - bookings:update-completed  (hàng ngày)
echo   - booking:check-schedules    (mỗi giờ)
echo.
cd /d "%~dp0"
php artisan schedule:work
goto MENU

:UPDATE_COMPLETED
echo.
echo === Cập nhật booking hoàn thành ===
echo (Đánh dấu các booking đã qua ngày tour thành 'completed')
cd /d "%~dp0"
php artisan bookings:update-completed
echo.
pause
goto MENU

:CHECK_SCHEDULES
echo.
echo === Kiểm tra lịch trình ===
cd /d "%~dp0"
php artisan booking:check-schedules
echo.
pause
goto MENU

:CHECK_MIN_PARTICIPANTS
echo.
echo === Kiểm tra số người tối thiểu ===
echo (Hủy các lịch trình chưa đủ người tại deadline đăng ký)
cd /d "%~dp0"
php artisan app:check-schedule-minimum
echo.
pause
goto MENU

:CLEAR_ALL
echo.
echo === Xóa tất cả cache ===
cd /d "%~dp0"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo Đã xóa tất cả cache!
echo.
pause
goto MENU

:CLEAR_CONFIG
echo.
echo === Xóa cache config ===
cd /d "%~dp0"
php artisan config:clear
echo.
pause
goto MENU

:CLEAR_ROUTE
echo.
echo === Xóa cache route ===
cd /d "%~dp0"
php artisan route:clear
echo.
pause
goto MENU

:EXIT
exit
