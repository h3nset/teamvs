# TeamVS Helper Scripts

## Available Scripts

### 🔧 `fix-permissions.sh`
Quickly fix file ownership and permissions without manual sudo commands.

**Usage:**
```bash
./scripts/fix-permissions.sh
```

**What it does:**
- Sets ownership to `user:www-data`
- Sets directories to `775` (rwxrwxr-x)
- Sets files to `664` (rw-rw-r--)
- Makes `artisan` executable
- Sets ACLs for automatic permission inheritance

**When to use:**
- After `git pull` when you see permission errors
- After running `composer install/update`
- After creating new files in storage/cache
- Whenever you see "Permission denied" errors

---

### 🚀 `bump-version.sh`
Automate semantic version bumping.

**Usage:**
```bash
# Bug fixes (1.0.0 → 1.0.1)
./scripts/bump-version.sh patch

# New features (1.0.1 → 1.1.0)
./scripts/bump-version.sh minor

# Breaking changes (1.1.0 → 2.0.0)
./scripts/bump-version.sh major
```

**What it does:**
- Updates `APP_VERSION` in `.env`
- Updates `APP_VERSION_DATE` to today
- Shows next steps for releasing

**Release Workflow:**
1. Run version bump script
2. Update `CHANGELOG.md`
3. Test application
4. Commit: `git commit -am "Release vX.X.X"`
5. Tag: `git tag -a vX.X.X -m "Release vX.X.X"`
6. Push: `git push origin dev && git push --tags`
7. Merge to main for production

---

## Permission Setup (One-Time)

If this is your first time, ensure proper setup:

```bash
# 1. Add yourself to www-data group
sudo usermod -a -G www-data $USER

# 2. Log out and back in (important!)
# Or run: su - $USER

# 3. Run the fix script
./scripts/fix-permissions.sh

# 4. Verify it works
touch storage/test.txt  # Should work without sudo
rm storage/test.txt
```

---

## Daily Workflow

With proper setup, you should **rarely need sudo**:

```bash
# Development (no sudo needed)
cd /var/www/php82/teamvs
git pull origin dev
/usr/local/php82/bin/php $(which composer) install
/usr/local/php82/bin/php artisan migrate
npm run build

# If you encounter permission errors
./scripts/fix-permissions.sh
```

---

## Troubleshooting

**Problem:** Git shows mode changes (755 → 644)  
**Solution:** Already configured via `git config core.fileMode false`

**Problem:** Can't write to storage after git operations  
**Solution:** Run `./scripts/fix-permissions.sh`

**Problem:** Composer creates files you can't edit  
**Solution:** You're in www-data group now, should work automatically

**Problem:** Still getting permission denied  
**Solution:** Log out and back in (group membership requires new session)
