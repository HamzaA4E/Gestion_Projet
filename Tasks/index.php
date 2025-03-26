<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="Boostarp/css/all.min.css" />
    <link rel="stylesheet" href="css/index.css">
    <title>AProjectO</title>
    <style>
       
    </style>
</head>
<body>
    <nav class="navbar justify-content-between container-fluid py-2">
        <a class="navbar-brand fs-3" href="#">AProjectO</a>
        <form class="form-inline d-flex gap-2">
            <input class="form-control" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-dark" type="submit">Search</button>
        </form>
    </nav>
  
    <div class="d-flex flex-lg-row flex-column">
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
  
        <div class="Content w-100">
            <h5 class="pt-3 ms-3 text-black-50">Tasks</h5>
            <div class="Overview d-flex flex-wrap mt-4 ps-lg-5 ps-3 justify-content-between align-items-center position-relative mb-5">
                <h4>Overview</h4>
                <div class="d-none d-md-flex me-3">
                    <form class="form-inline d-flex gap-2" onsubmit="searchTasks(event)">
                        <input id="searchInput" class="form-control" type="search" placeholder="Search Projects" aria-label="Search">
                        <button class="btn btn-outline-dark" type="submit">Search</button>
                    </form>
                </div>
            </div>
            <select aria-label="Type de vue" class="me-5 select-view" id="viewSelector">
                <option value="colonne">Colonne</option>
                <option value="liste">Liste</option>
            </select>



           
            <div class="status mt-4 ps-lg-3 ps-2 pe-lg-3 pe-2 row mx-0" style="width: 100%;">
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="Backlog shadow-sm rounded-3">
                    
                        <h4 class="p-3 rounded-top-3">Backlog</h4>
                        <div class="p-3 task-container">
                            <div class="add-task-btn">
                                <pre class="b6 d-flex fs-2 align-items-center justify-content-center rounded-3">+</pre>
                            </div>
                            <div class="column-empty-area"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-sm-12 mb-4" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="InProgress shadow-sm rounded-3">
                        <h4 class="p-3 rounded-top-3">In Progress</h4>
                        <div class="p-3 task-container">
                            <div class="add-task-btn">
                                <pre class="b6 d-flex fs-2 align-items-center justify-content-center rounded-3">+</pre>
                            </div>
                            <div class="column-empty-area"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 col-sm-12 mb-4" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="Completed shadow-sm rounded-3">
                        <h4 class="p-3 rounded-top-3">Completed</h4>
                        <div class="p-3 task-container">
                            <div class="add-task-btn">
                                <pre class="b6 d-flex fs-2 align-items-center justify-content-center rounded-3">+</pre>
                            </div>
                            <div class="column-empty-area"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="project-content" style="display: none;"></div>
            <div id="dashboard-content" style="display: none;"></div>



        </div>
    </div>
    <div id="customConfirmOverlay" class="confirm-overlay">
        <div class="confirm-box">
            <p id="confirmMessage">Êtes-vous sûr de vouloir supprimer cette tâche ?</p>
            <div class="confirm-buttons">
                <button id="confirmYes" class="confirm-button confirm-yes">Oui</button>
                <button id="confirmNo" class="confirm-button confirm-no">Non</button>
            </div>
        </div>
    </div>
    <div class="popup-overlay" id="popupOverlay">
       
    </div>

    <script>
        // Dans votre script.js
document.getElementById('viewSelector')?.addEventListener('change', function() {
    const selectedView = this.value;
    
    if (selectedView === 'liste') {
        // Utilisez soit un chemin absolu
        window.location.href = '/Gestion_Projet/Tasks/liste.php';
        
        // Ou un chemin relatif si vous préférez
        // window.location.href = 'Tasks/liste.php';
    }
});
</script>
    <script src="Boostarp/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    

</body>
</html>