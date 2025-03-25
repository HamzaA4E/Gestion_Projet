<?php
header('Content-Type: application/json');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT * FROM tasks");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
            
        case 'POST':
            $stmt = $pdo->prepare("INSERT INTO tasks 
                (title, description, status, deadline_date, deadline_time) 
                VALUES (:title, :description, :status, :deadline_date, :deadline_time)");
            
            $stmt->execute([
                ':title' => $input['title'],
                ':description' => $input['description'],
                ':status' => $input['status'] ?? 'Backlog',
                ':deadline_date' => $input['deadline_date'] ?? null,
                ':deadline_time' => $input['deadline_time'] ?? '23:59'
            ]);
            
            echo json_encode(['id' => $pdo->lastInsertId()]);
            break;
            
        case 'PUT':
            // Pour les mises à jour
            break;
            
        case 'DELETE':
            // Pour les suppressions
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>