<?php
header('Content-Type: application/json');
require __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($tasks);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['title'])) {
                throw new Exception('Le titre est obligatoire');
            }

            $stmt = $pdo->prepare("INSERT INTO tasks 
                (title, description, status, deadline_date, deadline_time, created_at)
                VALUES (:title, :description, :status, :deadline_date, :deadline_time, NOW())");
            
            $stmt->execute([
                ':title' => trim($input['title']),
                ':description' => trim($input['description'] ?? ''),
                ':status' => $input['status'] ?? 'Backlog',
                ':deadline_date' => $input['deadline_date'] ?? null,
                ':deadline_time' => $input['deadline_time'] ?? null
            ]);
            
            echo json_encode([
                'success' => true,
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}