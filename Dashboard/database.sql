-- Script SQL pour initialiser la base de données du projet de gestion de projets

-- Création de la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS gestion_projets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisation de la base de données
USE gestion_projets;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    poste VARCHAR(100) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    date_creation DATETIME NOT NULL,
    date_modification DATETIME DEFAULT NULL,
    derniere_connexion DATETIME DEFAULT NULL,
    status ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des projets
CREATE TABLE IF NOT EXISTS projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    deadline DATE,
    status ENUM('en_cours', 'termine', 'en_pause', 'annule') NOT NULL DEFAULT 'en_cours',
    creator_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des membres du projet
CREATE TABLE IF NOT EXISTS project_members (
    project_id INT,
    user_id INT,
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des tâches
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    project_id INT NOT NULL,
    assigned_to INT DEFAULT NULL,
    status ENUM('pending', 'in_progress', 'on_hold', 'completed') NOT NULL DEFAULT 'pending',
    priorite ENUM('basse', 'moyenne', 'haute', 'urgente') NOT NULL DEFAULT 'moyenne',
    date_debut DATE,
    date_echeance DATE,
    date_completion DATE DEFAULT NULL,
    date_creation DATETIME NOT NULL,
    date_modification DATETIME DEFAULT NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des discussions
CREATE TABLE IF NOT EXISTS discussions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    date_creation DATETIME NOT NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des favoris
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    date_ajout DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion d'un utilisateur de test (mot de passe: password123)
INSERT IGNORE INTO users (nom, prenom, email, password, telephone, poste, date_creation)
VALUES ('Dupont', 'Jean', 'jean.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', 'Chef de projet', NOW());

-- Insertion de projets de test
INSERT INTO projects (id, title, description, deadline, status, creator_id, created_at)
VALUES 
(1,'Refonte site web', 'Refonte complète du site web de l\'entreprise', '2025-01-01', 'en_cours', 1, NOW()),
(2,'Application mobile', 'Développement d\'une application mobile pour les clients', '2025-08-15', 'en_cours', 1, NOW())
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Insertion de tâches de test
INSERT INTO tasks (titre, description, project_id, assigned_to, status, priorite, date_debut, date_echeance, date_creation)
VALUES 
('Réunion avec le client', 'Présentation du projet et recueil des besoins', 1, 1, 'pending', 'haute', '2025-03-25', '2025-03-25', NOW()),
('Finaliser la maquette', 'Terminer la maquette du site web', 1, 1, 'in_progress', 'moyenne', '2025-03-20', '2025-03-30', NOW()),
('Tester lapplication', 'Tests fonctionnels de lapplication mobile', 2, 1, 'pending', 'haute', '2025-04-01', '2025-04-15', NOW()),
('Préparer la présentation', 'Préparer la présentation pour la réunion de projet', 1, 1, 'pending', 'moyenne', '2025-03-28', '2025-04-05', NOW());
