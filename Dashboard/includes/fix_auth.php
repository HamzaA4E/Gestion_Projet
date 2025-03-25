<?php
/**
 * Script pour corriger le problème d'authentification
 * Ce script met à jour le mot de passe de l'utilisateur de test
 */

// Inclusion du fichier de configuration
require_once 'config.php';

// Mot de passe en clair
$password = 'password123';

// Génération d'un nouveau hash avec la version actuelle de PHP
$new_hash = password_hash($password, PASSWORD_DEFAULT);

// Mise à jour du mot de passe dans la base de données
try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $result = $stmt->execute([$new_hash, 'jean.dupont@example.com']);
    
    if ($result) {
        echo "<h1>Correction du problème d'authentification</h1>";
        echo "<p style='color: green;'>✓ Le mot de passe de l'utilisateur de test a été mis à jour avec succès.</p>";
        echo "<p>Nouveau hash généré : " . $new_hash . "</p>";
        echo "<p>Vous pouvez maintenant vous connecter avec :</p>";
        echo "<ul>";
        echo "<li>Email : jean.dupont@example.com</li>";
        echo "<li>Mot de passe : password123</li>";
        echo "</ul>";
        echo "<p><a href='../test.php'>Retour aux tests</a> | <a href='../login.php'>Page de connexion</a></p>";
    } else {
        echo "<h1>Erreur</h1>";
        echo "<p style='color: red;'>✗ Une erreur est survenue lors de la mise à jour du mot de passe.</p>";
    }
} catch (PDOException $e) {
    echo "<h1>Erreur</h1>";
    echo "<p style='color: red;'>✗ Erreur de base de données : " . $e->getMessage() . "</p>";
}
?>
