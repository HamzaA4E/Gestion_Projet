<?php
// view2.php

// Connexion à la base de données
$host = 'localhost';
$dbname = 'task_manager';
$username = 'root'; // Remplacez par votre nom d'utilisateur MySQL
$password = ''; // Remplacez par votre mot de passe MySQL

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer toutes les tâches
    $stmt = $conn->query("SELECT * FROM tasks");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/Boostarp/css/all.min.css" />
    <link rel="stylesheet" href="./index.css">
    <link rel="stylesheet" href="./view2.css">
    <title>AProjectO</title>
</head>
<body>
    <nav class="navbar justify-content-between container-fluid">
        <a class="navbar-brand fs-3" href="#">AProjectO</a>
        <form class="form-inline d-flex gap-2">
            <input class="form-control co" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-dark" type="submit">Search</button>
        </form>
    </nav>

    <div class="d-flex">
        <div class="sidebar">
            <ul class="d-flex gap-4 ulSidebar mt-4 ps-4">
                <li class="Projects fs-5 fw-bold">Projects</li>
                <li class="Tasks fs-5 fw-bold">Tasks</li>
                <li class="Favoris fs-5 fw-bold">Favoris</li>
                <li class="Discussion fs-5 fw-bold">Discussion</li>
                <li class="Settings fs-5 fw-bold">Settings</li>
            </ul>
        </div>

        <div class="Content w-100 container-fluid">
            <div class="d-flex align-items-center justify-content-between pt-5">
                <h5 class="ms-2 text-black-50">Tasks</h5>
                <select aria-label="Type de vue" class="me-5 select-view" id="viewSelector">
                    <option value="colonne">Colonne</option>
                    <option value="liste">Liste</option>
                </select>
            </div>

            <div class="container-fluid py-4">
                <?php foreach ($tasks as $task) : ?>
                    <div class="task-card">
                        <div class="d-flex align-items-center flex-wrap">
                            <div class="task-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-column flex-md-row align-items-md-center mb-1">
                                    <div class="me-auto">
                                        <p class="task-title"><?php echo htmlspecialchars($task['title']); ?></p>
                                        <p class="task-id">
                                            #<?php echo $task['id']; ?> - <?php echo $task['status']; ?>
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2 mt-2 mt-md-0">
                                        <span class="status-completed"><?php echo $task['status']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center ms-md-3 mt-2 mt-md-0 action-section">
                                <div class="time-display me-3">
                                    <i class="fas fa-clock time-icon"></i>
                                    <?php echo date('H:i:s', strtotime($task['created_at'])); ?>
                                </div>
                                <div class="avatar me-3">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User avatar">
                                </div>
                                <span class="count-badge me-3">2</span>
                                <div class="d-flex">
                                    <div class="action-icon me-2">
                                        <i class="fas fa-list"></i>
                                    </div>
                                    <div class="action-icon">
                                        <i class="far fa-comment-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>