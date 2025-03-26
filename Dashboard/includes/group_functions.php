<?php
/**
 * Fonctions pour la gestion des groupes
 */

/**
 * Crée un nouveau groupe
 * 
 * @param string $nom Nom du groupe
 * @param string $description Description du groupe
 * @param int $creator_id ID de l'utilisateur créateur
 * @return int|false ID du groupe créé ou false en cas d'erreur
 */
function createGroup($nom, $description, $creator_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Création du groupe
        $stmt = $pdo->prepare("
            INSERT INTO groups (nom, description, creator_id, date_creation) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$nom, $description, $creator_id]);
        
        $group_id = $pdo->lastInsertId();
        
        // Ajout du créateur comme admin du groupe
        $stmt = $pdo->prepare("
            INSERT INTO group_members (group_id, user_id, role, date_ajout) 
            VALUES (?, ?, 'admin', NOW())
        ");
        $stmt->execute([$group_id, $creator_id]);
        
        $pdo->commit();
        return $group_id;
    } catch (PDOException $e) {
        $pdo->rollBack();
        // En production, il faudrait logger l'erreur
        return false;
    }
}

/**
 * Récupère un groupe par son ID
 * 
 * @param int $group_id ID du groupe
 * @return array|false Données du groupe ou false si non trouvé
 */
function getGroupById($group_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM groups WHERE id = ?");
    $stmt->execute([$group_id]);
    
    return $stmt->fetch();
}

/**
 * Récupère tous les groupes dont l'utilisateur est membre
 * 
 * @param int $user_id ID de l'utilisateur
 * @return array Liste des groupes
 */
