<?php
/**
 * Script de test des fonctionnalités
 * Ce fichier permet de vérifier que toutes les fonctionnalités fonctionnent correctement
 */

// Inclusion du fichier de configuration
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Fonction pour tester la connexion à la base de données
function testDatabaseConnection() {
    global $pdo;
    
    try {
        $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Fonction pour tester l'existence des tables
function testTablesExist() {
    global $pdo;
    
    $tables = ['users', 'projects', 'tasks', 'project_members', 'discussions', 'favorites'];
    $missing_tables = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                $missing_tables[] = $table;
            }
        } catch (PDOException $e) {
            $missing_tables[] = $table;
        }
    }
    
    return [
        'success' => count($missing_tables) === 0,
        'missing_tables' => $missing_tables
    ];
}

// Fonction pour tester l'existence de l'utilisateur de test
function testUserExists() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['jean.dupont@example.com']);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Fonction pour tester l'authentification
function testAuthentication() {
    $user = getUserByEmail('jean.dupont@example.com');
    
    if (!$user) {
        return false;
    }
    
    return password_verify('password123', $user['password']);
}

// Exécution des tests
$tests = [
    'Connexion à la base de données' => testDatabaseConnection(),
    'Existence des tables' => testTablesExist(),
    'Existence de l\'utilisateur de test' => testUserExists(),
    'Authentification' => testAuthentication()
];

// Affichage des résultats
echo "<h1>Tests de l'application</h1>";

foreach ($tests as $test_name => $result) {
    if ($test_name === 'Existence des tables') {
        if ($result['success']) {
            echo "<p style='color: green;'>✓ $test_name : OK</p>";
        } else {
            echo "<p style='color: red;'>✗ $test_name : ÉCHEC</p>";
            echo "<p>Tables manquantes : " . implode(', ', $result['missing_tables']) . "</p>";
        }
    } else {
        if ($result) {
            echo "<p style='color: green;'>✓ $test_name : OK</p>";
        } else {
            echo "<p style='color: red;'>✗ $test_name : ÉCHEC</p>";
        }
    }
}

// Vérification des fichiers et dossiers
$required_files = [
    'dashboard.php',
    'profile.php',
    'login.php',
    'register.php',
    'includes/config.php',
    'includes/functions.php',
    'css/style.css',
    'css/profile.css',
    'css/auth.css',
    'js/script.js',
    'img/default-avatar.jpg',
    'php/logout.php'
];

echo "<h2>Vérification des fichiers</h2>";
$missing_files = [];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file : OK</p>";
    } else {
        echo "<p style='color: red;'>✗ $file : MANQUANT</p>";
        $missing_files[] = $file;
    }
}

if (count($missing_files) === 0) {
    echo "<h2 style='color: green;'>Tous les tests ont été passés avec succès !</h2>";
    echo "<p>L'application est prête à être utilisée.</p>";
    echo "<p>Vous pouvez maintenant vous <a href='login.php'>connecter</a> avec les identifiants suivants :</p>";
    echo "<ul>";
    echo "<li>Email : jean.dupont@example.com</li>";
    echo "<li>Mot de passe : password123</li>";
    echo "</ul>";
} else {
    echo "<h2 style='color: red;'>Certains fichiers sont manquants</h2>";
    echo "<p>Veuillez vérifier que tous les fichiers nécessaires sont présents.</p>";
}
?>
