
<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $courses = $pdo->query("SELECT id, name, description, duration, category, status, image FROM courses WHERE status = 'Active' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $courses]);
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO courses (name, description, duration, category, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['description'], $data['duration'], $data['category'], $data['status']]);
        echo json_encode(['success' => true, 'message' => 'Course added successfully']);
    }
    elseif ($method === 'PUT') {
        $id = $_GET['id'];
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE courses SET name = ?, description = ?, duration = ?, category = ?, status = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['description'], $data['duration'], $data['category'], $data['status'], $id]);
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    }
    elseif ($method === 'DELETE') {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
