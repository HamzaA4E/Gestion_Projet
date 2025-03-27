<?php
session_start();
require 'db.php';

$error = '';
$project = [];
$users = [];
$selected_members = [];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// First, verify the is_admin column exists
try {
    $pdo->query("SELECT is_admin FROM project_members LIMIT 1");
} catch (PDOException $e) {
    // If column doesn't exist, add it
    $pdo->exec("ALTER TABLE project_members ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
}

// Get project data
try {
    $project_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND creator_id = ?");
    $stmt->execute([$project_id, $user_id]);
    $project = $stmt->fetch();

    if (!$project) {
        $_SESSION['error'] = "You don't have permission to edit this project";
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

try {
    // Get creator info
    $stmt = $pdo->prepare("SELECT u.* FROM users u WHERE u.id = ?");
    $stmt->execute([$project['creator_id']]);
    $creator = $stmt->fetch();

    // Get other users (excluding creator)
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id != ?");
    $stmt->execute([$project['creator_id']]);
    $users = $stmt->fetchAll();

    // Get existing members (including creator for count)
    $stmt = $pdo->prepare("SELECT user_id FROM project_members WHERE project_id = ?");
    $all_members = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get non-admin members for checkbox selection
    $stmt = $pdo->prepare("SELECT user_id FROM project_members WHERE project_id = ? AND user_id != ?");
    $stmt->execute([$project_id, $project['creator_id']]);
    $selected_members = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Store member count in session for feedback
    $_SESSION['member_count'] = count($all_members);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $deadline = $_POST['deadline'];
        $status = $_POST['status'];
        $new_members = $_POST['members'] ?? [];

        // Start transaction
        $pdo->beginTransaction();

        // Update project
        $stmt = $pdo->prepare("UPDATE projects SET 
            title = ?, 
            description = ?, 
            deadline = ?, 
            status = ? 
            WHERE id = ?");
        $stmt->execute([$title, $description, $deadline, $status, $project_id]);

        // Delete non-admin members (preserves creator/admin)
        $stmt = $pdo->prepare("DELETE FROM project_members 
                             WHERE project_id = ? 
                             AND user_id != ? 
                             AND is_admin = 0");
        $stmt->execute([$project_id, $project['creator_id']]);

        // Insert new members (excluding creator)
        if (!empty($new_members)) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO project_members 
                                 (project_id, user_id, is_admin) 
                                 VALUES (?, ?, 0)");
            foreach ($new_members as $user_id) {
                if ($user_id != $project['creator_id']) {
                    $stmt->execute([$project_id, $user_id]);
                }
            }
        }

        // Get updated member count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM project_members WHERE project_id = ?");
        $stmt->execute([$project_id]);
        $member_count = $stmt->fetchColumn();

        $pdo->commit();
        $_SESSION['success'] = "Project updated successfully! Team members: " . $member_count;
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

                            <!-- Team Members Section -->
                            <div class="mb-4">
                                <label class="form-label">Team Members (Current: <?= $_SESSION['member_count'] ?? '0' ?>)</label>
                                <div class="scrollable-checkbox-group border rounded p-3">
                                    <!-- Admin section -->
                                    <div class="mb-3 p-2 bg-light rounded">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" checked disabled>
                                            <label class="form-check-label fw-bold text-primary">
                                                <i class="fas fa-crown me-2"></i>
                                                <?= htmlspecialchars($creator['username']) ?> (Admin)
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Other members -->
                                    <?php foreach ($users as $user): ?>
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                name="members[]"
                                                value="<?= $user['id'] ?>"
                                                id="member_<?= $user['id'] ?>"
                                                <?= in_array($user['id'], $selected_members) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="member_<?= $user['id'] ?>">
                                                <?= htmlspecialchars($user['username']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
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