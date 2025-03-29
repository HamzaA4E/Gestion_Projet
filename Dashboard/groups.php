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

// Récupération des groupes de l'utilisateur
$user_groups = getUserGroups($user_id);

// Récupération des invitations en attente
$pending_invitations = getPendingInvitations($user_id);

// Traitement de la création d'un nouveau groupe
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Création d'un nouveau groupe
        if ($_POST['action'] === 'create_group') {
            $nom = trim($_POST['nom']);
            $description = trim($_POST['description']);
            
            if (empty($nom)) {
                $error_message = "Le nom du groupe est obligatoire.";
            } else {
                $group_id = createGroup($nom, $description, $user_id);
                if ($group_id) {
                    $success_message = "Le groupe a été créé avec succès.";
                    // Recharger les groupes
                    $user_groups = getUserGroups($user_id);
                } else {
                    $error_message = "Une erreur est survenue lors de la création du groupe.";
                }
            }
        }
        
        // Réponse à une invitation
        elseif ($_POST['action'] === 'respond_invitation') {
            $invitation_id = $_POST['invitation_id'];
            $response = $_POST['response'];
            
            if (respondToInvitation($invitation_id, $user_id, $response)) {
                $success_message = $response === 'acceptee' ? "Vous avez rejoint le groupe." : "Vous avez refusé l'invitation.";
                // Recharger les groupes et les invitations
                $user_groups = getUserGroups($user_id);
                $pending_invitations = getPendingInvitations($user_id);
            } else {
                $error_message = "Une erreur est survenue lors du traitement de l'invitation.";
            }
        }
        
        // Invitation d'un membre
        elseif ($_POST['action'] === 'invite_member') {
            $group_id = $_POST['group_id'];
            $member_id = trim($_POST['member_id']);
            
            if (empty($member_id)) {
                $error_message = "L'ID du membre est obligatoire.";
            } elseif (!isGroupAdmin($user_id, $group_id)) {
                $error_message = "Vous n'avez pas les droits pour inviter des membres à ce groupe.";
            } else {
                $invited_user = getUserByPublicId($member_id);
                
                if (!$invited_user) {
                    $error_message = "Aucun utilisateur trouvé avec cet ID.";
                } elseif ($invited_user['id'] == $user_id) {
                    $error_message = "Vous ne pouvez pas vous inviter vous-même.";
                } elseif (isGroupMember($invited_user['id'], $group_id)) {
                    $error_message = "Cet utilisateur est déjà membre du groupe.";
                } else {
                    if (sendGroupInvitation($group_id, $user_id, $invited_user['id'])) {
                        $success_message = "L'invitation a été envoyée avec succès.";
                    } else {
                        $error_message = "Une erreur est survenue lors de l'envoi de l'invitation.";
                    }
                }
            }
        }
    }
}

