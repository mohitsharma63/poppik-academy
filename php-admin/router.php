<?php
// Router for PHP built-in server
// This handles requests without .php extension and ensures proper routing

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove leading slash
$path = ltrim($requestUri, '/');

// Handle root and empty paths
if (empty($path) || $path === '/') {
    if (file_exists(__DIR__ . '/index.php')) {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        require __DIR__ . '/index.php';
        return true;
    }
}

// Check if the path already has .php extension
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
if ($ext === 'php') {
    // It's already a PHP file, serve it directly
    $phpFile = __DIR__ . '/' . $path;
    if (file_exists($phpFile) && is_file($phpFile)) {
        $_SERVER['SCRIPT_NAME'] = '/' . $path;
        require $phpFile;
        return true;
    }
}

// Handle static files (CSS, JS, images, etc.)
$staticFile = __DIR__ . '/' . $path;
if (file_exists($staticFile) && is_file($staticFile)) {
    $staticExts = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'mp4', 'pdf'];
    if (in_array($ext, $staticExts)) {
        return false; // Let PHP serve it directly
    }
}

// Try to find the file with .php extension (for URLs without extension)
$phpFile = __DIR__ . '/' . $path . '.php';
if (file_exists($phpFile) && is_file($phpFile)) {
    $_SERVER['SCRIPT_NAME'] = '/' . $path . '.php';
    require $phpFile;
    return true;
}

// For directories, try index.php
if (is_dir($staticFile) && file_exists($staticFile . '/index.php')) {
    $_SERVER['SCRIPT_NAME'] = $requestUri . '/index.php';
    require $staticFile . '/index.php';
    return true;
}

// 404
http_response_code(404);
echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body>";
echo "<h1>404 Not Found</h1>";
echo "<p>The requested URL <code>" . htmlspecialchars($requestUri) . "</code> was not found.</p>";
echo "<p><a href='/'>Go to Dashboard</a></p>";
echo "</body></html>";
return true;

