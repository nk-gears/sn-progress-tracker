-- Setup production branches for meditation tracker
-- Run this SQL script on your production database

-- First, check if branches table exists and has the right structure
-- CREATE TABLE IF NOT EXISTS medt_branches (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     name VARCHAR(255) NOT NULL,
--     location VARCHAR(255),
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- Insert basic branches for your NGO
INSERT IGNORE INTO medt_branches (name, location) VALUES
('Chennai Central Branch', 'Chennai, Tamil Nadu'),
('Coimbatore Branch', 'Coimbatore, Tamil Nadu'),
('Madurai Branch', 'Madurai, Tamil Nadu'),
('Salem Branch', 'Salem, Tamil Nadu'),
('Trichy Branch', 'Tiruchirappalli, Tamil Nadu'),
('Vellore Branch', 'Vellore, Tamil Nadu'),
('Thanjavur Branch', 'Thanjavur, Tamil Nadu'),
('Kanchipuram Branch', 'Kanchipuram, Tamil Nadu');

-- Show the inserted branches
SELECT id, name, location FROM medt_branches ORDER BY name;