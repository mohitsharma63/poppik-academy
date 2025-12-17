# Hostinger Database Setup Guide

## Your Hostinger Database Credentials

Based on your Hostinger panel, use these settings:

```
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u816041250_poppik_academy
DB_USER=u816041250_root
DB_PASS=your_mysql_password_here
DB_CHARSET=utf8mb4
```

## How to Get Your MySQL Password

### Option 1: Check Hostinger Panel
1. Go to Hostinger hPanel
2. Navigate to: **Databases** → **MySQL Databases**
3. Find your database user: `u816041250_root`
4. Click on the user or use "Change Password" to set/reset password
5. Copy the password

### Option 2: Reset Password in Hostinger
1. In Hostinger panel, go to MySQL Databases
2. Find user `u816041250_root`
3. Click "Change Password" or "Reset Password"
4. Set a new password and save it
5. Update your `.env` file with the new password

### Option 3: Use phpMyAdmin
1. Click "Enter phpMyAdmin" button in Hostinger panel
2. Try logging in with your Hostinger account credentials
3. The password might be the same as your Hostinger account password

## Important Notes for Hostinger

- **DB_HOST**: Always use `localhost` (not 127.0.0.1)
- **DB_NAME**: Must include the prefix `u816041250_`
- **DB_USER**: Must include the prefix `u816041250_`
- **Password**: Case-sensitive, make sure there are no extra spaces

## Testing Connection

After updating `.env`, visit:
```
http://127.0.0.1:8000/setup-db.php
```

This will test your connection and show any errors.

