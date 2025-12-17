<?php
session_start();

// Load .env file (simple loader) if present one level up (project root)
$envFile = realpath(__DIR__ . '/.env');
if ($envFile && file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // remove surrounding quotes
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// Require MySQL-only mode. Do not fallback to SQLite.
$dbDriver = getenv('DB_DRIVER') ?: null;

try {
    if (strtolower((string)$dbDriver) !== 'mysql') {
        die("Database driver must be MySQL. Set DB_DRIVER=mysql in .env or environment.\n");
    }

    $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
    $dbPort = getenv('DB_PORT') ?: '3306';
    $dbName = getenv('DB_NAME') ?: 'poppik_academy';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS') ?: '';
    $dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";
    
    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        $errorCode = $e->getCode();
        $errorMsg = $e->getMessage();
        
        // Provide helpful error messages
        if ($errorCode == 1045) {
            die("
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Error</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .error-box { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; margin-top: 0; }
        .error-details { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
        .config-info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        ul { line-height: 1.8; }
    </style>
</head>
<body>
    <div class=\"error-box\">
        <h1>❌ Database Connection Failed</h1>
        <div class=\"error-details\">
            <strong>Error:</strong> Access denied for user '<code>{$dbUser}</code>'@'<code>{$dbHost}</code>'<br>
            <strong>Details:</strong> {$errorMsg}
        </div>
        <div class=\"config-info\">
            <h3>How to Fix:</h3>
            <ol>
                <li>Check if <code>php-admin/.env</code> file exists</li>
                <li>Verify your MySQL credentials in the <code>.env</code> file:
                    <ul>
                        <li><code>DB_HOST</code> = {$dbHost}</li>
                        <li><code>DB_PORT</code> = {$dbPort}</li>
                        <li><code>DB_NAME</code> = {$dbName}</li>
                        <li><code>DB_USER</code> = {$dbUser}</li>
                        <li><code>DB_PASS</code> = (check your password)</li>
                    </ul>
                </li>
                <li>Make sure the MySQL user exists and has proper permissions</li>
                <li>Verify the database '<code>{$dbName}</code>' exists</li>
                <li>If using XAMPP, default user is <code>root</code> with empty password</li>
            </ol>
            <p><strong>Tip:</strong> Copy <code>.env.example</code> to <code>.env</code> and update with your credentials.</p>
            <p style=\"margin-top: 20px;\">
                <strong>Quick Fix:</strong> 
                <a href=\"fix-env.php\" style=\"display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px;\">
                    🔧 Fix .env File Now
                </a>
            </p>
        </div>
    </div>
</body>
</html>");
        } elseif ($errorCode == 1049) {
            die("
<!DOCTYPE html>
<html>
<head>
    <title>Database Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .error-box { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; }
        .info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class=\"error-box\">
        <h1>❌ Database Not Found</h1>
        <div class=\"info\">
            <p>The database '<code>{$dbName}</code>' does not exist.</p>
            <p><strong>Solution:</strong> Create the database first:</p>
            <pre style=\"background: #f5f5f5; padding: 10px; border-radius: 4px;\">CREATE DATABASE {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>
        </div>
    </div>
</body>
</html>");
        } else {
            die("
<!DOCTYPE html>
<html>
<head>
    <title>Database Error</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .error-box { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; }
        .error-details { background: #ffebee; padding: 15px; border-left: 4px solid #d32f2f; margin: 20px 0; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class=\"error-box\">
        <h1>❌ MySQL Connection Failed</h1>
        <div class=\"error-details\">
            <strong>Error Code:</strong> {$errorCode}<br>
            <strong>Message:</strong> {$errorMsg}
        </div>
        <p>Please check your database configuration in <code>php-admin/.env</code> file.</p>
    </div>
</body>
</html>");
        }
    }

} catch (Exception $e) {
    die("Configuration error: " . $e->getMessage() . "\n");
}

function createTables($pdo, $driver = 'sqlite') {
    if (strtolower($driver) === 'mysql') {
        // MySQL-compatible table definitions
        $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            duration VARCHAR(100),
            category VARCHAR(100),
            status VARCHAR(50) DEFAULT 'Active',
            image TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS students (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            course_id INT,
            status VARCHAR(50) DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS certificates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            student_id INT,
            course_id INT,
            certificate_code VARCHAR(255) UNIQUE,
            issue_date DATE,
            status VARCHAR(50) DEFAULT 'Issued',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS queries (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            subject VARCHAR(255),
            message TEXT,
            status VARCHAR(50) DEFAULT 'Pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS hero_sliders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) DEFAULT NULL,
            subtitle TEXT DEFAULT NULL,
            image LONGTEXT,
            button_text VARCHAR(255) DEFAULT NULL,
            button_link VARCHAR(255) DEFAULT NULL,
            sort_order INT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS gallery (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255),
            image TEXT NOT NULL,
            category VARCHAR(100),
            sort_order INT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT,
            excerpt TEXT,
            image TEXT,
            author VARCHAR(255),
            category VARCHAR(100),
            status VARCHAR(50) DEFAULT 'Draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS videos (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            video_url TEXT,
            thumbnail TEXT,
            category VARCHAR(100),
            duration VARCHAR(50),
            status VARCHAR(50) DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS partners (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            logo LONGTEXT,
            website VARCHAR(255),
            description TEXT,
            sort_order INT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(255) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } else {
        // SQLite-compatible definitions (existing)
        $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            duration TEXT,
            category TEXT,
            status TEXT DEFAULT 'Active',
            image TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            course_id INTEGER,
            status TEXT DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS certificates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id INTEGER,
            course_id INTEGER,
            certificate_code TEXT UNIQUE,
            issue_date DATE,
            status TEXT DEFAULT 'Issued',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS queries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            subject TEXT,
            message TEXT,
            status TEXT DEFAULT 'Pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS hero_sliders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            subtitle TEXT,
            image TEXT,
            button_text TEXT,
            button_link TEXT,
            sort_order INTEGER DEFAULT 0,
            status TEXT DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS gallery (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            image TEXT NOT NULL,
            category TEXT,
            sort_order INTEGER DEFAULT 0,
            status TEXT DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT,
            excerpt TEXT,
            image TEXT,
            author TEXT,
            category TEXT,
            status TEXT DEFAULT 'Draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS videos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            video_url TEXT,
            thumbnail TEXT,
            category TEXT,
            duration TEXT,
            status TEXT DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS partners (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            logo TEXT,
            website TEXT,
            description TEXT,
            sort_order INTEGER DEFAULT 0,
            status TEXT DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT UNIQUE NOT NULL,
            setting_value TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }
}

// Always create MySQL tables when running this admin code
createTables($pdo, 'mysql');

function insertSampleData($pdo) {
    $courseCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    if ($courseCount == 0) {
        $pdo->exec("INSERT INTO courses (name, description, duration, category, status, image) VALUES
            ('Professional Makeup Artistry', 'Professional makeup and beauty training', '3 Months', 'Beauty', 'Active', 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400'),
            ('Hair Styling & Cutting', 'Professional hair styling techniques', '4 Months', 'Hair', 'Active', 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=400'),
            ('Skin Care & Facial Therapy', 'Advanced skin care treatments', '2 Months', 'Skin', 'Active', 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400'),
            ('Personal Grooming & Styling', 'Complete grooming solutions', '1 Month', 'Lifestyle', 'Active', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400'),
            ('Yoga & Mindfulness', 'Holistic wellness through yoga', '3 Months', 'Wellness', 'Active', 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=400')");
    }



    $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    if ($studentCount == 0) {
        $pdo->exec("INSERT INTO students (name, email, phone, course_id, status) VALUES
            ('mohit sharma', 'mohit637520@gmail.com', '+91-9876543210', 1, 'Active')");
    }

    $certCount = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
    if ($certCount == 0) {
        $pdo->exec("INSERT INTO certificates (student_id, course_id, certificate_code, issue_date, status) VALUES
            (1, 1, 'CERT-2024-001', '2024-01-15', 'Issued')");
    }

    $galleryCount = $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
    if ($galleryCount == 0) {
        $pdo->exec("INSERT INTO gallery (title, image, category, sort_order, status) VALUES
            ('Beauty Training Session', 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=400', 'Beauty', 1, 'Active'),
            ('Professional Hair Styling', 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=400', 'Beauty', 2, 'Active'),
            ('Makeup Workshop', 'https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?w=400', 'Beauty', 3, 'Active'),
            ('Wellness Class', 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400', 'Wellness', 4, 'Active'),
            ('Grooming Session', 'https://images.unsplash.com/photo-1519699047748-de8e457a634e?w=400', 'Lifestyle', 5, 'Active'),
            ('Academy Campus', 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=400', 'Events', 6, 'Active')");
    }

    $partnersCount = $pdo->query("SELECT COUNT(*) FROM partners")->fetchColumn();
    if ($partnersCount == 0) {
        $pdo->exec("INSERT INTO partners (name, logo, website, description, sort_order, status) VALUES
            ('Lakme Salon', 'https://via.placeholder.com/150x80?text=Lakme', 'https://www.lakmesalon.in', 'Leading beauty salon chain', 1, 'Active'),
            ('VLCC', 'https://via.placeholder.com/150x80?text=VLCC', 'https://www.vlccwellness.com', 'Wellness and beauty experts', 2, 'Active'),
            ('Naturals', 'https://via.placeholder.com/150x80?text=Naturals', 'https://www.naturals.in', 'Professional salon services', 3, 'Active'),
            ('Jawed Habib', 'https://via.placeholder.com/150x80?text=Jawed+Habib', 'https://www.jawedhabib.com', 'Celebrity hair stylist chain', 4, 'Active'),
            ('Green Trends', 'https://via.placeholder.com/150x80?text=Green+Trends', 'https://www.greentrends.in', 'Modern unisex salon', 5, 'Active'),
            ('YLG Salon', 'https://via.placeholder.com/150x80?text=YLG', 'https://www.ylgsalons.com', 'Premium salon services', 6, 'Active')");
    }

    $blogsCount = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    if ($blogsCount == 0) {
        $pdo->exec("INSERT INTO blogs (title, excerpt, content, image, author, category, status) VALUES
            ('10 Essential Makeup Tips for Beginners', 'Master the basics of makeup application with these professional tips that will transform your beauty routine...', 'Complete guide to makeup basics for beginners.', 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400', 'Poppik Team', 'Beauty', 'Published'),
            ('Building Confidence Through Personal Grooming', 'Discover how personal grooming impacts your professional presence and opens doors to new opportunities...', 'Learn the importance of personal grooming in professional life.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400', 'Poppik Team', 'Lifestyle', 'Published'),
            ('Mindfulness Practices for Daily Balance', 'Simple mindfulness techniques to incorporate into your routine for better mental and emotional wellbeing...', 'Guide to daily mindfulness practices for wellness.', 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?w=400', 'Poppik Team', 'Wellness', 'Published')");
    }
}

insertSampleData($pdo);

function getStats($pdo) {
    return [
        'courses' => $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
        'students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
        'certificates' => $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn(),
        'queries' => $pdo->query("SELECT COUNT(*) FROM queries WHERE status = 'Pending'")->fetchColumn(),
        'blogs' => $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn(),
        'partners' => $pdo->query("SELECT COUNT(*) FROM partners")->fetchColumn()
    ];
}

// Authentication helper function
function requireAuth() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
?>
