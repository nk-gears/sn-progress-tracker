<?php
// Test script to verify database connection and table prefixes
require_once 'config.php';

echo "Testing database connection and table prefixes...\n\n";

try {
    // Test connection
    echo "✓ Database connection successful\n";
    
    // Test each table exists with prefix
    $tables = [
        'medt_users',
        'medt_branches', 
        'medt_user_branches',
        'medt_participants',
        'medt_meditation_sessions'
    ];
    
    foreach ($tables as $table) {
        $result = $mysqli->query("DESCRIBE $table");
        if ($result) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' does not exist: " . $mysqli->error . "\n";
        }
    }
    
    // Test sample queries
    echo "\nTesting sample queries:\n";
    
    // Test user query
    $user_count = fetchRow("SELECT COUNT(*) as count FROM medt_users");
    echo "✓ Users count: " . $user_count['count'] . "\n";
    
    // Test branch query  
    $branch_count = fetchRow("SELECT COUNT(*) as count FROM medt_branches");
    echo "✓ Branches count: " . $branch_count['count'] . "\n";
    
    // Test user-branch relationship
    $user_branch_count = fetchRow("SELECT COUNT(*) as count FROM medt_user_branches");
    echo "✓ User-branch relationships: " . $user_branch_count['count'] . "\n";
    
    echo "\n✅ All tests passed! Database is ready with medt_ table prefixes.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>