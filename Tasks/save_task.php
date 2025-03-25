<?php
// save_task.php
require __DIR__ . '/db.php';

header('Content-Type: application/json'); // Indique que la réponse est au format JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['title'], $_POST['description'], $_POST['status'], $_POST['deadline'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $deadline = $_POST['deadline'];

        $sql = "INSERT INTO tasks (title, description, status, deadline) VALUES (:title, :description, :status, :deadline)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':status' => $status,
            ':deadline' => $deadline
        ]);

        echo json_encode(['success' => true, 'message' => 'Tâche créée avec succès']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Tous les champs du formulaire ne sont pas remplis.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode HTTP non autorisée.']);
    exit;
}
?>
