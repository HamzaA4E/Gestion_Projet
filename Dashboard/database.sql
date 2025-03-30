-- Script SQL pour initialiser la base de données du projet de gestion de projets

-- Création de la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS gestion_projets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisation de la base de données
USE gestion_projets;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    username VARCHAR(100) GENERATED ALWAYS AS (CONCAT(nom, ' ', prenom)) STORED,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    poste VARCHAR(50),
    profile_image VARCHAR(255),
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    derniere_connexion DATETIME,
    statut ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif'
);

-- Table des projets
CREATE TABLE IF NOT EXISTS projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    deadline DATE,
    status ENUM('Ongoing','On Hold','Completed') DEFAULT 'Ongoing',
    creator_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des membres du projet
CREATE TABLE IF NOT EXISTS project_members (
    project_id INT,
    user_id INT,
    is_admin TINYINT(1) DEFAULT 0,
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE tasks (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    project_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Backlog', 'In Progress', 'Complete') NOT NULL,
    due_date DATE,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    assigned_to INT(11),
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

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
