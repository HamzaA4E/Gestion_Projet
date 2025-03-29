<?php

/**
 * Fonctions utilitaires pour l'application de gestion de projets
 */

/**
 * Vérifie si un email existe déjà dans la base de données
 * 
 * @param string $email Email à vérifier
 * @return bool True si l'email existe, false sinon
 */
function emailExists($email)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);

    return $stmt->fetchColumn() > 0;
}

/**
 * Récupère un utilisateur par son email
 * 
 * @param string $email Email de l'utilisateur
 * @return array|false Données de l'utilisateur ou false si non trouvé
 */
function getUserByEmail($email)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    return $stmt->fetch();
}

/**
 * Récupère un utilisateur par son ID
 * 
 * @param int $id ID de l'utilisateur
 * @return array|false Données de l'utilisateur ou false si non trouvé
 */
function getUserById($id)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);

    return $stmt->fetch();
}

/**
 * Crée un nouvel utilisateur
 * 
 * @param string $nom Nom de l'utilisateur
 * @param string $prenom Prénom de l'utilisateur
 * @param string $email Email de l'utilisateur
 * @param string $telephone Téléphone de l'utilisateur
 * @param string $poste Poste/fonction de l'utilisateur
 * @param string $password Mot de passe hashé
 * @return bool True si l'utilisateur a été créé, false sinon
 */
function createUser($nom, $prenom, $email, $telephone, $poste, $password)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (nom, prenom, email, telephone, poste, password, date_creation) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([$nom, $prenom, $email, $telephone, $poste, $password]);
    } catch (PDOException $e) {
        // En production, il faudrait logger l'erreur
        return false;
    }
}

/**
 * Met à jour le profil d'un utilisateur
 * 
 * @param int $id ID de l'utilisateur
 * @param string $nom Nom de l'utilisateur
 * @param string $prenom Prénom de l'utilisateur
 * @param string $email Email de l'utilisateur
 * @param string $telephone Téléphone de l'utilisateur
 * @param string $poste Poste/fonction de l'utilisateur
 * @param string $profile_image Chemin de l'image de profil
 * @param string|null $password Nouveau mot de passe hashé (null si inchangé)
 * @return bool True si le profil a été mis à jour, false sinon
 */
function updateUserProfile($id, $nom, $prenom, $email, $telephone, $poste, $profile_image, $password = null)
{
    global $pdo;

    try {
        // Construction de la requête en fonction de la présence ou non d'un nouveau mot de passe
        if ($password !== null) {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET nom = ?, prenom = ?, email = ?, telephone = ?, poste = ?, profile_image = ?, password = ?, date_modification = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$nom, $prenom, $email, $telephone, $poste, $profile_image, $password, $id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET nom = ?, prenom = ?, email = ?, telephone = ?, poste = ?, profile_image = ?, date_modification = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$nom, $prenom, $email, $telephone, $poste, $profile_image, $id]);
        }
    } catch (PDOException $e) {
        // En production, il faudrait logger l'erreur
        return false;
    }
}

/**
 * Récupère les projets récents d'un utilisateur
 * 
 * @param int $user_id ID de l'utilisateur
 * @param int $limit Nombre maximum de projets à récupérer
 * @return array Liste des projets
 */
function getRecentProjects($user_id, $limit = 5)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT p.*, 
               (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = 'completed') as completed_tasks,
               (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) as total_tasks
        FROM projects p
        WHERE p.creator_id = ? OR p.id IN (
            SELECT project_id FROM project_members WHERE creator_id = ?
        )
        
        
    ");

    $stmt->execute([$user_id, $user_id]);

    return $stmt->fetchAll();
}

/**
 * Récupère les tâches à faire d'un utilisateur
 * 
 * @param int $user_id ID de l'utilisateur
 * @param int $limit Nombre maximum de tâches à récupérer
 * @return array Liste des tâches
 */
function getPendingTasks($user_id, $limit = 5)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT t.*, p.creator_id as project_name
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE (t.assigned_to = ? OR p.creator_id = ?) AND t.status != 'completed'
        
    ");

    $stmt->execute([$user_id, $user_id]);

    return $stmt->fetchAll();
}
function getProjectTasks($project_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT t.*, 
                             u.prenom AS assigned_firstname, 
                             u.nom AS assigned_lastname
                             FROM tasks t
                             LEFT JOIN users u ON t.assigned_to = u.id
                             WHERE t.project_id = ?
                             ORDER BY t.due_date ASC");
        $stmt->execute([$project_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching tasks: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les statistiques des tâches d'un utilisateur
 * 
 * @param int $user_id ID de l'utilisateur
 * @return array Statistiques des tâches
 */

function getAllProjects()
{
    global $pdo;
    $stmt = $pdo->query("SELECT id, nom FROM projects ORDER BY nom");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getTasksStats($user_id)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN t.status = 'Backlog' THEN 1 ELSE 0 END) as backlog,
            SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE t.assigned_to = ? OR p.creator_id = ?
    ");

    $stmt->execute([$user_id, $user_id]);

    return $stmt->fetch();
}

/**
 * Nettoie et sécurise une chaîne de caractères
 * 
 * @param string $data Données à nettoyer
 * @return string Données nettoyées
 */
function cleanData($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Génère un jeton CSRF
 * 
 * @return string Jeton CSRF
 */
function generateCsrfToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si un jeton CSRF est valide
 * 
 * @param string $token Jeton à vérifier
 * @return bool True si le jeton est valide, false sinon
 */
function verifyCsrfToken($token)
{
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Redirige vers une URL
 * 
 * @param string $url URL de redirection
 * @return void
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Vérifie si l'utilisateur est connecté
 * 
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a accès à une ressource
 * 
 * @param int $resource_id ID de la ressource
 * @param string $resource_type Type de ressource (project, task, etc.)
 * @param int $user_id ID de l'utilisateur
 * @return bool True si l'utilisateur a accès, false sinon
 */
function hasAccess($resource_id, $resource_type, $user_id)
{
    global $pdo;

    switch ($resource_type) {
        case 'project':
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM projects 
                WHERE id = ? AND (creator_id = ? OR id IN (
                    SELECT project_id FROM project_members WHERE user_id = ?
                ))
            ");
            $stmt->execute([$resource_id, $user_id, $user_id]);
            break;

        case 'task':
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM tasks t
                JOIN projects p ON t.project_id = p.id
                WHERE t.id = ? AND (t.assigned_to = ? OR p.creator_id = ? OR p.id IN (
                    SELECT project_id FROM project_members WHERE user_id = ?
                ))
            ");
            $stmt->execute([$resource_id, $user_id, $user_id, $user_id]);
            break;

        default:
            return false;
    }

    return $stmt->fetchColumn() > 0;
}

function getAllUsers()
{
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT id, nom, prenom FROM users ORDER BY nom, prenom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des utilisateurs: " . $e->getMessage());
        return [];
    }
}
