<?php
require_once 'config.php';

echo "=== DEBUG: Session Data ===\n\n";

try {
    // Check all sessions
    echo "All sessions in database:\n";
    $allSessions = fetchAll("SELECT * FROM medt_meditation_sessions ORDER BY created_at DESC LIMIT 10");
    foreach ($allSessions as $session) {
        echo "ID: {$session['id']}, Participant: {$session['participant_id']}, Duration: {$session['duration_minutes']}min, Date: {$session['session_date']}\n";
    }
    
    echo "\n";
    
    // Check all participants
    echo "All participants in database:\n";
    $allParticipants = fetchAll("SELECT * FROM medt_participants ORDER BY created_at DESC LIMIT 10");
    foreach ($allParticipants as $participant) {
        echo "ID: {$participant['id']}, Name: {$participant['name']}, Branch: {$participant['branch_id']}\n";
    }
    
    echo "\n";
    
    // Check sessions for each participant
    echo "Session counts per participant:\n";
    $stats = fetchAll("
        SELECT 
            p.id,
            p.name,
            COUNT(s.id) as session_count,
            SUM(s.duration_minutes) as total_minutes
        FROM medt_participants p 
        LEFT JOIN medt_meditation_sessions s ON p.id = s.participant_id 
        GROUP BY p.id, p.name
        ORDER BY p.name
    ");
    
    foreach ($stats as $stat) {
        echo "Participant: {$stat['name']} (ID: {$stat['id']}) - {$stat['session_count']} sessions, {$stat['total_minutes']} total minutes\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>