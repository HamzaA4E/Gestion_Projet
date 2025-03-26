
CREATE DATABASE IF NOT EXISTS gestion_projets;
USE gestion_projets;


CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Backlog', 'In Progress', 'Completed') NOT NULL,
    deadline DATETIME, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE tasks
CHANGE deadline deadline_date DATE,
ADD COLUMN deadline_time TIME AFTER deadline_date;


GRANT ALL PRIVILEGES ON task_manager.* TO 'root'@'localhost';
FLUSH PRIVILEGES;