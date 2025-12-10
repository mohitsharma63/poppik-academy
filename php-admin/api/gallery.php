
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT * FROM gallery WHERE status = 'Active' ORDER BY sort_order ASC");
            $gallery = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $gallery]);
            break;

        case 'POST':
                // support file upload via multipart form-data when ?upload=1
                if (isset($_GET['upload'])) {
                    // Image upload
                    if (isset($_FILES['image_file'])) {
                        $uploadsDir = __DIR__ . '/../uploads/gallery';
                        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                        $file = $_FILES['image_file'];
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $name = uniqid('gallery_') . '.' . $ext;
                        $dest = $uploadsDir . '/' . $name;
                        if (move_uploaded_file($file['tmp_name'], $dest)) {
                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'];
                            $baseUrl = $scheme . '://' . $host;
                            $url = $baseUrl . '/uploads/gallery/' . $name;
                            echo json_encode(['success' => true, 'url' => $url]);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Image upload failed']);
                        }
                        break;
                    }

                    // Video upload (support field name: video_file)
                    if (isset($_FILES['video_file'])) {
                        $uploadsDir = __DIR__ . '/../uploads/videos';
                        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                        $file = $_FILES['video_file'];
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $name = uniqid('video_') . '.' . $ext;
                        $dest = $uploadsDir . '/' . $name;
                        if (move_uploaded_file($file['tmp_name'], $dest)) {
                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'];
                            $baseUrl = $scheme . '://' . $host;
                            $url = $baseUrl . '/uploads/videos/' . $name;
                            echo json_encode(['success' => true, 'url' => $url]);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Video upload failed']);
                        }
                        break;
                    }
                }

                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $pdo->prepare("INSERT INTO gallery (title, image, category, sort_order, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['title'] ?? '',
                    $data['image'] ?? '',
                    $data['category'] ?? 'General',
                    $data['sort_order'] ?? 0,
                    $data['status'] ?? 'Active'
                ]);
                echo json_encode(['success' => true, 'message' => 'Image added successfully']);
            break;

        case 'PUT':
            $id = $_GET['id'] ?? null;
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE gallery SET title = ?, image = ?, category = ?, sort_order = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $data['title'] ?? '',
                $data['image'],
                $data['category'] ?? 'General',
                $data['sort_order'] ?? 0,
                $data['status'] ?? 'Active',
                $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Image updated successfully']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
