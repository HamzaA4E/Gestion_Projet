<?php
// /Gestion_Projet/Dashboard/api/api_users.php

require_once __DIR__.'/../includes/config.php';

header('Content-Type: application/json');

try {
    // Vérifier la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Méthode non autorisée', 405);
    }

    // Récupérer les utilisateurs actifs
    $stmt = $pdo->query("
        SELECT id, prenom, nom, poste, profile_image 
        FROM users 
        WHERE is_active = 1
        ORDER BY nom, prenom
    ");
    
    $users = $stmt->fetchAll();

    // Formater l'image de profil
    foreach ($users as &$user) {
        $user['profile_image'] = $user['profile_image'] 
            ? APP_URL.'/'.UPLOAD_DIR.basename($user['profile_image'])
            : APP_URL.'/img/default-avatar.jpg';
    }

    echo json_encode([
        'success' => true,
        'data' => $users
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}