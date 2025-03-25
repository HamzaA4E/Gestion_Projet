<?php
// Désactivation des avertissements pour une meilleure expérience utilisateur
error_reporting(E_ERROR | E_PARSE);

// Inclusion du fichier de configuration
require_once 'includes/config.php';

// Fonction pour exécuter un script SQL
function executeSQLScript($pdo, $filename) {
    $success = true;
    $error_message = '';
    
    try {
        // Lecture du fichier SQL
        $sql = file_get_contents($filename);
        
        // Exécution des requêtes SQL
        $result = $pdo->exec($sql);
        
        if ($result === false) {
            $success = false;
            $error_message = implode('; ', $pdo->errorInfo());
        }
    } catch (PDOException $e) {
        $success = false;
        $error_message = $e->getMessage();
    }
    
    return [
        'success' => $success,
        'error_message' => $error_message
    ];
}

// Exécution du script SQL pour les tables de groupes
$result = executeSQLScript($pdo, 'install_groups.sql');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation des Groupes - Gestion de Projets</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Installation des Fonctionnalités de Groupes</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($result['success']): ?>
                            <div class="alert alert-success">
                                <h4>Installation réussie !</h4>
                                <p>Les tables nécessaires au système de groupes ont été créées avec succès.</p>
                                <p>Un groupe personnel a été créé pour chaque utilisateur existant.</p>
                            </div>
                            <div class="mt-4">
                                <h5>Prochaines étapes :</h5>
                                <ol>
                                    <li>Connectez-vous à l'application avec vos identifiants</li>
                                    <li>Accédez à la page "Groupes" pour créer de nouveaux groupes</li>
                                    <li>Invitez des membres en utilisant leur ID utilisateur</li>
                                    <li>Utilisez la salle de discussion pour communiquer avec les membres du groupe</li>
                                </ol>
                            </div>
                            <div class="mt-4">
                                <a href="login.php" class="btn btn-primary">Se connecter</a>
                                <a href="dashboard.php" class="btn btn-secondary">Accéder au Dashboard</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <h4>Erreur lors de l'installation</h4>
                                <p>Une erreur est survenue lors de la création des tables de groupes :</p>
                                <pre><?php echo $result['error_message']; ?></pre>
                            </div>
                            <div class="mt-4">
                                <p>Vérifiez que :</p>
                                <ul>
                                    <li>La base de données existe et est accessible</li>
                                    <li>L'utilisateur de la base de données a les droits nécessaires</li>
                                    <li>Les tables principales ont été créées via install.php</li>
                                </ul>
                            </div>
                            <div class="mt-4">
                                <a href="install.php" class="btn btn-primary">Installer les tables principales</a>
                                <a href="install_groups.php" class="btn btn-secondary">Réessayer</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
