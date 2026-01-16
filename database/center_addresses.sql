-- Schema for the medt_center_addresses table, designed to be flexible for CSV import.

CREATE TABLE IF NOT EXISTS medt_center_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    center_code VARCHAR(255),
    state VARCHAR(255),
    district VARCHAR(255),
    locality VARCHAR(255),
    address TEXT,
    contact_no VARCHAR(255),
    address_contact_verified VARCHAR(255),
    latitude_longitude VARCHAR(255),
    lat_long_verified VARCHAR(255),
    url VARCHAR(255),
    verified VARCHAR(255),
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
