<?php
// delete_task.php
require 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Supprimer la tâche
    $sql = "DELETE FROM tasks WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    // Rediriger vers la page d'accueil
    header('Location: index.php');
    exit;
}
?>