# Deployment Guide - Local vs VPS

## Environment Differences

| Component | Local (Ubuntu 20.04) | VPS (Ubuntu 22) |
|-----------|---------------------|-----------------|
| PHP | 8.2.28 | 8.2.33 |
| Symfony | 7.x | 8.x |
| Composer | - | Latest |

## Critical Files to Regenerate on Each Environment

### 1. **Composer Dependencies**
```bash
# On VPS after pull
composer install --no-dev --optimize-autoloader
```
- `composer.lock` is gitignored - dependencies will resolve based on platform
- Platform PHP locked to 8.2.28 in `composer.json` for consistency

### 2. **Laravel Caches** (Auto-regenerated, but clear if issues)
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### 3. **Frontend Assets**
```bash
npm install
npm run build  # For production
```
- Build artifacts in `/public/build` are gitignored
- Regenerate on each environment

### 4. **Storage Permissions** (VPS only)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## VPS Deployment Workflow

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm install

# 3. Build assets
npm run build

# 4. Run migrations
php artisan migrate --force

# 5. Clear and optimize caches
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart services
php artisan queue:restart
sudo systemctl restart php8.2-fpm  # or your web server
```

## Files Gitignored for Environment Safety

✅ **Ignored** (regenerated per environment):
- `composer.lock` - Different PHP/Symfony versions
- `/bootstrap/cache/*.php` - Compiled Laravel configs
- `/storage/framework/**` - Cache, sessions, views
- `/public/build/**` - Compiled frontend assets
- `package-lock.json` - Node dependency locks

✅ **Committed** (shared across environments):
- `composer.json` - Dependency definitions with platform lock
- `.env.example` - Environment template
- Source code, migrations, views

## Troubleshooting

**Composer errors:** Clear cache and regenerate autoload
```bash
composer clear-cache
composer dump-autoload
```

**Class not found:** Regenerate optimized autoloader
```bash
composer dump-autoload -o
php artisan optimize
```

**View errors:** Clear compiled views
```bash
php artisan view:clear
```

**Permissions errors:** Fix storage permissions
```bash
chmod -R 775 storage bootstrap/cache
```
