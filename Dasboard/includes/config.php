<?php
/**
 * Configuration de la base de données et paramètres de l'application
 */

// Suppression des paramètres de session qui causent les avertissements
// Ces paramètres peuvent être définis dans php.ini si nécessaire

// Informations de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_projets');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Options PDO
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Connexion à la base de données
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En production, il faudrait logger l'erreur et afficher un message générique
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Paramètres de l'application
define('APP_NAME', 'Gestion de Projets');
define('APP_URL', 'http://localhost/projet_gestion');
define('UPLOAD_DIR', 'img/profiles/');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');
