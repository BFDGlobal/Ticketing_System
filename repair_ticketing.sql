
CREATE DATABASE IF NOT EXISTS repair_ticketing;
USE repair_ticketing;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') NOT NULL
);

CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    department VARCHAR(100) NOT NULL,
    action_taken TEXT NOT NULL,
    status ENUM('Open', 'In Progress', 'Closed') DEFAULT 'Open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_at DATETIME NULL,
    downtime INT DEFAULT NULL,  
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert some sample users for testing
INSERT INTO users (username, password, role) VALUES
('admin', MD5('admin123'), 'admin'),
('IT', MD5('it123'), 'admin2'),
('client1', MD5('client123'), 'client'),
('Emma', MD5('emma123'), 'client');

