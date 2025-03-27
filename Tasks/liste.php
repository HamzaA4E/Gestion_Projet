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
                    <button id="createTaskBtn" class="btn btn-primary">
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
        <!-- Exemple de structure de tâche COMPLÈTE -->
        <div class="task-item">
            <div class="task-header">
                <h5>Titre de la tâche</h5>
                <span class="task-status">Status</span>
            </div>
            <div class="task-description">
                <p>Description de la tâche</p>
            </div>
            <div class="task-footer">
                <span class="deadline">Date limite</span>
                <span class="comments-count">0 commentaires</span>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>

    <!-- Popup Overlay -->
    <div id="popupOverlay" class="popup-overlay"></div>
    
    <script src="Boostarp/js/bootstrap.bundle.min.js"></script>
    <script src="list.js"></script>
    
    <script>
    // Remplacer la gestion existante du popup par une redirection simple
    document.getElementById('createTaskBtn').addEventListener('click', function() {
        window.location.href = 'http://localhost/Gestion_Projet/Tasks/create_task.php';
    });
</script>
</body>
</html>