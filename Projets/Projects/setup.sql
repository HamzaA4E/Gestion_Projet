USE gestion_projets;

-- Users Table with auto-generated username
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    username VARCHAR(50) GENERATED ALWAYS AS (
        LOWER(CONCAT(
            SUBSTRING(REPLACE(prenom, ' ', ''),  -- First name (no spaces)
            '.',                                  -- Dot separator
            REPLACE(nom, ' ', '')                 -- Last name (no spaces)
        )
    ))) STORED UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE DEFAULT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT 'profile_photo.svg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active','inactive') DEFAULT 'active'
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