<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /Gestion_Projet/Dashboard/login.php");
    exit();
}

$error = '';
$project_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify project ownership
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND creator_id = ?");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch();

if (!$project) {
    die("Access denied or project not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $deadline = $_POST['deadline'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, deadline = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $description, $deadline, $status, $project_id]);

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error updating project: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Project</h3>
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
                                    value="<?= htmlspecialchars($project['title']) ?>"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control"
                                    name="description"
                                    rows="4"
                                    required><?= htmlspecialchars($project['description']) ?></textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Deadline</label>
                                    <input type="date"
                                        class="form-control"
                                        name="deadline"
                                        value="<?= $project['deadline'] ?>"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="Ongoing" <?= $project['status'] === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                        <option value="On Hold" <?= $project['status'] === 'On Hold' ? 'selected' : '' ?>>On Hold</option>
                                        <option value="Completed" <?= $project['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="index.php" class="btn btn-secondary px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Save Changes
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