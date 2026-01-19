#!/bin/bash
# Quick permission fix script for TeamVS project

PROJECT_DIR="/var/www/php82/teamvs"
cd "$PROJECT_DIR" || exit 1

echo "🔧 Fixing permissions for TeamVS..."

# Set ownership (requires sudo)
echo "  → Setting ownership to user:www-data..."
sudo chown -R user:www-data . 2>/dev/null || echo "    ⚠ Ownership change failed (need sudo)"

# Set directory permissions
echo "  → Setting directory permissions (775)..."
chmod -R 775 storage bootstrap/cache database 2>/dev/null || \
sudo chmod -R 775 storage bootstrap/cache database

# Set file permissions for writable files
echo "  → Setting file permissions for storage..."
find storage -type f -exec chmod 664 {} \; 2>/dev/null || \
sudo find storage -type f -exec chmod 664 {} \;

# Make artisan executable
echo "  → Making artisan executable..."
chmod +x artisan 2>/dev/null || sudo chmod +x artisan

# Set ACLs if available
if command -v setfacl &> /dev/null; then
    echo "  → Setting ACLs for automatic permission inheritance..."
    sudo setfacl -R -m u:www-data:rwx storage bootstrap/cache database 2>/dev/null
    sudo setfacl -R -m u:user:rwx storage bootstrap/cache database 2>/dev/null
    sudo setfacl -R -d -m u:www-data:rwx storage bootstrap/cache database 2>/dev/null
    sudo setfacl -R -d -m u:user:rwx storage bootstrap/cache database 2>/dev/null
fi

echo ""
echo "✓ Permissions fixed successfully!"
echo ""
echo "Remember:"
echo "  - You're now in the www-data group"
echo "  - Log out and back in if this is your first time"
echo "  - Most operations should work without sudo now"
