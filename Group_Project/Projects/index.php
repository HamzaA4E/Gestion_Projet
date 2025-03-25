<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM projects WHERE creator_id = ?");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AProjectO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <div class="brand-icon"><img src="/Group_Project/Projects/Logo.svg" alt="Logo"></div>
            <div class="brand-text">AProjectO</div>
        </div>
        <div class="menu">
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
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container-fluid">
            <div class="flex-grow-1"></div>
            <div class="search-box navbar_search me-3">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control" placeholder="Search for anything...">
            </div>
            <div class="notifications me-3">
                <i class="far fa-bell"></i>
            </div>
            <div class="user-profile">
                <img src="/Group_Project/Projects/profile_photo.svg" alt="User" class="user-avatar">
                <div class="user-info">
                    <p class="user-name">Anima Agrawal</p>
                    <p class="user-location">UP, India</p>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Projects Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Projects</h2>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-create" onclick="window.location.href='create_project.php'">
                    <i class="fas fa-plus"></i> Create Project
                </button>
                <div class="search-box" style="margin-left: 60px;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" placeholder="Search for anything...">
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="row">
            <?php if (empty($projects)): ?>
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted">No projects found. Create your first project!</h4>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="col-md-4 mb-4">
                        <div class="project-card position-relative">
                            <div class="project-card-header">
                                <h3 class="project-title"><?= htmlspecialchars($project['title']) ?></h3>
                                <a href="edit_project.php?id=<?= $project['id'] ?>" class="edit-button">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                            <div class="project-card-body">
                                <p class="project-desc"><?= htmlspecialchars($project['description']) ?></p>
                                <p class="project-deadline">Deadline: <?= date('d F Y', strtotime($project['deadline'])) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="team-member-count">
                                        <i class="fas fa-users member-icon"></i>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) AS member_count 
                                        FROM project_members 
                                        WHERE project_id = ?");
                                        $stmt->execute([$project['id']]);
                                        $result = $stmt->fetch();
                                        $member_count = $result['member_count'];
                                        $stmt->execute([$project['id']]);
                                        $member_count = $stmt->fetchColumn();
                                        echo $member_count . ' Members';
                                        ?>
                                    </div>
                                </div>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '', $project['status'])) ?>">
                                    <?= htmlspecialchars($project['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>