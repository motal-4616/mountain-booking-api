@echo off
chcp 65001 >nul
echo ============================================
echo    KIỂM TRA BACKEND - MOUNTAIN BOOKING
echo ============================================
echo.

cd /d "%~dp0"

echo [1] Kiểm tra Backend đang chạy...
echo.

netstat -ano | findstr :8000 >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✅ Backend đang chạy trên port 8000
    echo.
    netstat -ano | findstr :8000
    echo.
) else (
    echo ❌ Backend KHÔNG chạy trên port 8000
    echo.
    echo Vui lòng chạy START-BACKEND.bat để khởi động backend!
    echo.
    pause
    exit /b 1
)

echo [2] Lấy địa chỉ IP máy tính...
echo.

for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do (
    set IP=%%a
    set IP=!IP:~1!
    echo 🌐 IP Address: !IP!
)

echo.
echo [3] Kiểm tra API endpoints...
echo.

echo Testing localhost...
curl -s http://localhost:8000/api/health >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✅ http://localhost:8000/api/health - OK
) else (
    echo ❌ http://localhost:8000/api/health - FAILED
)

curl -s http://localhost:8000/api/tours >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✅ http://localhost:8000/api/tours - OK
) else (
    echo ❌ http://localhost:8000/api/tours - FAILED
)

echo.
echo ============================================
echo    KẾT QUẢ KIỂM TRA
echo ============================================
echo.
echo 📱 Truy cập từ máy tính:
echo    http://localhost:8000/api/tours
echo.
echo 📱 Truy cập từ điện thoại (cùng WiFi):
echo    http://!IP!:8000/api/tours
echo.
echo 💡 Mở link trên trong browser điện thoại để test!
echo    Nếu thấy JSON data → Backend hoạt động đúng
echo.
echo ============================================
echo.
pause
