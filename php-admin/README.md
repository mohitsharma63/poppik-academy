# PHP Admin Panel

Pure PHP admin panel for Poppik Academy.

## Running the Server

### Windows:
Double-click `run.bat` or run in command prompt:
```bash
cd php-admin
run.bat
```

### Linux/Mac:
```bash
cd php-admin
chmod +x run.sh
./run.sh
```

### Manual:
Navigate to the `php-admin` directory and run:
```bash
cd php-admin
php -S 127.0.0.1:8000 -t . router.php
```

Then open your browser and go to:
```
http://127.0.0.1:8000/login.php
```

Or you can access without .php extension:
```
http://127.0.0.1:8000/login
```

## Default Login

- **Email**: admin@poppik.com
- **Password**: admin123

(You can create a new admin account using `create_admin.php`)

## Directory Structure

- `index.php` - Dashboard
- `login.php` - Admin login page
- `logout.php` - Logout handler
- `config.php` - Database configuration and helper functions
- `includes/` - Header and footer templates
- `assets/` - CSS and JavaScript files
- `uploads/` - Uploaded files (blogs, gallery, etc.)
- `api/` - API endpoints (for Angular frontend)

## Requirements

- PHP 7.4 or higher
- MySQL database
- `.env` file with database credentials (see `.env.example`)

## Environment Variables

Create a `.env` file in the `php-admin` directory. You can copy from `.env.example`:

```bash
cp .env.example .env
```

Then edit `.env` with your MySQL credentials:

```
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=poppik_academy
DB_USER=root
DB_PASS=your_password
DB_CHARSET=utf8mb4
```

**For XAMPP users:**
- Default user: `root`
- Default password: (empty/blank)

**For shared hosting:**
- Use the credentials provided by your hosting provider
- Usually format: `u[number]_username`

## Testing Database Connection

Visit `http://127.0.0.1:8000/setup-db.php` to test your database connection and see current configuration.

## Check Your .env File

Visit `http://127.0.0.1:8000/check-env.php` to verify your `.env` file configuration.

## Hostinger Users

If you're using Hostinger hosting, see `HOSTINGER_SETUP.md` for specific instructions.

**Quick Hostinger Setup:**
```
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u816041250_poppik_academy
DB_USER=u816041250_root
DB_PASS=your_mysql_password_from_hostinger_panel
DB_CHARSET=utf8mb4
```

**To get your MySQL password:**
1. Go to Hostinger hPanel
2. Navigate to: **Databases** → **MySQL Databases**
3. Find user `u816041250_root`
4. Click "Change Password" to set/reset password
5. Update `.env` file with the new password

