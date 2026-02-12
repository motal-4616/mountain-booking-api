#!/bin/bash

echo "========================================"
echo "  RAILWAY QUICK DEPLOY SCRIPT"
echo "========================================"
echo ""

# Check if Railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "❌ Railway CLI not found!"
    echo ""
    echo "Installing Railway CLI..."
    npm install -g @railway/cli
    echo "✅ Railway CLI installed!"
    echo ""
fi

# Login to Railway
echo "Step 1: Login to Railway"
railway login

# Link project
echo ""
echo "Step 2: Link to Railway project"
echo "Chọn project 'mountain-booking-api' từ list"
railway link

# Run migrations
echo ""
echo "Step 3: Run database migrations"
read -p "Run migrations now? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Running migrations..."
    railway run php artisan migrate --force
    echo "✅ Migrations completed!"
    
    echo ""
    read -p "Seed sample data? (y/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        railway run php artisan db:seed --force
        echo "✅ Data seeded!"
    fi
fi

# Show database info
echo ""
echo "Step 4: Check database"
railway run php artisan db:show

echo ""
echo "========================================"
echo "  ✅ DEPLOYMENT COMPLETE!"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Get your Railway URL from dashboard"
echo "2. Update mobile app environment files"
echo "3. Rebuild APK"
echo ""

read -p "Press any key to continue..."
