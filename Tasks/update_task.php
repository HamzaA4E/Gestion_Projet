<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../Dashboard/includes/config.php';
require_once __DIR__ . '/../../Dashboard/includes/functions.php';

try {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non authentifié');
    }

    $data = json_decode(file_get_contents('php://input'), true);

    // Validation des données
    $required = ['id', 'title', 'status'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Champ $field manquant");
        }
    }

    // Mise à jour dans la base
    $stmt = $pdo->prepare("UPDATE tasks SET 
        title = :title,
        description = :description,
        status = :status,
        priority = :priority,
        due_date = CONCAT(:deadlineDate, ' ', :deadlineTime)
        WHERE id = :id");

    $success = $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':status' => $data['status'],
        ':priority' => $data['priority'],
        ':deadlineDate' => $data['deadlineDate'],
        ':deadlineTime' => $data['deadlineTime'] ?: '23:59',
        ':id' => $data['id']
    ]);

    echo json_encode(['success' => $success]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>