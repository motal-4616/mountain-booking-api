@echo off
echo ========================================
echo   PUSH CODE LEN GITHUB
echo ========================================
echo.

echo Step 1: Initialize Git Repository
git init

echo.
echo Step 2: Add all files
git add .

echo.
echo Step 3: Commit
git commit -m "Initial commit - Ready for Railway deployment"

echo.
echo ========================================
echo   INSTRUCTIONS:
echo ========================================
echo.
echo 1. Tao GitHub repository moi:
echo    - Truy cap: https://github.com/new
echo    - Ten repo: mountain-booking-api
echo    - Public hoac Private
echo    - KHONG chon "Add README"
echo.
echo 2. Copy command tu GitHub va paste vao day:
echo    VD: git remote add origin https://github.com/YOUR_USERNAME/mountain-booking-api.git
echo.
set /p REMOTE="Paste GitHub remote URL (hoac Enter de bo qua): "

if not "%REMOTE%"=="" (
    echo.
    echo Adding remote...
    git remote add origin %REMOTE%
    
    echo.
    echo Pushing to GitHub...
    git branch -M main
    git push -u origin main
    
    echo.
    echo ========================================
    echo   SUCCESS! Code da duoc push len GitHub
    echo ========================================
    echo.
    echo Buoc tiep theo:
    echo 1. Truy cap https://railway.app
    echo 2. Doc huong dan trong file: RAILWAY_DEPLOYMENT_GUIDE.md
    echo.
) else (
    echo.
    echo Setup remote manually:
    echo   git remote add origin YOUR_GITHUB_URL
    echo   git branch -M main
    echo   git push -u origin main
    echo.
)

pause