function getUserGroups($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT g.*, gm.role, 
               (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as member_count
        FROM groups g
        JOIN group_members gm ON g.id = gm.group_id
        WHERE gm.user_id = ?
        ORDER BY g.date_creation DESC
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}

/**
 * Vérifie si un utilisateur est membre d'un groupe
 * 
 * @param int $user_id ID de l'utilisateur
 * @param int $group_id ID du groupe
 * @return bool True si l'utilisateur est membre, false sinon
 */
function isGroupMember($user_id, $group_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM group_members 
        WHERE user_id = ? AND group_id = ?
    ");
    $stmt->execute([$user_id, $group_id]);
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si un utilisateur est admin d'un groupe
 * 
 * @param int $user_id ID de l'utilisateur
 * @param int $group_id ID du groupe
 * @return bool True si l'utilisateur est admin, false sinon
 */
function isGroupAdmin($user_id, $group_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM group_members 
        WHERE user_id = ? AND group_id = ? AND role = 'admin'
    ");
    $stmt->execute([$user_id, $group_id]);
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Récupère les membres d'un groupe
 * 
 * @param int $group_id ID du groupe
 * @return array Liste des membres
 */
function getGroupMembers($group_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.nom, u.prenom, u.email, u.profile_image, gm.role, gm.date_ajout
        FROM group_members gm
        JOIN users u ON gm.user_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.role DESC, u.nom, u.prenom
    ");
    $stmt->execute([$group_id]);
    
    return $stmt->fetchAll();
}

/**
 * Envoie une invitation à rejoindre un groupe
 * 
 * @param int $group_id ID du groupe
 * @param int $sender_id ID de l'utilisateur qui envoie l'invitation
 * @param int $receiver_id ID de l'utilisateur qui reçoit l'invitation
 * @return bool True si l'invitation a été envoyée, false sinon
 */
function sendGroupInvitation($group_id, $sender_id, $receiver_id) {
    global $pdo;
    
    // Vérifier si l'expéditeur est admin du groupe
    if (!isGroupAdmin($sender_id, $group_id)) {
        return false;
    }
    
    // Vérifier si le destinataire est déjà membre du groupe
    if (isGroupMember($receiver_id, $group_id)) {
        return false;
    }
    
    try {
        // Vérifier si une invitation en attente existe déjà
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM group_invitations 
            WHERE group_id = ? AND receiver_id = ? AND status = 'en_attente'
        ");
        $stmt->execute([$group_id, $receiver_id]);
        
        if ($stmt->fetchColumn() > 0) {
            return false; // Une invitation en attente existe déjà
        }
        
        // Créer l'invitation
        $stmt = $pdo->prepare("
            INSERT INTO group_invitations (group_id, sender_id, receiver_id, status, date_invitation) 
            VALUES (?, ?, ?, 'en_attente', NOW())
        ");
        
        return $stmt->execute([$group_id, $sender_id, $receiver_id]);
    } catch (PDOException $e) {
        // En production, il faudrait logger l'erreur
        return false;
    }
}

/**
 * Récupère les invitations en attente pour un utilisateur
 * 
 * @param int $user_id ID de l'utilisateur
 * @return array Liste des invitations
 */
function getPendingInvitations($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT gi.*, g.nom as group_name, u.nom as sender_nom, u.prenom as sender_prenom
        FROM group_invitations gi
        JOIN groups g ON gi.group_id = g.id
        JOIN users u ON gi.sender_id = u.id
        WHERE gi.receiver_id = ? AND gi.status = 'en_attente'
        ORDER BY gi.date_invitation DESC
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}

/**
 * Répond à une invitation de groupe
 * 
 * @param int $invitation_id ID de l'invitation
 * @param int $user_id ID de l'utilisateur qui répond
 * @param string $response Réponse ('acceptee' ou 'refusee')
 * @return bool True si la réponse a été traitée, false sinon
 */
function respondToInvitation($invitation_id, $user_id, $response) {
    global $pdo;
    
    if ($response !== 'acceptee' && $response !== 'refusee') {
        return false;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Récupérer l'invitation
        $stmt = $pdo->prepare("
            SELECT * FROM group_invitations 
            WHERE id = ? AND receiver_id = ? AND status = 'en_attente'
        ");
        $stmt->execute([$invitation_id, $user_id]);
        $invitation = $stmt->fetch();
        
        if (!$invitation) {
            $pdo->rollBack();
            return false;
        }
        
        // Mettre à jour le statu de l'invitation
        $stmt = $pdo->prepare("
            UPDATE group_invitations 
            SET status = ?, date_reponse = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$response, $invitation_id]);
        
        // Si l'invitation est acceptée, ajouter l'utilisateur au groupe
        if ($response === 'acceptee') {
            $stmt = $pdo->prepare("
                INSERT INTO group_members (group_id, user_id, role, date_ajout) 
                VALUES (?, ?, 'membre', NOW())
            ");
            $stmt->execute([$invitation['group_id'], $user_id]);
        }
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        // En production, il faudrait logger l'erreur
        return false;
    }
}

/**
 * Ajoute un message dans la discussion de groupe
 * 
 * @param int $group_id ID du groupe
 * @param int $user_id ID de l'utilisateur qui envoie le message
 * @param string $message Contenu du message
 * @param array $mentions Liste des IDs des utilisateurs mentionnés
 * @return int|false ID du message créé ou false en cas d'erreur
 */
function addGroupMessage($group_id, $user_id, $message, $mentions = []) {
    global $pdo;
    
    // Vérifier si l'utilisateur est membre du groupe
    if (!isGroupMember($user_id, $group_id)) {
        return false;
    }
    
    try {
        $mentions_json = !empty($mentions) ? json_encode($mentions) : null;
        
        $stmt = $pdo->prepare("
            INSERT INTO group_messages (group_id, user_id, message, mentions, date_creation) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$group_id, $user_id, $message, $mentions_json]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        // En production, il faudrait logger l'erreur
        return false;
    }
}

/**
 * Récupère les messages d'un groupe
 * 
 * @param int $group_id ID du groupe
 * @param int $limit Nombre maximum de messages à récupérer
 * @param int $offset Offset pour la pagination
 * @return array Liste des messages
 */
function getGroupMessages($group_id, $limit = 50, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT gm.*, u.nom, u.prenom, u.profile_image
        FROM group_messages gm
        JOIN users u ON gm.user_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.date_creation DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$group_id, $limit, $offset]);
    
    return $stmt->fetchAll();
}

/**
 * Récupère les statistiques des tâches pour un groupe spécifique
 * 
 * @param int $group_id ID du groupe
 * @return array Statistiques des tâches
 */
function getGroupTasksStats($group_id) {
    global $pdo;
    
    // Récupérer tous les membres du groupe
    $members = getGroupMembers($group_id);
    $member_ids = array_column($members, 'id');
    
    if (empty($member_ids)) {
        return [
            'completed' => 0,
            'on_hold' => 0,
            'in_progress' => 0,
            'pending' => 0
        ];
    }
    
    // Convertir le tableau d'IDs en chaîne pour la requête SQL
    $member_ids_str = implode(',', $member_ids);
    
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN t.status = 'on_hold' THEN 1 ELSE 0 END) as on_hold,
            SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE t.assigned_to IN ({$member_ids_str}) OR p.user_id IN ({$member_ids_str})
    ");
    
    $stmt->execute();
    
    $result = $stmt->fetch();
    
    // Si aucune tâche n'est trouvée, retourner des valeurs par défaut
    if (!$result || ($result['completed'] === null && $result['on_hold'] === null && 
                    $result['in_progress'] === null && $result['pending'] === null)) {
        return [
            'completed' => 0,
            'on_hold' => 0,
            'in_progress' => 0,
            'pending' => 0
        ];
    }
    
    return $result;
}

/**
 * Récupère les projets récents pour un groupe spécifique
 * 
 * @param int $group_id ID du groupe
 * @param int $limit Nombre maximum de projets à récupérer
 * @return array Liste des projets
 */
function getGroupRecentProjects($group_id, $limit = 5) {
    global $pdo;
    
    // Récupérer tous les membres du groupe
    $members = getGroupMembers($group_id);
    $member_ids = array_column($members, 'id');
    
    if (empty($member_ids)) {
        return [];
    }
    
    // Convertir le tableau d'IDs en chaîne pour la requête SQL
    $member_ids_str = implode(',', $member_ids);
    
    $stmt = $pdo->prepare("
        SELECT p.*, 
               (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = 'completed') as completed_tasks,
               (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) as total_tasks
        FROM projects p
        WHERE p.user_id IN ({$member_ids_str}) OR p.id IN (
            SELECT project_id FROM project_members WHERE user_id IN ({$member_ids_str})
        )
        ORDER BY p.date_modification DESC
        LIMIT ?
    ");
    
    $stmt->execute([$limit]);
    
    return $stmt->fetchAll();
}

/**
 * Récupère les tâches à faire pour un groupe spécifique
 * 
 * @param int $group_id ID du groupe
 * @param int $limit Nombre maximum de tâches à récupérer
 * @return array Liste des tâches
 */
function getGroupPendingTasks($group_id, $limit = 5) {
    global $pdo;
    
    // Récupérer tous les membres du groupe
    $members = getGroupMembers($group_id);
    $member_ids = array_column($members, 'id');
    
    if (empty($member_ids)) {
        return [];
    }
    
    // Convertir le tableau d'IDs en chaîne pour la requête SQL
    $member_ids_str = implode(',', $member_ids);
    
    $stmt = $pdo->prepare("
        SELECT t.*, p.nom as project_name
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE (t.assigned_to IN ({$member_ids_str}) OR p.user_id IN ({$member_ids_str})) AND t.status != 'completed'
        ORDER BY t.date_echeance ASC
        LIMIT ?
    ");
    
    $stmt->execute([$limit]);
    
    return $stmt->fetchAll();
}

/**
 * Recherche un utilisateur par son ID public
 * 
 * @param string $public_id ID public de l'utilisateur
 * @return array|false Données de l'utilisateur ou false si non trouvé
 */
function getUserByPublicId($public_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$public_id]);
    
    return $stmt->fetch();
}
