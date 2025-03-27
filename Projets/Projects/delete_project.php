<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $project_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Verify project ownership and admin status
    $stmt = $pdo->prepare("SELECT p.creator_id, pm.is_admin 
                          FROM projects p
                          LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = ?
                          WHERE p.id = ?");
    $stmt->execute([$user_id, $project_id]);
    $project = $stmt->fetch();

    if ($project && ($project['creator_id'] == $user_id || $project['is_admin'] == 1)) {
        try {
            $pdo->beginTransaction();

            // Delete from project_members
            $stmt = $pdo->prepare("DELETE FROM project_members WHERE project_id = ?");
            $stmt->execute([$project_id]);

            // Delete the project
            $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$project_id]);

            $pdo->commit();

            // Return success response for AJAX
            echo json_encode(['status' => 'success']);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit;
        }
    } else {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized to delete this project']);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}
