<?php
session_start();
require '../Dashboard/includes/config.php';
require '../Dashboard/includes/functions.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Dashboard/login.php');
    exit;
}

// Récupération et validation de l'ID du projet
$project_id = (int)($_GET['project_id'] ?? 0);
if ($project_id <= 0) {
    header('Location: liste.php');
    exit;
}

// Vérification du projet et des accès
$project = getProjectById($project_id);
if (!$project || !hasAccess($project_id, 'project', $_SESSION['user_id'])) {
    header('Location: liste.php');
    exit;
}

// Récupération des utilisateurs
$users = getAllUsers();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    $status = 'Backlog';
    $assigned_to = $_POST['assigned_to'] ?? null;
    $created_by = $_SESSION['user_id'];

    // Validation
    if (empty($title)) {
        $error = "Le titre de la tâche est obligatoire";
    } else {
        try {
            // Préparation de la requête
            $stmt = $pdo->prepare("INSERT INTO tasks 
                (title, description, due_date, priority, status, assigned_to, project_id, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
            // Exécution avec les paramètres
            $success = $stmt->execute([
                $title, 
                $description, 
                $due_date, 
                $priority, 
                $status, 
                $assigned_to,
                $project_id, 
                $created_by
            ]);

            if ($success) {
                header("Location: liste.php?project_id=$project_id");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de la création de la tâche: " . $e->getMessage();
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une nouvelle tâche</title>
    <link rel="stylesheet" href="Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="Boostarp/css/all.min.css" />
    <style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --success-color: #4cc9f0;
        --danger-color: #f72585;
        --light-color: #f8f9fa;
        --dark-color: #212529;
        --border-radius: 8px;
        --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
        color: var(--dark-color);
        line-height: 1.6;
    }

    .form-container {
        max-width: 700px;
        margin: 40px auto;
        padding: 30px;
        background: #fff;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: var(--transition);
    }

    .form-container:hover {
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    h2 {
        color: var(--primary-color);
        text-align: center;
        margin-bottom: 30px;
        font-weight: 600;
        position: relative;
        padding-bottom: 10px;
    }

    h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: var(--success-color);
    }

    .form-label {
        font-weight: 500;
        color: var(--dark-color);
        margin-bottom: 8px;
        display: block;
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: var(--border-radius);
        padding: 12px 15px;
        transition: var(--transition);
        box-shadow: none;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .btn {
        padding: 10px 20px;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
        letter-spacing: 0.5px;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
        transform: translateY(-2px);
    }

    .alert {
        border-radius: var(--border-radius);
        padding: 15px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    /* Animation pour les champs requis */
    input:required, select:required {
        border-left: 3px solid var(--primary-color);
    }

    /* Style pour la date */
    input[type="date"] {
        position: relative;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        background: transparent;
        bottom: 0;
        color: transparent;
        cursor: pointer;
        height: auto;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        width: auto;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .form-container {
            margin: 20px 15px;
            padding: 20px;
        }
        
        h2 {
            font-size: 1.5rem;
        }
    }

    /* Effet de chargement */
    .loading {
        display: none;
        text-align: center;
        margin-top: 20px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        margin: 0 auto;
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-left-color: var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Style pour les options du select */
    option {
        padding: 10px;
    }

    /* Style pour le conteneur des boutons */
    .button-container {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    
</style>
</head>
<body>
   

    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">Créer une nouvelle tâche</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="title" class="form-label">Titre *</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="due_date" class="form-label">Date d'échéance</label>
                    <input type="date" class="form-control" id="due_date" name="due_date">
                </div>

                <div class="mb-3">
                    <label for="priority" class="form-label">Priorité</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="low">Faible</option>
                        <option value="medium" selected>Moyenne</option>
                        <option value="high">Élevée</option>
                    </select>
                </div>
                <div class="mb-3">
    <label class="form-label">Projet</label>
    <div class="form-control-plaintext">
        <strong><?php echo htmlspecialchars($project['title']); ?></strong>
        <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
    </div>
</div>

                <div class="mb-3">
                    <label for="assigned_to" class="form-label">Assigner à</label>
                    <select class="form-select" id="assigned_to" name="assigned_to">
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['prenom'] . ' ' . htmlspecialchars($user['nom'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <p><button type="submit" class="btn btn-primary">Créer la tâche</button></p>
                <a href="liste.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>

    <script src="../Bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>