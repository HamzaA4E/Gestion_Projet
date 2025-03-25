<?php
/**
 * Script d'installation de la base de données
 * Ce fichier permet de créer la base de données et les tables nécessaires
 */

// Inclusion du fichier de configuration
require_once 'includes/config.php';

// Lecture du fichier SQL
$sql_file = file_get_contents('database.sql');

// Séparation des requêtes
$queries = explode(';', $sql_file);

// Exécution des requêtes
$success = true;
$error_messages = [];

echo "<h1>Installation de la base de données</h1>";
echo "<p>Exécution des requêtes SQL...</p>";

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        try {
            $pdo->exec($query);
            echo "<p style='color: green;'>✓ Requête exécutée avec succès</p>";
        } catch (PDOException $e) {
            $success = false;
            $error_message = "Erreur lors de l'exécution de la requête : " . $e->getMessage();
            $error_messages[] = $error_message;
            echo "<p style='color: red;'>✗ " . $error_message . "</p>";
        }
    }
}

if ($success) {
    echo "<h2 style='color: green;'>Installation terminée avec succès !</h2>";
    echo "<p>La base de données a été correctement installée.</p>";
    echo "<p>Vous pouvez maintenant vous <a href='login.php'>connecter</a> avec les identifiants suivants :</p>";
    echo "<ul>";
    echo "<li>Email : jean.dupont@example.com</li>";
    echo "<li>Mot de passe : password123</li>";
    echo "</ul>";
} else {
    echo "<h2 style='color: red;'>Des erreurs sont survenues lors de l'installation</h2>";
    echo "<p>Veuillez corriger les erreurs suivantes :</p>";
    echo "<ul>";
    foreach ($error_messages as $message) {
        echo "<li>" . $message . "</li>";
    }
    echo "</ul>";
}
?>
