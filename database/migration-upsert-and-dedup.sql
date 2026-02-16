-- Migration: Add UNIQUE constraint and ensure required columns for UPSERT logic
-- This migration handles the two-step registration flow with duplicate prevention

USE meditation_tracker;

-- 1. Ensure centre_id is nullable (if not already)
ALTER TABLE medt_event_register MODIFY COLUMN centre_id VARCHAR(20) NULL;

-- 2. Ensure number_of_people column exists
ALTER TABLE medt_event_register ADD COLUMN IF NOT EXISTS number_of_people INT DEFAULT 1;

-- 3. Ensure campaign_source column exists
ALTER TABLE medt_event_register ADD COLUMN IF NOT EXISTS campaign_source VARCHAR(50) NULL;

-- 4. Add UNIQUE constraint on mobile to enable ON DUPLICATE KEY UPDATE
-- First, check if there are duplicates and keep the most recent one
ALTER TABLE medt_event_register
ADD UNIQUE KEY uk_mobile (mobile);

-- 5. Update indexes for better query performance
ALTER TABLE medt_event_register ADD INDEX IF NOT EXISTS idx_centre_id (centre_id);
ALTER TABLE medt_event_register ADD INDEX IF NOT EXISTS idx_created_at (created_at);
ALTER TABLE medt_event_register ADD INDEX IF NOT EXISTS idx_campaign_source (campaign_source);

-- Display schema for verification
DESC medt_event_register;

-- Show indexes
SHOW INDEXES FROM medt_event_register;
