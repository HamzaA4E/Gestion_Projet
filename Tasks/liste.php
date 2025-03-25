<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Prj2/Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/Prj2/Boostarp/css/all.min.css" />
    <link rel="stylesheet" href="/Prj2/css/view2.css">
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
            <ul class="ulSidebar">
                <li class="d-flex align-items-center">
                    <i class="fa-solid fa-pen me-2"></i>
                    <span class="fs-5 fw-bold">Projects</span>
                </li>
                <li class="d-flex align-items-center">
                    <i class="fa-solid fa-square-check me-2"></i>
                    <span class="fs-5 fw-bold">Tasks</span>
                </li>
                <li class="d-flex align-items-center">
                    <i class="fa-solid fa-heart me-2"></i>
                    <span class="fs-5 fw-bold">Favoris</span>
                </li>
                <li class="d-flex align-items-center">
                    <i class="fa-solid fa-gear me-2"></i>
                    <span class="fs-5 fw-bold">Settings</span>
                </li>
            </ul>
        </div>

        <div class="Content w-100 container-fluid">
            <div class="d-flex align-items-center justify-content-between pt-5">
                <h5 class="ms-2 text-black-50">Tasks</h5>
                <select aria-label="Type de vue" class="me-5 select-view" id="viewSelector">
                    <option value="colonne">Colonne</option>
                    <option value="liste" selected>Liste</option>
                </select>
            </div>

            <div class="container-fluid py-4">
                <div id="task-list-container" class="task-list-container">
                    <!-- Les tâches seront chargées dynamiquement ici -->
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Overlay -->
    <div id="popupOverlay" class="popup-overlay"></div>
    
    <script src="/Prj2/Boostarp/js/bootstrap.bundle.min.js"></script>
    <script src="/Prj2/list.js"></script>
</body>

</html>