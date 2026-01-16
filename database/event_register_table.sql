-- Event Registration Table
-- This table stores event registrations from the public website

USE meditation_tracker;

CREATE TABLE IF NOT EXISTS medt_event_register (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    centre_id VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- FOREIGN KEY (centre_id) REFERENCES medt_branches(id) ON DELETE CASCADE,
    INDEX idx_mobile (mobile),
    -- INDEX idx_centre_date (centre_id, created_at)
);
