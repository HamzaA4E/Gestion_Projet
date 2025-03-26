-- setup.sql - Works alongside database.sql, database_update.sql, and install_groups.sql
USE project_manager;

-- Users Table (compatible with login.php, profile.php, and index.php)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) DEFAULT NULL,
    prenom VARCHAR(100) DEFAULT NULL,
    email VARCHAR(255) UNIQUE DEFAULT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT 'profile_photo.svg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active','inactive') DEFAULT 'active'
);

-- Projects Table (matches create_project.php and edit_project.php)
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

-- Project Members (supports is_admin from your UI)
CREATE TABLE IF NOT EXISTS project_members (
    project_id INT,
    user_id INT,
    is_admin TINYINT(1) DEFAULT 0,
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert test users (password: "password123")
INSERT IGNORE INTO users (username, password, nom, prenom, email, profile_image) VALUES
('anima', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Agrawal', 'Anima', 'anima@example.com', 'profile_photo.svg'),
('jean.dupont', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dupont', 'Jean', 'jean@example.com', 'profile_photo.svg'),
('marie.curie', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Curie', 'Marie', 'marie@example.com', 'profile_photo.svg');