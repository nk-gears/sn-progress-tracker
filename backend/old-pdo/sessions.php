<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $branch_id = $_GET['branch_id'] ?? null;
    $date = $_GET['date'] ?? date('Y-m-d');

    if (!$branch_id) {
        sendResponse(['success' => false, 'message' => 'Branch ID is required'], 400);
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                ms.id,
                ms.participant_id,
                p.name as participant_name,
                ms.branch_id,
                ms.volunteer_id,
                ms.session_date,
                ms.start_time,
                ms.duration_minutes,
                ms.created_at,
                ms.updated_at
            FROM meditation_sessions ms
            JOIN participants p ON ms.participant_id = p.id
            WHERE ms.branch_id = ? AND ms.session_date = ?
            ORDER BY ms.start_time DESC
        ");
        $stmt->execute([$branch_id, $date]);
        $sessions = $stmt->fetchAll();

        sendResponse(['success' => true, 'sessions' => $sessions]);

    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to fetch sessions'], 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $missing = validateRequired($input, ['participant_id', 'branch_id', 'volunteer_id', 'session_date', 'start_time', 'duration_minutes']);

    if (!empty($missing)) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)], 400);
    }

    $participant_id = $input['participant_id'];
    $branch_id = $input['branch_id'];
    $volunteer_id = $input['volunteer_id'];
    $session_date = $input['session_date'];
    $start_time = $input['start_time'];
    $duration_minutes = $input['duration_minutes'];

    // Validate duration
    if (!in_array($duration_minutes, [30, 60, 90, 120])) {
        sendResponse(['success' => false, 'message' => 'Duration must be 30, 60, 90, or 120 minutes'], 400);
    }

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $session_date)) {
        sendResponse(['success' => false, 'message' => 'Invalid date format'], 400);
    }

    // Validate time format
    if (!preg_match('/^\d{2}:\d{2}$/', $start_time)) {
        sendResponse(['success' => false, 'message' => 'Invalid time format'], 400);
    }

    try {
        // Verify participant exists
        $stmt = $pdo->prepare("SELECT name FROM participants WHERE id = ? AND branch_id = ?");
        $stmt->execute([$participant_id, $branch_id]);
        $participant = $stmt->fetch();

        if (!$participant) {
            sendResponse(['success' => false, 'message' => 'Participant not found'], 400);
        }

        // Create meditation session
        $stmt = $pdo->prepare("
            INSERT INTO meditation_sessions 
            (participant_id, branch_id, volunteer_id, session_date, start_time, duration_minutes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$participant_id, $branch_id, $volunteer_id, $session_date, $start_time, $duration_minutes]);
        
        $session_id = $pdo->lastInsertId();

        // Return the created session with participant name
        $session = [
            'id' => $session_id,
            'participant_id' => $participant_id,
            'participant_name' => $participant['name'],
            'branch_id' => $branch_id,
            'volunteer_id' => $volunteer_id,
            'session_date' => $session_date,
            'start_time' => $start_time,
            'duration_minutes' => $duration_minutes,
            'created_at' => date('Y-m-d H:i:s')
        ];

        sendResponse(['success' => true, 'session' => $session, 'message' => 'Session recorded successfully']);

    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to record session'], 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Extract session ID from URL path or input
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    $session_id = end($pathParts);
    
    // If ID not in URL, check input
    if (!is_numeric($session_id)) {
        $session_id = $input['id'] ?? null;
    }

    if (!$session_id) {
        sendResponse(['success' => false, 'message' => 'Session ID is required'], 400);
    }

    // Extract fields to update
    $updateFields = [];
    $updateValues = [];

    if (isset($input['participant_id'])) {
        $updateFields[] = 'participant_id = ?';
        $updateValues[] = $input['participant_id'];
    }

    if (isset($input['session_date'])) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['session_date'])) {
            sendResponse(['success' => false, 'message' => 'Invalid date format'], 400);
        }
        $updateFields[] = 'session_date = ?';
        $updateValues[] = $input['session_date'];
    }

    if (isset($input['start_time'])) {
        if (!preg_match('/^\d{2}:\d{2}$/', $input['start_time'])) {
            sendResponse(['success' => false, 'message' => 'Invalid time format'], 400);
        }
        $updateFields[] = 'start_time = ?';
        $updateValues[] = $input['start_time'];
    }

    if (isset($input['duration_minutes'])) {
        if (!in_array($input['duration_minutes'], [30, 60, 90, 120])) {
            sendResponse(['success' => false, 'message' => 'Duration must be 30, 60, 90, or 120 minutes'], 400);
        }
        $updateFields[] = 'duration_minutes = ?';
        $updateValues[] = $input['duration_minutes'];
    }

    if (empty($updateFields)) {
        sendResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }

    $updateValues[] = $session_id;

    try {
        // Update session
        $sql = "UPDATE meditation_sessions SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateValues);

        if ($stmt->rowCount() === 0) {
            sendResponse(['success' => false, 'message' => 'Session not found or no changes made'], 404);
        }

        sendResponse(['success' => true, 'message' => 'Session updated successfully']);

    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to update session'], 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Extract session ID from URL path or input
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    $session_id = end($pathParts);
    
    // If ID not in URL, check query params
    if (!is_numeric($session_id)) {
        $session_id = $_GET['id'] ?? null;
    }

    if (!$session_id) {
        sendResponse(['success' => false, 'message' => 'Session ID is required'], 400);
    }

    try {
        // Delete session
        $stmt = $pdo->prepare("DELETE FROM meditation_sessions WHERE id = ?");
        $stmt->execute([$session_id]);

        if ($stmt->rowCount() === 0) {
            sendResponse(['success' => false, 'message' => 'Session not found'], 404);
        }

        sendResponse(['success' => true, 'message' => 'Session deleted successfully']);

    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to delete session'], 500);
    }

} else {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>