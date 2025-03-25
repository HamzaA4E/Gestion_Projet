-- Créer la base de données
CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

-- Créer la table tasks
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Backlog', 'In Progress', 'Completed') NOT NULL,
    deadline DATETIME, -- Champ ajouté
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Accorder les privilèges à l'utilisateur root
GRANT ALL PRIVILEGES ON task_manager.* TO 'root'@'localhost';
FLUSH PRIVILEGES;