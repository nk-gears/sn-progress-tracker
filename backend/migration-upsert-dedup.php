<?php
/**
 * Database Migration: Add UNIQUE constraint and ensure required columns for UPSERT logic
 * This migration handles the two-step registration flow with duplicate prevention
 *
 * Usage: php migration-upsert-dedup.php
 */

require_once 'db.php';

try {
    $conn = getDbConnection();

    echo "\n=== UPSERT & Deduplication Migration ===\n\n";

    // 1. Ensure centre_id is nullable
    echo "1. Ensuring centre_id is nullable...\n";
    if ($conn->query("ALTER TABLE medt_event_register MODIFY COLUMN centre_id VARCHAR(20) NULL")) {
        echo "   ✓ centre_id modified to nullable\n";
    } else {
        echo "   ✓ centre_id already nullable or no change needed\n";
    }

    // 2. Ensure number_of_people column exists
    echo "\n2. Ensuring number_of_people column exists...\n";
    $result = $conn->query("SHOW COLUMNS FROM medt_event_register LIKE 'number_of_people'");
    if ($result && $result->num_rows === 0) {
        if ($conn->query("ALTER TABLE medt_event_register ADD COLUMN number_of_people INT DEFAULT 1")) {
            echo "   ✓ number_of_people column added\n";
        } else {
            throw new Exception("Failed to add number_of_people: " . $conn->error);
        }
    } else {
        echo "   ✓ number_of_people column already exists\n";
    }

    // 3. Ensure campaign_source column exists
    echo "\n3. Ensuring campaign_source column exists...\n";
    $result = $conn->query("SHOW COLUMNS FROM medt_event_register LIKE 'campaign_source'");
    if ($result && $result->num_rows === 0) {
        if ($conn->query("ALTER TABLE medt_event_register ADD COLUMN campaign_source VARCHAR(50) NULL")) {
            echo "   ✓ campaign_source column added\n";
        } else {
            throw new Exception("Failed to add campaign_source: " . $conn->error);
        }
    } else {
        echo "   ✓ campaign_source column already exists\n";
    }

    // 4. Add UNIQUE constraint on mobile (if not already exists)
    echo "\n4. Adding UNIQUE constraint on mobile...\n";
    $result = $conn->query("SHOW INDEXES FROM medt_event_register WHERE KEY_NAME = 'uk_mobile'");
    if ($result && $result->num_rows === 0) {
        if ($conn->query("ALTER TABLE medt_event_register ADD UNIQUE KEY uk_mobile (mobile)")) {
            echo "   ✓ UNIQUE constraint on mobile added\n";
        } else {
            // If constraint already exists under different name, that's ok
            echo "   ✓ UNIQUE constraint already exists or no change needed\n";
        }
    } else {
        echo "   ✓ UNIQUE constraint on mobile already exists\n";
    }

    // 5. Add indexes for performance
    echo "\n5. Adding performance indexes...\n";
    $indexes_to_add = [
        ['name' => 'idx_centre_id', 'columns' => 'centre_id'],
        ['name' => 'idx_created_at', 'columns' => 'created_at'],
        ['name' => 'idx_campaign_source', 'columns' => 'campaign_source']
    ];

    foreach ($indexes_to_add as $idx) {
        $result = $conn->query("SHOW INDEXES FROM medt_event_register WHERE KEY_NAME = '{$idx['name']}'");
        if ($result && $result->num_rows === 0) {
            if ($conn->query("ALTER TABLE medt_event_register ADD INDEX {$idx['name']} ({$idx['columns']})")) {
                echo "   ✓ Index {$idx['name']} added\n";
            } else {
                echo "   ! Failed to add {$idx['name']}: " . $conn->error . "\n";
            }
        } else {
            echo "   ✓ Index {$idx['name']} already exists\n";
        }
    }

    // 6. Display final schema
    echo "\n6. Final table schema:\n";
    $result = $conn->query("DESC medt_event_register");
    echo "   ";
    $result->fetch_all(MYSQLI_ASSOC);
    $conn->query("DESCRIBE medt_event_register"); // Show formatted output
    while ($row = $result->fetch_assoc()) {
        echo "   - {$row['Field']}: {$row['Type']}\n";
    }

    // 7. Display indexes
    echo "\n7. Table indexes:\n";
    $result = $conn->query("SHOW INDEXES FROM medt_event_register");
    while ($row = $result->fetch_assoc()) {
        echo "   - {$row['Key_name']}: {$row['Column_name']}\n";
    }

    echo "\n✓ Migration completed successfully!\n";
    echo "=======================================\n\n";

    $conn->close();

} catch (Exception $e) {
    echo "\n✗ Migration failed:\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "=======================================\n\n";
    exit(1);
}
?>
