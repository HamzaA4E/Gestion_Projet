<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /Gestion_Projet/Dashboard/login.php");
    exit();
}

// Get the project ID from URL
$project_id = $_GET['project_id'] ?? null;
if (!$project_id) {
    die("Project ID is required");
}

// Verify user has access to this project
$stmt = $pdo->prepare("SELECT COUNT(*) FROM projects p 
                      LEFT JOIN project_members pm ON p.id = pm.project_id
                      WHERE p.id = ? AND (p.creator_id = ? OR pm.user_id = ?)");
$stmt->execute([$project_id, $_SESSION['user_id'], $_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    die("You don't have access to this project");
}

// Get project details
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

// Get tasks for this project
// Get tasks for this project with assigned user info
$stmt = $pdo->prepare("SELECT t.*, u.prenom as assigned_firstname, u.nom as assigned_lastname 
                      FROM tasks t
                      LEFT JOIN users u ON t.assigned_to = u.id
                      WHERE t.project_id = ?");
$stmt->execute([$project_id]);
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="Boostarp/css/all.min.css" />
    <link rel="stylesheet" href="css/view.css">
    <title>AProjectO</title>
</head>

<body>
    <nav class="navbar justify-content-between container-fluid">
        <a class="navbar-brand fs-3" href="#">AProjectO</a>
        <form class="form-inline d-flex gap-2" id="searchForm">
            <input class="form-control co" type="search" placeholder="Search" aria-label="Search" id="searchInput">
            <button class="btn btn-outline-dark" type="submit">Search</button>
        </form>
    </nav>

    <div class="d-flex">
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

        <div class="Content w-100 container-fluid">
            <div class="d-flex align-items-center justify-content-between pt-5">
                <h5 class="ms-2 text-black-50">Tasks</h5>
                <div class="d-flex align-items-center gap-3">
                <button id="createTaskBtn" class="btn btn-primary" data-project-id="<?php echo $_GET['project_id'] ?? ''; ?>">
    <i class="fas fa-plus me-2"></i>Créer une tâche
</button>
                    <select aria-label="Type de vue" class="select-view" id="viewSelector">
                        <option value="colonne">Colonne</option>
                        <option value="liste" selected>Liste</option>
                    </select>
                </div>
            </div>


            
            <div class="container-fluid py-4">
    <div id="task-list-container" class="task-list-container">
        <?php if (empty($tasks)): ?>
            <div class="alert alert-info">Aucune tâche trouvée pour ce projet.</div>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div class="task-item mb-3 p-3 border rounded">
                    <div class="task-header d-flex justify-content-between align-items-center">
                        <h5><?php echo htmlspecialchars($task['title']); ?></h5>
                        <span class="badge 
                            <?php 
                                switch($task['status']) {
                                    case 'Completed': echo 'bg-success'; break;
                                    case 'In Progress': echo 'bg-warning text-dark'; break;
                                    default: echo 'bg-secondary';
                                }
                            ?>">
                            <?php echo htmlspecialchars($task['status']); ?>
                        </span>
                    </div>
                    
                    <div class="task-description my-2">
                        <p><?php echo htmlspecialchars($task['description']); ?></p>
                    </div>
                    
                    <div class="task-footer d-flex justify-content-between text-muted small">
                        <div>
                            <span class="deadline">
                                <i class="fas fa-calendar-day me-1"></i>
                                <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                            </span>
                            <?php if ($task['assigned_to']): ?>
                                <span class="ms-3">
                                    <i class="fas fa-user me-1"></i>
                                    <?php 
                                        echo htmlspecialchars(
                                            $task['assigned_firstname'] . ' ' . 
                                            $task['assigned_lastname']
                                        ); 
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <span class="priority me-2">
                                <i class="fas fa-flag me-1"></i>
                                <?php 
                                    switch($task['priority']) {
                                        case 'high': echo 'Élevée'; break;
                                        case 'medium': echo 'Moyenne'; break;
                                        default: echo 'Faible';
                                    }
                                ?>
                            </span>
                            <span class="comments-count">
                                <i class="fas fa-comment me-1"></i>
                                0 commentaires
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
        </div>
    </div>

    <!-- Popup Overlay -->
    <div id="popupOverlay" class="popup-overlay"></div>

    <script src="Boostarp/js/bootstrap.bundle.min.js"></script>
    <script src="listss.js"></script>
    
    <script>
document.getElementById('createTaskBtn').addEventListener('click', function() {
    const projectId = this.getAttribute('data-project-id');
    if (projectId) {
        window.location.href = `http://localhost/Gestion_Projet/Tasks/create_task.php?project_id=${projectId}`;
    } else {
        // Handle case where no project is selected (if applicable)
        alert('Veuillez sélectionner un projet d\'abord');
        // Or redirect to project selection:
        // window.location.href = 'http://localhost/Gestion_Projet/Projets/Projects/index.php';
    }
});
</script>
</body>

</html>