<?php
/**
 * Quick .env File Fixer
 * This script helps you create/update .env file with correct Hostinger credentials
 */

$envFile = __DIR__ . '/.env';
$envExample = __DIR__ . '/.env.example';

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['DB_HOST'] ?? 'localhost';
    $dbPort = $_POST['DB_PORT'] ?? '3306';
    $dbName = $_POST['DB_NAME'] ?? '';
    $dbUser = $_POST['DB_USER'] ?? '';
    $dbPass = $_POST['DB_PASS'] ?? '';
    
    if (empty($dbName) || empty($dbUser)) {
        $error = 'Database name and username are required!';
    } else {
        $envContent = "DB_DRIVER=mysql\n";
        $envContent .= "DB_HOST={$dbHost}\n";
        $envContent .= "DB_PORT={$dbPort}\n";
        $envContent .= "DB_NAME={$dbName}\n";
        $envContent .= "DB_USER={$dbUser}\n";
        $envContent .= "DB_PASS={$dbPass}\n";
        $envContent .= "DB_CHARSET=utf8mb4\n";
        
        if (file_put_contents($envFile, $envContent)) {
            $message = '✅ .env file created/updated successfully! Redirecting to test connection...';
            header('Refresh: 2; url=setup-db.php');
        } else {
            $error = '❌ Failed to write .env file. Please check file permissions.';
        }
    }
}

// Read existing .env if exists
$existingConfig = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || 
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        $existingConfig[$name] = $value;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix .env File - Poppik Academy</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-top: 0; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; border-radius: 4px; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; border-radius: 4px; }
        .info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0; border-radius: 4px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #667eea; }
        .btn { background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; width: 100%; }
        .btn:hover { background: #5568d3; }
        .help-text { font-size: 12px; color: #666; margin-top: 5px; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix .env File Configuration</h1>
        
        <?php if ($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="info">
            <h3>🌐 For Hostinger Users</h3>
            <p>Based on your error, use these values:</p>
            <ul>
                <li><strong>DB_HOST:</strong> <code>localhost</code></li>
                <li><strong>DB_NAME:</strong> <code>u816041250_poppik_academy</code></li>
                <li><strong>DB_USER:</strong> <code>u816041250_root</code></li>
                <li><strong>DB_PASS:</strong> Get from Hostinger panel → Databases → MySQL Databases</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="DB_HOST">DB_HOST</label>
                <input type="text" id="DB_HOST" name="DB_HOST" value="<?= htmlspecialchars($existingConfig['DB_HOST'] ?? 'localhost') ?>" required>
                <div class="help-text">For Hostinger: use <code>localhost</code></div>
            </div>
            
            <div class="form-group">
                <label for="DB_PORT">DB_PORT</label>
                <input type="text" id="DB_PORT" name="DB_PORT" value="<?= htmlspecialchars($existingConfig['DB_PORT'] ?? '3306') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="DB_NAME">DB_NAME</label>
                <input type="text" id="DB_NAME" name="DB_NAME" value="<?= htmlspecialchars($existingConfig['DB_NAME'] ?? 'u816041250_poppik_academy') ?>" required>
                <div class="help-text">For Hostinger: <code>u816041250_poppik_academy</code></div>
            </div>
            
            <div class="form-group">
                <label for="DB_USER">DB_USER</label>
                <input type="text" id="DB_USER" name="DB_USER" value="<?= htmlspecialchars($existingConfig['DB_USER'] ?? 'u816041250_root') ?>" required>
                <div class="help-text">For Hostinger: <code>u816041250_root</code></div>
            </div>
            
            <div class="form-group">
                <label for="DB_PASS">DB_PASS (Password)</label>
                <input type="password" id="DB_PASS" name="DB_PASS" value="<?= htmlspecialchars($existingConfig['DB_PASS'] ?? '') ?>" placeholder="Enter your MySQL password">
                <div class="help-text">
                    <strong>Important:</strong> Get this from Hostinger panel:<br>
                    1. Go to hPanel → Databases → MySQL Databases<br>
                    2. Find user <code>u816041250_root</code><br>
                    3. Click "Change Password" to set/reset password<br>
                    4. Copy the password and paste here
                </div>
            </div>
            
            <button type="submit" class="btn">💾 Save .env File</button>
        </form>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <p><strong>After saving:</strong></p>
            <ul>
                <li>You'll be redirected to test the connection</li>
                <li>Or visit: <a href="setup-db.php">setup-db.php</a> to test manually</li>
                <li>Then go to: <a href="login.php">login.php</a> to access admin panel</li>
            </ul>
        </div>
    </div>
</body>
</html>

