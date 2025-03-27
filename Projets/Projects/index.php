<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Gestion_Projet/Dashboard/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get projects where user is either creator or member
$stmt = $pdo->prepare("SELECT p.* FROM projects p
                      LEFT JOIN project_members pm ON p.id = pm.project_id
                      WHERE p.creator_id = ? OR pm.user_id = ?
                      GROUP BY p.id");
$stmt->execute([$user_id, $user_id]);
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
    <link rel="stylesheet" href="Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="Boostarp/css/all.min.css" />
    <link rel="stylesheet" href="style.css">
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

        <!-- Main Content -->
        <div class="main-content">
            <!-- Projects Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Projects</h2>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-create" onclick="window.location.href='create_project.php'">
                        <i class="fas fa-plus"></i> Create Project
                    </button>
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control" placeholder="Search projects...">
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Projects Grid -->
            <div class="row">
                <?php if (empty($projects)): ?>
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">No projects found. Create your first project!</h4>
                    </div>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <?php
                        // Get member count and check if current user is member (not creator)
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM project_members WHERE project_id = ?");
                        $stmt->execute([$project['id']]);
                        $member_count = $stmt->fetchColumn();

                        $is_creator = ($project['creator_id'] == $_SESSION['user_id']);
                        $is_member = !$is_creator;
                        ?>
                        <div class="col-md-4 mb-4" onclick="window.location='./Tasks/index.php?project_id=<?= $project['id'] ?>'">
                            <div class="project-card position-relative">
                                <div class="project-card-header">
                                    <h3 class="project-title"><?= htmlspecialchars($project['title']) ?></h3>
                                    <?php if ($is_creator): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="edit_project.php?id=<?= $project['id'] ?>" class="edit-button">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-link text-danger delete-project-btn"
                                                data-project-id="<?= $project['id'] ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    <?php elseif ($is_member): ?>
                                        <span class="member-badge bg-primary text-white px-3 py-1 rounded-pill">
                                            <i class="fas fa-user-check me-1"></i>Member
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="project-card-body">
                                    <p class="project-desc"><?= htmlspecialchars($project['description']) ?></p>
                                    <p class="project-deadline">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        <?= date('M d, Y', strtotime($project['deadline'])) ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="team-member-count">
                                            <i class="fas fa-users me-2"></i>
                                            <?= $member_count ?> Member<?= $member_count != 1 ? 's' : '' ?>
                                        </div>
                                        <span class="status-badge status-<?= strtolower(str_replace(' ', '', $project['status'])) ?>">
                                            <?= htmlspecialchars($project['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <p>Are you sure you want to delete this project? This action cannot be undone.</p>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                                <i class="fas fa-trash-alt me-2"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="script.js"></script>
</body>

</html>