// Récupération des membres pour chaque groupe où l'utilisateur est admin
$groups_members = [];
foreach ($user_groups as $group) {
    if ($group['role'] === 'admin') {
        $groups_members[$group['id']] = getGroupMembers($group['id']);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Groupes - Gestion de Projets</title>
    <link rel="stylesheet" href="Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="Boostarp/css/all.min.css" />
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/groups.css">
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
        <h3 class="text-dark dashboard-title">Gestion des Groupes</h3>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Invitations en attente -->
            <?php if (!empty($pending_invitations)): ?>
            <div class="col-md-12">
                <div class="card mb-4">
                    <h5>Invitations en attente</h5>
                    <div class="invitations-list">
                        <?php foreach ($pending_invitations as $invitation): ?>
                            <div class="invitation-item">
                                <div class="invitation-info">
                                    <strong><?php echo htmlspecialchars($invitation['group_name']); ?></strong>
                                    <span>Invitation de <?php echo htmlspecialchars($invitation['sender_prenom'] . ' ' . $invitation['sender_nom']); ?></span>
                                </div>
                                <div class="invitation-actions">
                                    <form action="groups.php" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="respond_invitation">
                                        <input type="hidden" name="invitation_id" value="<?php echo $invitation['id']; ?>">
                                        <input type="hidden" name="response" value="acceptee">
                                        <button type="submit" class="btn btn-sm btn-success">Accepter</button>
                                    </form>
                                    <form action="groups.php" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="respond_invitation">
                                        <input type="hidden" name="invitation_id" value="<?php echo $invitation['id']; ?>">
                                        <input type="hidden" name="response" value="refusee">
                                        <button type="submit" class="btn btn-sm btn-danger">Refuser</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Liste des groupes -->
            <div class="col-md-12">
                <div class="card">
                    <h5>Mes Groupes</h5>
                    <div class="groups-list">
                        <?php if (!empty($user_groups)): ?>
                            <?php foreach ($user_groups as $group): ?>
                                <div class="group-item <?php echo $group['role'] === 'admin' ? 'admin' : 'member'; ?>" data-group-id="<?php echo $group['id']; ?>">
                                    <div class="group-header">
                                        <div class="group-info">
                                            <span class="group-role-badge <?php echo $group['role']; ?>"><?php echo $group['role'] === 'admin' ? 'Admin' : 'Membre'; ?></span>
                                            <h6><?php echo htmlspecialchars($group['nom']); ?></h6>
                                        </div>
                                        <div class="group-actions">
                                            <button class="btn btn-sm btn-outline-primary toggle-group-details">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="group-details" style="display: none;">
                                        <?php if ($group['role'] === 'admin'): ?>
                                            <div class="group-members">
                                                <h6>Membres (<?php echo count($groups_members[$group['id']]); ?>)</h6>
                                                <ul class="members-list">
                                                    <?php foreach ($groups_members[$group['id']] as $member): ?>
                                                        <li>
                                                            <div class="member-info">
                                                                <img src="<?php echo !empty($member['profile_image']) ? htmlspecialchars($member['profile_image']) : 'img/default-avatar.jpg'; ?>" alt="Photo de profil" class="member-avatar">
                                                                <span><?php echo htmlspecialchars($member['prenom'] . ' ' . $member['nom']); ?></span>
                                                                <span class="member-role"><?php echo $member['role'] === 'admin' ? '(Admin)' : ''; ?></span>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <div class="invite-form">
                                                    <form action="groups.php" method="post">
                                                        <input type="hidden" name="action" value="invite_member">
                                                        <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" name="member_id" placeholder="ID de l'utilisateur">
                                                            <div class="input-group-append">
                                                                <button type="submit" class="btn btn-primary">Inviter</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="group-actions mt-3">
                                            <a href="group_chat.php?group_id=<?php echo $group['id']; ?>" class="btn btn-info">Accéder à la discussion</a>
                                            <a href="dashboard.php?group_id=<?php echo $group['id']; ?>" class="btn btn-secondary">Voir le dashboard</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Vous n'êtes membre d'aucun groupe.</p>
                        <?php endif; ?>
                        
                        <!-- Bouton pour créer un nouveau groupe -->
                        <div class="group-item new-group">
                            <button class="btn btn-outline-primary btn-block" data-toggle="modal" data-target="#createGroupModal">
                                <i class="fas fa-plus"></i> Créer un nouveau groupe
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour créer un nouveau groupe -->
<div class="modal fade" id="createGroupModal" tabindex="-1" role="dialog" aria-labelledby="createGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createGroupModalLabel">Créer un nouveau groupe</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="groups.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_group">
                    <div class="form-group">
                        <label for="nom">Nom du groupe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="js/script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'affichage des détails des groupes
    const toggleButtons = document.querySelectorAll('.toggle-group-details');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const groupItem = this.closest('.group-item');
            const groupDetails = groupItem.querySelector('.group-details');
            const icon = this.querySelector('i');
            
            if (groupDetails.style.display === 'none') {
                groupDetails.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                groupDetails.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    });
});
</script>
</body>
</html>
