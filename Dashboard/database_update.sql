-- Script SQL pour ajouter les tables nécessaires au système de groupes et de discussion

-- Table des groupes
CREATE TABLE IF NOT EXISTS groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    creator_id INT NOT NULL,
    date_creation DATETIME NOT NULL,
    date_modification DATETIME DEFAULT NULL,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des membres du groupe
CREATE TABLE IF NOT EXISTS group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin', 'membre') NOT NULL DEFAULT 'membre',
    date_ajout DATETIME NOT NULL,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_member (group_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des invitations aux groupes
CREATE TABLE IF NOT EXISTS group_invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    statut ENUM('en_attente', 'acceptee', 'refusee') NOT NULL DEFAULT 'en_attente',
    date_invitation DATETIME NOT NULL,
    date_reponse DATETIME DEFAULT NULL,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_invitation (group_id, receiver_id, statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des messages de groupe
CREATE TABLE IF NOT EXISTS group_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    mentions TEXT DEFAULT NULL,
    date_creation DATETIME NOT NULL,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajout d'un groupe personnel pour l'utilisateur de test
INSERT INTO groups (nom, description, creator_id, date_creation)
VALUES ('Personnel', 'Espace personnel de Jean Dupont', 1, NOW());

-- Ajout de l'utilisateur comme admin de son groupe personnel
INSERT INTO group_members (group_id, user_id, role, date_ajout)
VALUES (1, 1, 'admin', NOW());
