USE gestion_projets;

-- Users Table with auto-generated username
CREATE TABLE utilisateurs (
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

-- Projects Table (unchanged)
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

-- Project Members (unchanged)
CREATE TABLE IF NOT EXISTS project_members (
    project_id INT,
    user_id INT,
    is_admin TINYINT(1) DEFAULT 0,
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert test users (username auto-generated as "prenom.nom")
INSERT IGNORE INTO users (nom, prenom, password, email) VALUES
('Agrawal', 'Anima', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'anima@example.com'),
('Dupont', 'Jean', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jean@example.com'),
('Curie', 'Marie', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marie@example.com');