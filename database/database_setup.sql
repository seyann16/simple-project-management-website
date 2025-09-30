CREATE DATABASE project_manager;
USE project_manager;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- FIXED: PRIMARY KEY (not PRIMARY_KEY)
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- FIXED: Removed extra comma
);

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- FIXED: PRIMARY KEY (not PRIMARY_KEY)
    user_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Comma stays here (not last column)
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- FIXED: PRIMARY KEY (not PRIMARY_KEY)
    project_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in progress', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Comma stays here
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);