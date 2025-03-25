<?php
// db.php
$host = 'localhost'; // Ou l'adresse de votre serveur MySQL
$dbname = 'task_manager';
$username = 'root'; // Remplacez par votre nom d'utilisateur MySQL
$password = ''; // Remplacez par votre mot de passe MySQL

try {
    // Connexion à la base de données avec encodage UTF-8
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Message de débogage (à supprimer en production)
    // echo "Connexion à la base de données réussie !";
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage() . "<br>Vérifiez les informations de connexion dans db.php.");
}
?>