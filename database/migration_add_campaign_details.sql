-- Migration: Add campaign details and number of people joining fields
-- Date: 2026-01-24
-- Description: Adds campaign_details column to medt_center_addresses for storing dates/hours and number_of_people to medt_event_register

-- Add campaign_details column to medt_center_addresses
ALTER TABLE medt_center_addresses
ADD COLUMN campaign_details TEXT DEFAULT NULL COMMENT 'Campaign dates and hours (e.g., "February 14–16, 2026    7:00 AM – 12 Noon & 4:00 PM – 8PM")' AFTER verified;

-- Create index for faster queries
ALTER TABLE medt_center_addresses
ADD INDEX idx_campaign_details (campaign_details(100));

-- Add number_of_people column to medt_event_register
ALTER TABLE medt_event_register
ADD COLUMN number_of_people INT DEFAULT 1 COMMENT 'Number of people joining the event' AFTER centre_id;

-- Create index on number_of_people for analytics
ALTER TABLE medt_event_register
ADD INDEX idx_number_of_people (number_of_people);

-- Update last_modified timestamp for centers
UPDATE medt_center_addresses SET last_modified = CURRENT_TIMESTAMP WHERE last_modified IS NULL;
