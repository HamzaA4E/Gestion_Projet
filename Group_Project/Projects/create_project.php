<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php';
$error = '';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get all users
try {
    $users = $pdo->query("SELECT * FROM users")->fetchAll();
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}

// Form handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate inputs
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $deadline = $_POST['deadline'];
        $status = $_POST['status'];
        $members = $_POST['members'] ?? [];
        $creator_id = $_SESSION['user_id'];

        // Insert project
        $stmt = $pdo->prepare("INSERT INTO projects (title, description, deadline, status, creator_id) 
                             VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $deadline, $status, $creator_id]);
        $project_id = $pdo->lastInsertId();

        // Insert members if any
        if (!empty($members)) {
            $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id) VALUES (?, ?)");
            foreach ($members as $user_id) {
                if (is_numeric($user_id)) {
                    $stmt->execute([$project_id, $user_id]);
                }
            }
        }

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Project</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Project Title</label>
                                <input type="text"
                                    class="form-control form-control-lg"
                                    name="title"
                                    required
                                    placeholder="Enter project name">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control"
                                    name="description"
                                    rows="4"
                                    required
                                    placeholder="Describe your project"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deadline</label>
                                <input type="date"
                                    class="form-control"
                                    name="deadline"
                                    required
                                    min="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="On Hold">On Hold</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Team Members</label>
                                <div class="scrollable-checkbox-group">
                                    <?php foreach ($users as $user): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="members[]"
                                                value="<?= $user['id'] ?>" id="user<?= $user['id'] ?>">
                                            <label class="form-check-label" for="user<?= $user['id'] ?>">
                                                <?= htmlspecialchars($user['username']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="index.php" class="btn btn-secondary px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Create Project
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>