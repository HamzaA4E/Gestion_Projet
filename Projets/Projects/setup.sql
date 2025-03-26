-- Création de la base de données
CREATE DATABASE IF NOT EXISTS gestion_projets;
USE gestion_projets;

-- Table des projets (version autonome sans référence à users)
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    deadline DATE,
    status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des tâches

-- Table des membres de projet (version simplifiée sans users)
CREATE TABLE project_members (
    project_id INT,
    member_name VARCHAR(100),  -- Champ texte libre à la place de user_id
    role VARCHAR(50),
    PRIMARY KEY (project_id, member_name),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);