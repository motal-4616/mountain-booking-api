@echo off
echo ========================================
echo   CAP NHAT MOBILE APP SAU KHI DEPLOY
echo ========================================
echo.

set /p RAILWAY_URL="Nhap Railway URL cua ban (VD: https://mountain-booking-api-production.up.railway.app): "

if "%RAILWAY_URL%"=="" (
    echo.
    echo ❌ Ban chua nhap URL!
    pause
    exit /b
)

echo.
echo Dang cap nhat environment files...
echo.

cd ..\mountain_booking_app\src\environments

echo export const environment = { > environment.ts
echo   production: false, >> environment.ts
echo   apiUrl: '%RAILWAY_URL%/api' >> environment.ts
echo }; >> environment.ts

echo export const environment = { > environment.prod.ts
echo   production: true, >> environment.prod.ts
echo   apiUrl: '%RAILWAY_URL%/api' >> environment.prod.ts
echo }; >> environment.prod.ts

echo ✅ Environment files updated!
echo.
echo Files updated:
echo - environment.ts
echo - environment.prod.ts
echo.
echo API URL: %RAILWAY_URL%/api
echo.
echo ========================================
echo   NEXT STEPS:
echo ========================================
echo.
echo 1. Test local:
echo    cd ..\..
echo    ionic serve
echo.
echo 2. Build production:
echo    ionic build --prod
echo.
echo 3. Sync Capacitor:
echo    npx cap sync
echo.
echo 4. Build APK:
echo    npm run build-apk
echo.

pause
