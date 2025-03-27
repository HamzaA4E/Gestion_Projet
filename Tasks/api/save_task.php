<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../Dashboard/includes/config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation des données
    if (empty($data['title'])) {
        throw new Exception('Le titre est obligatoire');
    }

    $table = $data['table'] ?? 'test';
    $allowedTables = ['test', 'tasks']; // Tables autorisées
    if (!in_array($table, $allowedTables)) {
        throw new Exception('Table non autorisée');
    }

    // Préparation de la requête
    $stmt = $pdo->prepare("INSERT INTO $table 
        (title, description, deadline_date, deadline_time, status, created_by, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())");

    $success = $stmt->execute([
        $data['title'],
        $data['description'] ?? '',
        $data['deadline_date'] ?? null,
        $data['deadline_time'] ?? '23:59',
        $data['status'] ?? 'backlog',
        $_SESSION['user_id'] ?? 1 // ID de l'utilisateur connecté
    ]);

    if ($success) {
        echo json_encode([
            'success' => true,
            'id' => $pdo->lastInsertId(),
            'message' => 'Tâche sauvegardée avec succès'
        ]);
    } else {
        throw new Exception('Erreur lors de la sauvegarde');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}