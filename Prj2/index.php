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
    <a href="/Gestion_Projet/Dasboard/dashboard.php" 
       class="Dashboard fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Dashboard
    </a>
</div>
<div class="d-flex">
    <i class="fa-solid fa-folder"></i>
    <a href="/Gestion_Projet/Group_Project/Projects/index.php" 
       class="Projects fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Projects
    </a>
</div>
<div class="d-flex" id="tasksBtn">
    <i class="fa-solid fa-square-check"></i>
    <a href="/Gestion_Projet/Prj2/index.php" 
       class="Tasks fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Tasks
    </a>
</div>

                
<div class="d-flex">
    <i class="fa-solid fa-gauge"></i>
    <a href="/Gestion_Projet/Dasboard/profile.php" 
       class="Settings fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Settings
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
        <!-- Popup content will be loaded here dynamically -->
    </div>


 
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM entièrement chargé"); // Debug 1
    
    const projectsBtn = document.getElementById('projectsBtn');
    const tasksBtn = document.getElementById('tasksBtn');
    const projectContent = document.getElementById('project-content');
    const tasksContent = document.querySelector('.status');
    const currentViewTitle = document.querySelector('.pt-3.ms-3.text-black-50');
    const overviewTitle = document.querySelector('.Overview h4');

    console.log("Éléments DOM sélectionnés:", { // Debug 2
        projectsBtn,
        tasksBtn,
        projectContent,
        tasksContent,
        currentViewTitle,
        overviewTitle
    });

    function loadProjectContent() {
        console.log("Clic sur Projects détecté"); // Debug 3
        
        const projectUrl = '/Gestion_Projet/Group_Project/Projects/index.php';
        console.log("Tentative de chargement:", projectUrl); // Debug 4

        fetch(projectUrl)
            .then(response => {
                console.log("Réponse reçue, status:", response.status); // Debug 5
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                console.log("Chargement réussi, injection du HTML"); // Debug 6
                projectContent.innerHTML = html;
                showProjectView();
            })
            .catch(error => {
                console.error("Erreur lors du chargement:", error); // Debug 7
                projectContent.innerHTML = `
                    <div class="alert alert-danger">
                        Error loading project: ${error.message}
                    </div>
                `;
                showProjectView();
            });
    }

    function showProjectView() {
        console.log("Affichage de la vue Projet"); // Debug 8
        projectContent.style.display = 'block';
        tasksContent.style.display = 'none';
        if (currentViewTitle) currentViewTitle.textContent = 'Projects';
        if (overviewTitle) overviewTitle.textContent = 'Group Project';
    }

    function showTasksView() {
        console.log("Affichage de la vue Tâches"); // Debug 9
        tasksContent.style.display = 'flex';
        projectContent.style.display = 'none';
        if (currentViewTitle) currentViewTitle.textContent = 'Tasks';
        if (overviewTitle) overviewTitle.textContent = 'Overview';
    }

    if (projectsBtn) {
        projectsBtn.addEventListener('click', function(e) {
            console.log("Event détaillé:", e); // Debug 10
            loadProjectContent();
        });
    } else {
        console.error("Bouton Projects non trouvé!"); // Debug 11
    }

    if (tasksBtn) {
        tasksBtn.addEventListener('click', showTasksView);
    }
});
    </script>
    <!-- <script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[INIT] Script dashboard chargé');
    
    const dashboardBtn = document.getElementById('dashboardBtn');
    
    if (!dashboardBtn) {
        console.error('[ERREUR] Bouton Dashboard introuvable');
        return;
    }

    dashboardBtn.addEventListener('click', function() {
        console.log('[CLIC] Bouton Dashboard cliqué');
        
        // CHEMIN ABSOLU CORRIGÉ (notez le /Dashboard/ avec D majuscule)
        const dashboardUrl = '/Gestion_Projet/Dasboard/dashboard.php'; 
        
        console.log('[CHARGEMENT] Tentative de chargement:', dashboardUrl);
        
        // Solution 1: Chargement AJAX (recommandé)
        fetch(dashboardUrl)
            .then(response => {
                console.log('[REPONSE] Statut:', response.status);
                if (!response.ok) throw new Error('Erreur '+response.status);
                return response.text();
            })
            .then(html => {
                console.log('[SUCCÈS] Contenu chargé - Remplacement du body');
                document.body.innerHTML = html;
            })
            .catch(error => {
                console.error('[ERREUR]', error);
                // Solution de repli
                window.location.href = dashboardUrl;
            });

        // Solution alternative 2: Redirection simple (décommentez si nécessaire)
        // window.location.href = dashboardUrl;
    });
});
</script> -->
    <!-- Scripts -->
    <script src="/Boostarp/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>

</body>
</html>