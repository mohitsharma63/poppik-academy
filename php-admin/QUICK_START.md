# 🚀 Quick Start Guide - Port 8000

## Step 1: Start Server

**Windows:**
```bash
cd php-admin
run.bat
```

**Or manually:**
```bash
cd php-admin
php -S 127.0.0.1:8000 -t . router.php
```

## Step 2: Fix Database Connection

1. Open browser: `http://127.0.0.1:8000/fix-env.php`
2. Fill in your Hostinger credentials:
   - **DB_HOST**: `localhost`
   - **DB_NAME**: `u816041250_poppik_academy`
   - **DB_USER**: `u816041250_root`
   - **DB_PASS**: Get from Hostinger panel
3. Click "Save .env File"

## Step 3: Test Connection

Visit: `http://127.0.0.1:8000/setup-db.php`

This will test your database connection.

## Step 4: Login

Visit: `http://127.0.0.1:8000/login.php`

**Default credentials:**
- Email: `admin@poppik.com`
- Password: `admin123`

(If no admin exists, run `create_admin.php` first)

## Step 5: Access Dashboard

After login, you'll be redirected to:
`http://127.0.0.1:8000/index.php`

## All Working URLs (Port 8000)

- Dashboard: `http://127.0.0.1:8000/` or `http://127.0.0.1:8000/index.php`
- Login: `http://127.0.0.1:8000/login.php` or `http://127.0.0.1:8000/login`
- Fix .env: `http://127.0.0.1:8000/fix-env.php`
- Check .env: `http://127.0.0.1:8000/check-env.php`
- Test DB: `http://127.0.0.1:8000/setup-db.php`
- All admin pages work with or without .php extension

## Troubleshooting

**Database Error?**
→ Visit `fix-env.php` and update credentials

**Can't login?**
→ Run `create_admin.php` to create admin account

**Port 8000 not working?**
→ Make sure no other application is using port 8000
→ Try: `netstat -ano | findstr :8000` (Windows) to check

