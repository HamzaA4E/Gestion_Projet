<?php
// Désactivation des avertissements pour une meilleure expérience utilisateur
error_reporting(E_ERROR | E_PARSE);

// Paramètres de session sécurisés (définis avant session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en production avec HTTPS

// Démarrage de la session
session_start();

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Inclusion des fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/group_functions.php';

// Récupération des informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Vérification si un groupe est sélectionné
$selected_group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : null;
$selected_group = null;
$is_group_member = false;

if ($selected_group_id) {
    $selected_group = getGroupById($selected_group_id);
    $is_group_member = isGroupMember($user_id, $selected_group_id);
    
    // Rediriger si l'utilisateur n'est pas membre du groupe
    if (!$is_group_member || !$selected_group) {
        header('Location: group_chat.php');
        exit;
    }
}

// Récupération des groupes de l'utilisateur pour le menu déroulant
$user_groups = getUserGroups($user_id);

// Traitement de l'envoi d'un message
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selected_group_id) {
    $message = trim($_POST['message']);
    
    if (empty($message)) {
        $error_message = "Le message ne peut pas être vide.";
    } else {
        // Extraction des mentions (@utilisateur)
        $mentions = [];
        preg_match_all('/@(\w+)/', $message, $matches);
        
        if (!empty($matches[1])) {
            // Récupérer les membres du groupe pour vérifier les mentions
            $group_members = getGroupMembers($selected_group_id);
            $member_usernames = [];
            
            foreach ($group_members as $member) {
                $username = strtolower($member['prenom'] . $member['nom']);
                $member_usernames[$username] = $member['id'];
            }
            
            foreach ($matches[1] as $mention) {
                $mention = strtolower($mention);
                if (isset($member_usernames[$mention])) {
                    $mentions[] = $member_usernames[$mention];
                }
            }
        }
        
        // Ajout du message
        if (addGroupMessage($selected_group_id, $user_id, $message, $mentions)) {
            // Redirection pour éviter la soumission multiple du formulaire
            header('Location: group_chat.php?group_id=' . $selected_group_id);
            exit;
        } else {
            $error_message = "Une erreur est survenue lors de l'envoi du message.";
        }
    }
}

// Récupération des messages du groupe sélectionné
$messages = [];
if ($selected_group_id) {
    $messages = getGroupMessages($selected_group_id);
}

