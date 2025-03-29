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
$is_personal_view = true;

if ($selected_group_id) {
    $selected_group = getGroupById($selected_group_id);
    $is_group_member = isGroupMember($user_id, $selected_group_id);

    // Rediriger si l'utilisateur n'est pas membre du groupe
    if (!$is_group_member || !$selected_group) {
        header('Location: dashboard.php');
        exit;
    }

    $is_personal_view = false;
}

// Récupération des groupes de l'utilisateur pour le menu déroulant
$user_groups = getUserGroups($user_id);

// Récupération des statistiques pour les graphiques
if ($is_personal_view) {
    // Statistiques personnelles
    $tasksStats = getTasksStats($user_id);
    $recentProjects = getRecentProjects($user_id);
    $pendingTasks = getPendingTasks($user_id);
} else {
    // Statistiques du groupe
    $tasksStats = getGroupTasksStats($selected_group_id);
    $recentProjects = getGroupRecentProjects($selected_group_id);
    $pendingTasks = getGroupPendingTasks($selected_group_id);
}

// Récupération des invitations en attente
$pending_invitations = getPendingInvitations($user_id);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestion de Projets</title>
    <link rel="stylesheet" href="Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="Boostarp/css/all.min.css" />
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/groups.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <div class="dashboard-header">
                <h3 class="text-dark dashboard-title">
                    Dashboard <?php echo $selected_group ? '- ' . htmlspecialchars($selected_group['nom']) : '- Personnel'; ?>
                </h3>

                <!-- Sélecteur de groupe -->
                <div class="group-selector">
                    <form action="dashboard.php" method="get">
                        <div class="input-group">
                            <select class="form-control" name="group_id" id="group_selector" onchange="this.form.submit()">
                                <option value="">Vue Personnelle</option>
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

            <!-- Invitations en attente -->
            <?php if (!empty($pending_invitations)): ?>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
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
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <h5>Tâches</h5>
                        <canvas id="tasksChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <h5>Projets</h5>
                        <canvas id="workLogChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <h5>Performance</h5>
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <h5>Projets récents</h5>
                        <div class="recent-projects">
                            <?php if (!empty($recentProjects)): ?>
                                <?php foreach ($recentProjects as $project): ?>
                                    <div class="project-item">
                                        <div class="project-name"><?php echo htmlspecialchars($project['nom']); ?></div>
                                        <div class="project-progress">
                                            <?php
                                            $progress = 0;
                                            if ($project['total_tasks'] > 0) {
                                                $progress = round(($project['completed_tasks'] / $project['total_tasks']) * 100);
                                            }
                                            ?>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progress; ?>%</div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Aucun projet récent.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <h5>Tâches à faire</h5>
                        <div class="todo-list">
                            <?php if (!empty($pendingTasks)): ?>
                                <?php foreach ($pendingTasks as $index => $task): ?>
                                    <div class="todo-item">
                                        <input type="checkbox" id="task<?php echo $index; ?>">
                                        <label for="task<?php echo $index; ?>"><?php echo htmlspecialchars($task['titre']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Aucune tâche en attente.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Données pour les graphiques
        const tasksData = {
            completed: <?php echo isset($tasksStats['completed']) ? $tasksStats['completed'] : 0; ?>,
            on_hold: <?php echo isset($tasksStats['on_hold']) ? $tasksStats['on_hold'] : 0; ?>,
            in_progress: <?php echo isset($tasksStats['in_progress']) ? $tasksStats['in_progress'] : 0; ?>,
            pending: <?php echo isset($tasksStats['pending']) ? $tasksStats['pending'] : 0; ?>
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Graphique des tâches
            const tasksCtx = document.getElementById('tasksChart').getContext('2d');
            new Chart(tasksCtx, {
                type: 'pie',
                data: {
                    labels: ['Terminées', 'En attente', 'En cours', 'À faire'],
                    datasets: [{
                        data: [tasksData.completed, tasksData.on_hold, tasksData.in_progress, tasksData.pending],
                        backgroundColor: ['#007bff', '#6f42c1', '#17a2b8', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Graphique des projets
            const workLogCtx = document.getElementById('workLogChart').getContext('2d');
            new Chart(workLogCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Projet 1', 'Projet 2', 'Projet 3', 'Projet 4'],
                    datasets: [{
                        data: [25, 25, 25, 25],
                        backgroundColor: ['#ff6384', '#ff9f40', '#ffcd56', '#4bc0c0']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Graphique de performance
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                    datasets: [{
                            label: 'Réalisé',
                            data: [6, 7, 8, 10, 7, 9],
                            borderColor: '#ff6384',
                            fill: false
                        },
                        {
                            label: 'Objectif',
                            data: [5, 6, 7, 9, 6, 8],
                            borderColor: '#36a2eb',
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
    <script src="js/script.js"></script>
</body>

</html>