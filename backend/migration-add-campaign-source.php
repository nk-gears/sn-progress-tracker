<?php
/**
 * Database Migration: Add campaign_source column to event_registrations table
 *
 * This migration adds support for tracking which campaign (e.g., Facebook, Instagram, etc.)
 * a user came from when registering for an event.
 *
 * Usage: php migration-add-campaign-source.php
 */

require_once 'db.php';

try {
    $conn = getDbConnection();

    echo "\n=== Campaign Source Migration ===\n\n";

    // Check if column already exists
    $result = $conn->query("SHOW COLUMNS FROM event_registrations LIKE 'campaign_source'");

    if ($result && $result->num_rows == 0) {
        echo "✓ Column does not exist, creating...\n";

        $sql = "ALTER TABLE event_registrations
                ADD COLUMN campaign_source VARCHAR(50) NULL
                AFTER number_of_people";

        if ($conn->query($sql)) {
            echo "✓ Successfully added campaign_source column\n";
            echo "  - Type: VARCHAR(50), allows NULL values\n";
            echo "  - Position: After number_of_people column\n\n";
        } else {
            throw new Exception("Failed to add column: " . $conn->error);
        }
    } else if ($result && $result->num_rows > 0) {
        echo "✓ campaign_source column already exists\n";
        echo "  No action needed.\n\n";
    } else {
        throw new Exception("Failed to check if column exists: " . $conn->error);
    }

    // Verify the column was created
    $result = $conn->query("DESCRIBE event_registrations");
    $columnFound = false;

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === 'campaign_source') {
                $columnFound = true;
                echo "✓ Verification successful:\n";
                echo "  - Field: " . $row['Field'] . "\n";
                echo "  - Type: " . $row['Type'] . "\n";
                echo "  - Null: " . $row['Null'] . "\n\n";
                break;
            }
        }
    }

    if (!$columnFound) {
        throw new Exception("Verification failed: campaign_source column not found after creation");
    }

    echo "✓ Migration completed successfully!\n";
    echo "=================================\n\n";

    $conn->close();

} catch (Exception $e) {
    echo "\n✗ Migration failed:\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "=================================\n\n";
    exit(1);
}
?>
