<?php
/**
 * Migration Script: Add is_active column to medt_center_addresses
 *
 * This script adds an is_active column to track which centres are active
 * Default value is 'y' (yes) for existing records
 *
 * Usage: Run this script once to add the column to the database
 * php migration-add-is-active.php
 */

require_once 'config.php';

try {
    // Check if column already exists
    $result = $mysqli->query("SHOW COLUMNS FROM medt_center_addresses LIKE 'is_active'");

    if ($result && $result->num_rows > 0) {
        echo "✓ Column 'is_active' already exists in medt_center_addresses table\n";
        exit(0);
    }

    // Add is_active column with default value 'y'
    $sql = "ALTER TABLE medt_center_addresses ADD COLUMN is_active CHAR(1) DEFAULT 'y' AFTER verified";

    if ($mysqli->query($sql)) {
        echo "✓ Successfully added 'is_active' column to medt_center_addresses table\n";
        echo "  Default value set to 'y' (yes) for all existing centres\n";

        // Add index for better query performance
        $indexSql = "ALTER TABLE medt_center_addresses ADD INDEX idx_is_active (is_active)";
        if ($mysqli->query($indexSql)) {
            echo "✓ Successfully added index on 'is_active' column\n";
        } else {
            echo "⚠ Warning: Could not add index - " . $mysqli->error . "\n";
        }
    } else {
        echo "✗ Error: Failed to add 'is_active' column\n";
        echo "  MySQL Error: " . $mysqli->error . "\n";
        exit(1);
    }

    echo "\n✓ Migration completed successfully!\n";
    exit(0);

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
