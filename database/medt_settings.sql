-- Settings table for storing configurable values
CREATE TABLE IF NOT EXISTS medt_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(255) NOT NULL UNIQUE,
    key_value TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    description VARCHAR(500),
    INDEX idx_key_name (key_name),
    INDEX idx_is_active (is_active)
);

-- Insert default WhatsApp link
INSERT INTO medt_settings (key_name, key_value, is_active, description)
VALUES ('whatsapp-link', 'https://chat.whatsapp.com/CmYtLn6dJU4IzsE4EOtAv0', 1, 'WhatsApp group link for joining updates')
ON DUPLICATE KEY UPDATE key_value = VALUES(key_value), is_active = VALUES(is_active);
