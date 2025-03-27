<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../Dashboard/includes/config.php';

try {
    // Récupérer le nom de la table depuis les paramètres (par défaut: test)
    $table = isset($_GET['table']) ? $_GET['table'] : 'test';
    
    // Valider que la table existe pour éviter les injections SQL
    $allowedTables = ['test', 'tasks']; // Ajoutez toutes vos tables autorisées
    if (!in_array($table, $allowedTables)) {
        throw new Exception('Table non autorisée');
    }

    $query = "SELECT * FROM $table ORDER BY created_at DESC";
    $stmt = $pdo->query($query);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tasks);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}