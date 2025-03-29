<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../Dashboard/includes/config.php';
require_once __DIR__ . '/../../Dashboard/includes/functions.php';

try {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non authentifié');
    }

    $project_id = (int)($_GET['project_id'] ?? 0);
    if ($project_id <= 0) {
        throw new Exception('ID de projet invalide');
    }

    // Vérifier que l'utilisateur a accès à ce projet
    if (!hasAccess($project_id, 'project', $_SESSION['user_id'])) {
        throw new Exception('Accès refusé à ce projet');
    }

    // Récupérer les tâches
    $stmt = $pdo->prepare("SELECT t.*, 
                          u.prenom AS assigned_firstname, 
                          u.nom AS assigned_lastname
                          FROM tasks t
                          LEFT JOIN users u ON t.assigned_to = u.id
                          WHERE t.project_id = ?");
    $stmt->execute([$project_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'tasks' => $tasks
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>