// Récupération des membres du groupe sélectionné pour les mentions
$group_members = [];
if ($selected_group_id) {
    $group_members = getGroupMembers($selected_group_id);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion de Groupe - Gestion de Projets</title>
    <link rel="stylesheet" href="Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="Boostarp/css/all.min.css" />
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>
<div id="er">
<div id="er00">
        <div id="er1">
        <a class="navbar-brand fs-3" href="#">AProjectO</a>
            <div id="em2">
                
                <h6 id="iduser_name"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h6>
                <a href="profile.php" id="profile-link"><img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'img/default-avatar.jpg'; ?>" alt="image de profil" class="img_prof"></a>
            </div>
        </div>
    </div>
    
    <div class="sidebar px-3">
            <ul class="ulSidebar ">
            <div class="d-flex">
    <i class="fa-solid fa-gauge"></i>
    <a href="/Gestion_Projet/Dashboard/dashboard.php" 
       class="Dashboard fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Dashboard
    </a>
</div>
<div class="d-flex">
    <i class="fa-solid fa-folder"></i>
    <a href="/Gestion_Projet/Projets/Projects/index.php" 
       class="Projects fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Projects
    </a>
</div>
<div class="d-flex" id="tasksBtn">
    <i class="fa-solid fa-square-check"></i>
    <a href="/Gestion_Projet/Tasks/index.php" 
       class="Tasks fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Tasks
    </a>
</div>
<div class="d-flex">
    <i class="fa-solid fa-comment"></i>
    <a href="/Gestion_Projet/Dashboard/group_chat.php" 
       class="Tasks fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Discussion
    </a>
</div>
<div class="d-flex">
    <i class="fa-solid fa-users-line"></i>
    <a href="/Gestion_Projet/Dashboard/groups.php" 
       class="Tasks fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Groupes
    </a>
</div>

                
<div class="d-flex">
    <i class="fa-solid fa-gears"></i>
    <a href="/Gestion_Projet/Dashboard/profile.php" 
       class="Settings fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Settings
    </a>
</div>
<div class="d-flex">
<i class="fa-solid fa-right-from-bracket"></i>
    <a href="/Gestion_Projet/Dashboard/php/logout.php" 
       class="Settings fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Déconnexion
    </a>
</div>
            </ul>
        </div>
    <div class="content">
        <div class="chat-header">
            <h3 class="text-dark dashboard-title">
                Discussion <?php echo $selected_group ? '- ' . htmlspecialchars($selected_group['nom']) : ''; ?>
            </h3>
            
            <!-- Sélecteur de groupe -->
            <div class="group-selector">
                <form action="group_chat.php" method="get">
                    <div class="input-group">
                        <select class="form-control" name="group_id" id="group_selector" onchange="this.form.submit()">
                            <option value="">Sélectionner un groupe</option>
                            <?php foreach ($user_groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>" <?php echo $selected_group_id == $group['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($group['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($selected_group): ?>
            <div class="chat-container">
                <div class="chat-messages" id="chat-messages">
                    <?php if (empty($messages)): ?>
                        <div class="no-messages">
                            <p>Aucun message dans ce groupe. Soyez le premier à écrire !</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['user_id'] == $user_id ? 'message-own' : ''; ?>">
                                <div class="message-avatar">
                                    <img src="<?php echo !empty($msg['profile_image']) ? htmlspecialchars($msg['profile_image']) : 'img/default-avatar.jpg'; ?>" alt="Avatar">
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <span class="message-author"><?php echo htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']); ?></span>
                                        <span class="message-time"><?php echo date('d/m/Y H:i', strtotime($msg['date_creation'])); ?></span>
                                    </div>
                                    <div class="message-text">
                                        <?php 
                                            // Mise en forme des mentions
                                            $formatted_message = htmlspecialchars($msg['message']);
                                            $formatted_message = preg_replace('/@(\w+)/', '<span class="mention">@$1</span>', $formatted_message);
                                            echo nl2br($formatted_message);
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="chat-input">
                    <form action="group_chat.php?group_id=<?php echo $selected_group_id; ?>" method="post">
                        <div class="input-group">
                            <textarea class="form-control" name="message" id="message-input" placeholder="Écrivez votre message..." rows="2" required></textarea>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">Envoyer</button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Liste des membres pour les mentions -->
                    <div class="mentions-list" id="mentions-list" style="display: none;">
                        <div class="mentions-header">Mentionner un membre</div>
                        <div class="mentions-content">
                            <?php foreach ($group_members as $member): ?>
                                <div class="mention-item" data-username="<?php echo htmlspecialchars($member['prenom'] . $member['nom']); ?>">
                                    <img src="<?php echo !empty($member['profile_image']) ? htmlspecialchars($member['profile_image']) : 'img/default-avatar.jpg'; ?>" alt="Avatar" class="mention-avatar">
                                    <span><?php echo htmlspecialchars($member['prenom'] . ' ' . $member['nom']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="no-group-selected">
                <div class="alert alert-info">
                    <p>Veuillez sélectionner un groupe pour accéder à la discussion.</p>
                    <p>Si vous n'êtes membre d'aucun groupe, vous pouvez <a href="groups.php">créer ou rejoindre un groupe</a>.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Faire défiler jusqu'au dernier message
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Système de mentions
    const messageInput = document.getElementById('message-input');
    const mentionsList = document.getElementById('mentions-list');
    const mentionItems = document.querySelectorAll('.mention-item');
    
    if (messageInput && mentionsList) {
        messageInput.addEventListener('input', function() {
            const cursorPosition = this.selectionStart;
            const text = this.value.substring(0, cursorPosition);
            const mentionMatch = text.match(/@(\w*)$/);
            
            if (mentionMatch) {
                const searchTerm = mentionMatch[1].toLowerCase();
                let hasMatches = false;
                
                mentionItems.forEach(item => {
                    const username = item.getAttribute('data-username').toLowerCase();
                    if (username.includes(searchTerm)) {
                        item.style.display = 'flex';
                        hasMatches = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                if (hasMatches) {
                    mentionsList.style.display = 'block';
                } else {
                    mentionsList.style.display = 'none';
                }
            } else {
                mentionsList.style.display = 'none';
            }
        });
        
        mentionItems.forEach(item => {
            item.addEventListener('click', function() {
                const username = this.getAttribute('data-username');
                const cursorPosition = messageInput.selectionStart;
                const text = messageInput.value.substring(0, cursorPosition);
                const mentionMatch = text.match(/@(\w*)$/);
                
                if (mentionMatch) {
                    const startPos = cursorPosition - mentionMatch[0].length;
                    const newText = messageInput.value.substring(0, startPos) + '@' + username + ' ' + messageInput.value.substring(cursorPosition);
                    messageInput.value = newText;
                    messageInput.focus();
                    messageInput.selectionStart = startPos + username.length + 2;
                    messageInput.selectionEnd = startPos + username.length + 2;
                }
                
                mentionsList.style.display = 'none';
            });
        });
        
        // Fermer la liste des mentions en cliquant ailleurs
        document.addEventListener('click', function(e) {
            if (!mentionsList.contains(e.target) && e.target !== messageInput) {
                mentionsList.style.display = 'none';
            }
        });
    }
});
</script>
</body>
</html>
