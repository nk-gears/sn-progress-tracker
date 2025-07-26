-- Meditation Time Tracker Database Schema

CREATE DATABASE IF NOT EXISTS meditation_tracker;
USE meditation_tracker;

-- Users table for volunteers
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mobile VARCHAR(15) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Branches table
CREATE TABLE branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User-Branch mapping (many-to-many relationship)
CREATE TABLE user_branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    branch_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_branch (user_id, branch_id)
);

-- Participants table
CREATE TABLE participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    age INT NULL,
    gender ENUM('Male', 'Female', 'Other') NULL,
    branch_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    INDEX idx_name_branch (name, branch_id)
);

-- Meditation sessions table
CREATE TABLE meditation_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    participant_id INT NOT NULL,
    branch_id INT NOT NULL,
    volunteer_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    duration_minutes INT NOT NULL CHECK (duration_minutes IN (30, 60, 90, 120)),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_date_branch (session_date, branch_id),
    INDEX idx_participant_date (participant_id, session_date)
);

-- Insert sample data
INSERT INTO branches (name, location) VALUES
('Chennai Central Branch', 'Chennai, Tamil Nadu'),
('Coimbatore Branch', 'Coimbatore, Tamil Nadu'),
('Madurai Branch', 'Madurai, Tamil Nadu'),
('Salem Branch', 'Salem, Tamil Nadu'),
('Trichy Branch', 'Tiruchirappalli, Tamil Nadu');

-- Insert sample volunteer users (password: 'meditation123' hashed)
INSERT INTO users (mobile, password, name) VALUES
('9283181228', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ramesh Kumar'),
('9876543211', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya Devi'),
('9876543212', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Suresh Babu'),
('9876543213', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lakshmi Raman'),
('9876543214', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Venkat Raja');

-- Assign volunteers to branches
INSERT INTO user_branches (user_id, branch_id) VALUES
(1, 1), (1, 2),  -- Ramesh manages Chennai and Coimbatore
(2, 2),          -- Priya manages Coimbatore
(3, 3),          -- Suresh manages Madurai
(4, 4),          -- Lakshmi manages Salem
(5, 5);          -- Venkat manages Trichy