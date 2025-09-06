<?php
require_once 'config-mysqli.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $branch_id = $_GET['branch_id'] ?? null;
    $search = $_GET['search'] ?? '';
    $action = $_GET['action'] ?? 'list';

    if (!$branch_id) {
        sendResponse(['success' => false, 'message' => 'Branch ID is required'], 400);
    }

    try {
        if ($action === 'search') {
            // Search medt_participants with details for autocomplete
            $sql = "SELECT id, name, age, gender, branch_id, created_at FROM medt_participants WHERE branch_id = ?";
            $params = [(int)$branch_id];
            $types = 'i';

            if (!empty($search)) {
                $sql .= " AND name LIKE ?";
                $params[] = "%$search%";
                $types .= 's';
            }

            $sql .= " ORDER BY name LIMIT 20";

            $medt_participants = fetchAll($sql, $params, $types);
            
            // Add session statistics for each participant in search results
            foreach ($medt_participants as &$participant) {
                $participant_id = $participant['id'];
                
                // Get session count
                $sessionCount = fetchRow(
                    "SELECT COUNT(*) as count FROM medt_meditation_sessions WHERE participant_id = ?",
                    [$participant_id],
                    'i'
                );
                
                // Get total meditation minutes (sum of all session durations)
                $totalMinutes = fetchRow(
                    "SELECT SUM(duration_minutes) as total FROM medt_meditation_sessions WHERE participant_id = ?",
                    [$participant_id],
                    'i'
                );
                
                $participant['session_count'] = (int)($sessionCount['count'] ?? 0);
                $participant['total_hours'] = round(($totalMinutes['total'] ?? 0) / 60, 1);
            }
            
            sendResponse(['success' => true, 'participants' => $medt_participants]);

        } else {
            // Get all medt_participants for the branch
            $sql = "SELECT id, name, age, gender, branch_id, created_at FROM medt_participants WHERE branch_id = ?";
            $params = [(int)$branch_id];
            $types = 'i';

            if (!empty($search)) {
                $sql .= " AND name LIKE ?";
                $params[] = "%$search%";
                $types .= 's';
            }

            $sql .= " ORDER BY name LIMIT 5000";

            $medt_participants = fetchAll($sql, $params, $types);
            
            // Add session statistics for each participant
            foreach ($medt_participants as &$participant) {
                $participant_id = $participant['id'];
                
                // Debug logging
                error_log("Getting stats for participant ID: " . $participant_id);
                
                // Get session count
                $sessionCount = fetchRow(
                    "SELECT COUNT(*) as count FROM medt_meditation_sessions WHERE participant_id = ?",
                    [$participant_id],
                    'i'
                );
                
                // Get total meditation minutes (sum of all session durations)
                $totalMinutes = fetchRow(
                    "SELECT SUM(duration_minutes) as total FROM medt_meditation_sessions WHERE participant_id = ?",
                    [$participant_id],
                    'i'
                );
                
                // Debug logging
                error_log("Participant ID $participant_id: sessions = " . ($sessionCount['count'] ?? 0) . ", total minutes = " . ($totalMinutes['total'] ?? 0));
                
                $participant['session_count'] = (int)($sessionCount['count'] ?? 0);
                $participant['total_hours'] = round(($totalMinutes['total'] ?? 0) / 60, 1);
            }
            
            sendResponse(['success' => true, 'participants' => $medt_participants]);
        }

    } catch (Exception $e) {
        error_log('Participants GET error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to fetch medt_participants'], 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if this is a find-or-create request
    if (isset($input['action']) && $input['action'] === 'findOrCreate') {
        // Validate and sanitize input data
        if (!is_string($input['name'])) {
            sendResponse(['success' => false, 'message' => 'Name must be a string'], 400);
        }
        
        $name = trim($input['name']);
        if (empty($name)) {
            sendResponse(['success' => false, 'message' => 'Name cannot be empty'], 400);
        }
        
        $branch_id = (int)$input['branch_id'];
        if (!$branch_id) {
            sendResponse(['success' => false, 'message' => 'Branch ID is required'], 400);
        }
        
        $age = isset($input['age']) && $input['age'] !== null && $input['age'] !== '' ? (int)$input['age'] : null;
        $gender = isset($input['gender']) && is_string($input['gender']) ? trim($input['gender']) : null;
        
        // Validate gender if provided
        if ($gender && !in_array($gender, ['Male', 'Female', 'Other'])) {
            sendResponse(['success' => false, 'message' => 'Gender must be Male, Female, or Other'], 400);
        }

        try {
            // First, try to find existing participant
            $participant = fetchRow(
                "SELECT id, name, age, gender, branch_id, created_at FROM medt_participants WHERE name = ? AND branch_id = ?",
                [$name, $branch_id],
                'si'
            );

            if ($participant) {
                // Participant exists
                sendResponse([
                    'success' => true,
                    'participant' => $participant,
                    'message' => 'Participant found'
                ]);
            } else {
                // Create new participant
                $result = executeInsert(
                    "INSERT INTO medt_participants (name, age, gender, branch_id) VALUES (?, ?, ?, ?)",
                    [$name, $age, $gender, $branch_id],
                    'sisi'
                );

                $participant_id = $result['insert_id'];

                // Return the created participant
                $participant = [
                    'id' => $participant_id,
                    'name' => $name,
                    'age' => $age,
                    'gender' => $gender,
                    'branch_id' => $branch_id,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                sendResponse([
                    'success' => true,
                    'participant' => $participant,
                    'message' => 'Participant created successfully'
                ]);
            }

        } catch (Exception $e) {
            error_log('Participants findOrCreate error: ' . $e->getMessage());
            sendResponse(['success' => false, 'message' => 'Failed to find or create participant'], 500);
        }
    } else {
        // Regular create participant request
        $missing = validateRequired($input, ['name', 'branch_id']);

        if (!empty($missing)) {
            sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)], 400);
        }

        // Validate and sanitize input data
        if (!is_string($input['name'])) {
            sendResponse(['success' => false, 'message' => 'Name must be a string'], 400);
        }
        
        $name = trim($input['name']);
        if (empty($name)) {
            sendResponse(['success' => false, 'message' => 'Name cannot be empty'], 400);
        }
        
        $branch_id = (int)$input['branch_id'];
        $age = isset($input['age']) && $input['age'] !== null && $input['age'] !== '' ? (int)$input['age'] : null;
        $gender = isset($input['gender']) && is_string($input['gender']) ? trim($input['gender']) : null;

        // Validate gender if provided
        if ($gender && !in_array($gender, ['Male', 'Female', 'Other'])) {
            sendResponse(['success' => false, 'message' => 'Gender must be Male, Female, or Other'], 400);
        }

        try {
            // Check if participant already exists
            $existing = fetchRow(
                "SELECT id FROM medt_participants WHERE name = ? AND branch_id = ?",
                [$name, $branch_id],
                'si'
            );

            if ($existing) {
                sendResponse(['success' => false, 'message' => 'Participant already exists in this branch'], 400);
            }

            // Create new participant
            $result = executeInsert(
                "INSERT INTO medt_participants (name, age, gender, branch_id) VALUES (?, ?, ?, ?)",
                [$name, $age, $gender, $branch_id],
                'sisi'
            );

            $participant_id = $result['insert_id'];

            // Return the created participant
            $participant = [
                'id' => $participant_id,
                'name' => $name,
                'age' => $age,
                'gender' => $gender,
                'branch_id' => $branch_id,
                'created_at' => date('Y-m-d H:i:s')
            ];

            sendResponse([
                'success' => true,
                'participant' => $participant,
                'message' => 'Participant created successfully'
            ]);

        } catch (Exception $e) {
            error_log('Participants POST error: ' . $e->getMessage());
            sendResponse(['success' => false, 'message' => 'Failed to create participant'], 500);
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $missing = validateRequired($input, ['id']);

    if (!empty($missing)) {
        sendResponse(['success' => false, 'message' => 'Participant ID is required'], 400);
    }

    $participant_id = (int)$input['id'];

    // Build UPDATE query dynamically
    $updateFields = [];
    $updateValues = [];
    $types = '';

    if (isset($input['name']) && !empty(trim($input['name']))) {
        $updateFields[] = 'name = ?';
        $updateValues[] = trim($input['name']);
        $types .= 's';
    }

    if (isset($input['age'])) {
        $updateFields[] = 'age = ?';
        $updateValues[] = $input['age'] ? (int)$input['age'] : null;
        $types .= 'i';
    }

    if (isset($input['gender'])) {
        if ($input['gender'] && !in_array($input['gender'], ['Male', 'Female', 'Other'])) {
            sendResponse(['success' => false, 'message' => 'Gender must be Male, Female, or Other'], 400);
        }
        $updateFields[] = 'gender = ?';
        $updateValues[] = $input['gender'] ?: null;
        $types .= 's';
    }

    if (empty($updateFields)) {
        sendResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }

    $updateValues[] = $participant_id;
    $types .= 'i';

    try {
        // Update participant
        $sql = "UPDATE medt_participants SET " . implode(', ', $updateFields) . " WHERE id = ?";
        executeQuery($sql, $updateValues, $types);

        global $mysqli;
        if ($mysqli->affected_rows === 0) {
            sendResponse(['success' => false, 'message' => 'Participant not found or no changes made'], 404);
        }

        sendResponse(['success' => true, 'message' => 'Participant updated successfully']);

    } catch (Exception $e) {
        error_log('Participants PUT error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to update participant'], 500);
    }

} else {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>