<?php
/**
 * Environment File Checker
 * This script helps verify your .env file configuration
 */

$envFile = __DIR__ . '/.env';
$envExample = __DIR__ . '/.env.example';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Environment File Checker</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
        .info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .password { color: #999; font-style: italic; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Environment File Checker</h1>
        
        <?php
        // Check if .env file exists
        if (!file_exists($envFile)) {
            echo '<div class="error">';
            echo '<strong>❌ .env file not found!</strong><br>';
            echo 'File path: <code>' . htmlspecialchars($envFile) . '</code><br><br>';
            
            if (file_exists($envExample)) {
                echo '<strong>Solution:</strong> Copy <code>.env.example</code> to <code>.env</code>:<br>';
                echo '<pre>cp .env.example .env</pre>';
                echo 'Or create a new <code>.env</code> file with your database credentials.';
            } else {
                echo 'Please create a <code>.env</code> file with your database credentials.';
            }
            echo '</div>';
        } else {
            echo '<div class="success">';
            echo '<strong>✅ .env file found!</strong><br>';
            echo 'File path: <code>' . htmlspecialchars($envFile) . '</code>';
            echo '</div>';
            
            // Read and parse .env file
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $config = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }
                if (strpos($line, '=') === false) continue;
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                // Remove surrounding quotes
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || 
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                $config[$name] = $value;
            }
            
            // Display configuration
            echo '<div class="info">';
            echo '<h3>Current Configuration</h3>';
            echo '<table>';
            echo '<tr><th>Setting</th><th>Value</th><th>Status</th></tr>';
            
            $required = ['DB_DRIVER', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
            $allSet = true;
            
            foreach ($required as $key) {
                $value = $config[$key] ?? '';
                $status = '';
                
                if (empty($value) && $key !== 'DB_PASS') {
                    $status = '<span style="color: red;">⚠️ Missing</span>';
                    $allSet = false;
                } elseif ($key === 'DB_PASS') {
                    $displayValue = empty($value) ? '<span class="password">(empty - might be correct for XAMPP)</span>' : '<span class="password">***' . substr($value, -2) . '</span>';
                    $status = empty($value) ? '<span style="color: orange;">⚠️ Empty</span>' : '<span style="color: green;">✅ Set</span>';
                } else {
                    $displayValue = htmlspecialchars($value);
                    $status = '<span style="color: green;">✅ OK</span>';
                }
                
                if ($key !== 'DB_PASS') {
                    echo '<tr><td><code>' . htmlspecialchars($key) . '</code></td><td>' . $displayValue . '</td><td>' . $status . '</td></tr>';
                } else {
                    echo '<tr><td><code>' . htmlspecialchars($key) . '</code></td><td>' . $displayValue . '</td><td>' . $status . '</td></tr>';
                }
            }
            
            echo '</table>';
            echo '</div>';
            
            // Hostinger specific check
            if (isset($config['DB_USER']) && strpos($config['DB_USER'], 'u816041250_') === 0) {
                echo '<div class="info">';
                echo '<h3>🌐 Hostinger Configuration Detected</h3>';
                echo '<p>Your database user suggests you\'re using Hostinger hosting.</p>';
                echo '<ul>';
                echo '<li><strong>DB_HOST</strong> should be: <code>localhost</code></li>';
                echo '<li><strong>DB_NAME</strong> should start with: <code>u816041250_</code></li>';
                echo '<li><strong>DB_USER</strong> should start with: <code>u816041250_</code></li>';
                echo '<li><strong>DB_PASS</strong> must match the password set in Hostinger panel</li>';
                echo '</ul>';
                echo '<p><strong>To get/reset password:</strong> Go to Hostinger hPanel → Databases → MySQL Databases → Find your user → Change Password</p>';
                echo '</div>';
            }
            
            // Test connection if all required fields are set
            if ($allSet || !empty($config['DB_PASS'])) {
                echo '<div class="info">';
                echo '<h3>Test Database Connection</h3>';
                echo '<p>Click the button below to test your database connection:</p>';
                echo '<a href="setup-db.php" style="display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px;">Test Connection</a>';
                echo '</div>';
            } else {
                echo '<div class="warning">';
                echo '<strong>⚠️ Configuration Incomplete</strong><br>';
                echo 'Please fill in all required fields in your <code>.env</code> file before testing the connection.';
                echo '</div>';
            }
            
            // Show recommended .env content for Hostinger
            if (isset($config['DB_USER']) && strpos($config['DB_USER'], 'u816041250_') === 0) {
                echo '<div class="info">';
                echo '<h3>Recommended .env Content for Hostinger</h3>';
                echo '<pre>DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u816041250_poppik_academy
DB_USER=u816041250_root
DB_PASS=YOUR_PASSWORD_HERE
DB_CHARSET=utf8mb4</pre>';
                echo '<p><em>Replace <code>YOUR_PASSWORD_HERE</code> with your actual MySQL password from Hostinger panel.</em></p>';
                echo '</div>';
            }
        }
        ?>
        
        <div class="info" style="margin-top: 30px;">
            <h3>Need Help?</h3>
            <ul>
                <li>Check <code>HOSTINGER_SETUP.md</code> for Hostinger-specific instructions</li>
                <li>Visit <code>setup-db.php</code> to test your database connection</li>
                <li>Make sure your MySQL password matches what's set in Hostinger panel</li>
            </ul>
        </div>
    </div>
</body>
</html>

