<?php
/**
 * Database Setup Helper
 * This script helps you test and configure your database connection
 */

require_once 'config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - Poppik Academy</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; }
        .info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Setup & Test</h1>
        
        <?php
        // Test connection
        try {
            $testQuery = $pdo->query("SELECT VERSION() as version");
            $version = $testQuery->fetch();
            
            echo '<div class="success">';
            echo '<strong>✅ Database Connection Successful!</strong><br>';
            echo 'MySQL Version: ' . htmlspecialchars($version['version']);
            echo '</div>';
            
            // Check if tables exist
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<div class="info">';
            echo '<h3>Database Status</h3>';
            echo '<p><strong>Database:</strong> ' . htmlspecialchars(getenv('DB_NAME') ?: 'poppik_academy') . '</p>';
            echo '<p><strong>Tables Found:</strong> ' . count($tables) . '</p>';
            
            if (count($tables) > 0) {
                echo '<table>';
                echo '<tr><th>Table Name</th><th>Rows</th></tr>';
                foreach ($tables as $table) {
                    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                    echo '<tr><td>' . htmlspecialchars($table) . '</td><td>' . $count . '</td></tr>';
                }
                echo '</table>';
            } else {
                echo '<p><em>No tables found. The tables will be created automatically on first access.</em></p>';
            }
            echo '</div>';
            
            // Check admins table
            if (in_array('admins', $tables)) {
                $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
                echo '<div class="info">';
                echo '<h3>Admin Accounts</h3>';
                echo '<p>Total admins: <strong>' . $adminCount . '</strong></p>';
                if ($adminCount == 0) {
                    echo '<p><em>No admin accounts found. Run <code>create_admin.php</code> to create one.</em></p>';
                }
                echo '</div>';
            }
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<strong>❌ Database Connection Failed</strong><br>';
            echo 'Error: ' . htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
        
        <div class="info">
            <h3>Current Configuration</h3>
            <table>
                <tr><th>Setting</th><th>Value</th></tr>
                <tr><td>DB_HOST</td><td><?= htmlspecialchars(getenv('DB_HOST') ?: '127.0.0.1') ?></td></tr>
                <tr><td>DB_PORT</td><td><?= htmlspecialchars(getenv('DB_PORT') ?: '3306') ?></td></tr>
                <tr><td>DB_NAME</td><td><?= htmlspecialchars(getenv('DB_NAME') ?: 'poppik_academy') ?></td></tr>
                <tr><td>DB_USER</td><td><?= htmlspecialchars(getenv('DB_USER') ?: 'root') ?></td></tr>
                <tr><td>DB_PASS</td><td><?= getenv('DB_PASS') ? '***' : '(empty)' ?></td></tr>
            </table>
            <p><em>To change these settings, edit <code>php-admin/.env</code> file.</em></p>
        </div>
        
        <a href="index.php" class="btn">Go to Dashboard</a>
        <a href="create_admin.php" class="btn" style="background: #28a745;">Create Admin Account</a>
    </div>
</body>
</html>

