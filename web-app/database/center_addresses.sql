-- Schema for the center_addresses table, designed to be flexible for CSV import.

CREATE TABLE IF NOT EXISTS center_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state VARCHAR(255),
    district VARCHAR(255),
    locality VARCHAR(255),
    address TEXT,
    contact_no VARCHAR(255),
    address_contact_verified VARCHAR(255),
    latitude_longitude VARCHAR(255), -- Combined to handle 'TBC' and comma-separated values
    lat_long_verified VARCHAR(255),
    url TEXT,
    verified VARCHAR(255)
);

  ALTER TABLE center_addresses ADD COLUMN last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE         
  CURRENT_TIMESTAMP;  