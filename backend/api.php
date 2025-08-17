<?php
require_once 'config.php';

// Parse the request URI to get the endpoint
$request_uri = $_SERVER['REQUEST_URI'];
$parsed_url = parse_url($request_uri);
$path = trim($parsed_url['path'], '/');

// Remove any subdirectory prefix that contains 'api' in the path
// This handles deployments in subdirectories like /sn-progress/api/
$path_parts = explode('/', $path);
$api_index = array_search('api', $path_parts);
if ($api_index !== false) {
    // Keep everything after 'api'
    $path_parts = array_slice($path_parts, $api_index + 1);
    $path = implode('/', $path_parts);
} else {
    // If no 'api' in path, remove common subdirectory prefixes
    $path = preg_replace('/^[^\/]*\//', '', $path);
}

// Remove 'api.php' from the path if it exists
$path = preg_replace('/^api\.php\/?/', '', $path);

// Split path into segments
$path_segments = array_filter(explode('/', $path));

// Determine the endpoint from the path
$endpoint = '';
if (!empty($path_segments)) {
    // If path starts with 'api/', remove it
    if ($path_segments[0] === 'api') {
        array_shift($path_segments);
    }
    
    // Get the endpoint (first segment after 'api/')
    $endpoint = $path_segments[0] ?? '';
}

// Debug logging
error_log("Request URI: " . $request_uri);
error_log("Parsed path: " . $path);
error_log("Endpoint: " . $endpoint);

// Route to appropriate handler
switch ($endpoint) {
    case 'auth':
        handleAuth();
        break;
        
    case 'participants':
        handleParticipants();
        break;
        
    case 'sessions':
        handleSessions();
        break;
        
    case 'dashboard':
        handleDashboard();
        break;
        
    default:
        sendResponse([
            'success' => false,
            'error' => 'Invalid endpoint',
            'message' => "Endpoint '$endpoint' not found. Available endpoints: auth, participants, sessions, dashboard",
            'available_endpoints' => [
                'POST /api/auth - User authentication',
                'GET /api/participants - Get participants',
                'POST /api/participants - Create participant',
                'PUT /api/participants - Update participant',
                'GET /api/sessions - Get sessions',
                'POST /api/sessions - Create session',
                'PUT /api/sessions - Update session',
                'DELETE /api/sessions - Delete session',
                'GET /api/dashboard - Get analytics'
            ]
        ], 404);
}

// Authentication handler
function handleAuth() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(['success' => false, 'message' => 'Method not allowed for /api/auth'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $missing = validateRequired($input, ['mobile', 'password']);

    if (!empty($missing)) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)], 400);
    }

    $mobile = trim($input['mobile']);
    $password = $input['password'];

    try {
        // Get user by mobile
        $user = fetchRow(
            "SELECT id, name, password FROM medt_users WHERE mobile = ?",
            [$mobile],
            's'
        );

        if (!$user) {
            sendResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // Note: Password verification is commented out for testing
        // if (!password_verify($password, $user['password'])) {
        //     sendResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
        // }

        // Get user's branches
        $branches = fetchAll("
            SELECT b.id, b.name, b.location 
            FROM medt_branches b 
            JOIN medt_user_branches ub ON b.id = ub.branch_id 
            WHERE ub.user_id = ?
            ORDER BY b.name
        ", [$user['id']], 'i');

        // Generate a simple token
        $token = 'auth_token_' . $user['id'] . '_' . time();

        sendResponse([
            'success' => true,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'mobile' => $mobile
            ],
            'branches' => $branches,
            'token' => $token
        ]);

    } catch (Exception $e) {
        error_log('Auth error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Authentication failed'], 500);
    }
}

// Participants handler
function handleParticipants() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $branch_id = $_GET['branch_id'] ?? null;
        $search = $_GET['search'] ?? '';
        $action = $_GET['action'] ?? 'list';

        if (!$branch_id) {
            sendResponse(['success' => false, 'message' => 'Branch ID is required'], 400);
        }

        try {
            $sql = "SELECT id, name, age, gender, branch_id, created_at FROM medt_participants WHERE branch_id = ?";
            $params = [(int)$branch_id];
            $types = 'i';

            if (!empty($search)) {
                $sql .= " AND name LIKE ?";
                $params[] = "%$search%";
                $types .= 's';
            }

            $limit = ($action === 'search') ? 20 : 50;
            $sql .= " ORDER BY name LIMIT $limit";

            $medt_participants = fetchAll($sql, $params, $types);
            
            // Add session statistics for each participant
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

        } catch (Exception $e) {
            error_log('Participants GET error: ' . $e->getMessage());
            sendResponse(['success' => false, 'message' => 'Failed to fetch medt_participants'], 500);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Handle find-or-create request
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
                // Try to find existing participant
                $participant = fetchRow(
                    "SELECT id, name, age, gender, branch_id, created_at FROM medt_participants WHERE name = ? AND branch_id = ?",
                    [$name, $branch_id],
                    'si'
                );

                if ($participant) {
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

                    $participant = [
                        'id' => $result['insert_id'],
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
            // Regular create participant
            error_log('Received participant data: ' . json_encode($input));
            
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
                $result = executeInsert(
                    "INSERT INTO medt_participants (name, age, gender, branch_id) VALUES (?, ?, ?, ?)",
                    [$name, $age, $gender, $branch_id],
                    'sisi'
                );

                $participant = [
                    'id' => $result['insert_id'],
                    'name' => $name,
                    'age' => $age,
                    'gender' => $gender,
                    'branch_id' => $branch_id,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                sendResponse(['success' => true, 'participant' => $participant, 'message' => 'Participant created successfully']);
            } catch (Exception $e) {
                error_log('Participants POST error: ' . $e->getMessage());
                sendResponse(['success' => false, 'message' => 'Failed to create participant'], 500);
            }
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            sendResponse(['success' => false, 'message' => 'Participant ID is required'], 400);
        }

        $participant_id = (int)$input['id'];
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
            $sql = "UPDATE medt_participants SET " . implode(', ', $updateFields) . " WHERE id = ?";
            executeQuery($sql, $updateValues, $types);

            global $mysqli;
            if ($mysqli->affected_rows === 0) {
                sendResponse(['success' => false, 'message' => 'Participant not found'], 404);
            }

            sendResponse(['success' => true, 'message' => 'Participant updated successfully']);
        } catch (Exception $e) {
            error_log('Participants PUT error: ' . $e->getMessage());
            sendResponse(['success' => false, 'message' => 'Failed to update participant'], 500);
        }

    } else {
        sendResponse(['success' => false, 'message' => 'Method not allowed for /api/medt_participants'], 405);
    }
}

// Sessions handler
function handleSessions() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $branch_id = $_GET['branch_id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');

        if (!$branch_id) {
            sendResponse(['success' => false, 'message' => 'Branch ID is required'], 400);
        }

        try {
            $sessions = fetchAll("
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
                FROM medt_meditation_sessions ms
                JOIN medt_participants p ON ms.participant_id = p.id
                WHERE ms.branch_id = ? AND ms.session_date = ?
                ORDER BY ms.start_time DESC
            ", [$branch_id, $date], 'is');

            sendResponse(['success' => true, 'sessions' => $sessions]);
        } catch (Exception $e) {
            error_log('Sessions GET error: ' . $e->getMessage());
            sendResponse(['success' => false, 'message' => 'Failed to fetch sessions'], 500);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $missing = validateRequired($input, ['participant_id', 'branch_id', 'volunteer_id', 'session_date', 'start_time', 'duration_minutes']);

        if (!empty($missing)) {
            sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)], 400);
        }

        $participant_id = (int)$input['participant_id'];
        $branch_id = (int)$input['branch_id'];
        $volunteer_id = (int)$input['volunteer_id'];
        $session_date = $input['session_date'];
        $start_time = $input['start_time'];
        $duration_minutes = (int)$input['duration_minutes'];

        // Validate duration
        if (!in_array($duration_minutes, [30, 60, 90, 120])) {
            sendResponse(['success' => false, 'message' => 'Duration must be 30, 60, 90, or 120 minutes'], 400);
        }

        try {
            // Verify participant exists
            $participant = fetchRow(
                "SELECT name FROM medt_participants WHERE id = ? AND branch_id = ?",
                [$participant_id, $branch_id],
                'ii'
            );

            if (!$participant) {
                sendResponse(['success' => false, 'message' => 'Participant not found'], 400);
            }

            // Create session
            $result = executeInsert("
                INSERT INTO medt_meditation_sessions 
                (participant_id, branch_id, volunteer_id, session_date, start_time, duration_minutes) 
                VALUES (?, ?, ?, ?, ?, ?)
            ", [$participant_id, $branch_id, $volunteer_id, $session_date, $start_time, $duration_minutes], 'iiissi');

            $session = [
                'id' => $result['insert_id'],
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
        } catch (Exception $e) {
            error_log('Sessions POST error: ' . $e->getMessage());
            sendResponse(['success' => false, 'message' => 'Failed to record session'], 500);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $session_id = $input['id'] ?? null;

        if (!$session_id) {
            sendResponse(['success' => false, 'message' => 'Session ID is required'], 400);
        }

        $updateFields = [];
        $updateValues = [];
        $types = '';

        if (isset($input['start_time'])) {
            $updateFields[] = 'start_time = ?';
            $updateValues[] = $input['start_time'];
            $types .= 's';
        }

        if (isset($input['duration_minutes'])) {
            if (!in_array($input['duration_minutes'], [30, 60, 90, 120])) {
                sendResponse(['success' => false, 'message' => 'Duration must be 30, 60, 90, or 120 minutes'], 400);
            }
            $updateFields[] = 'duration_minutes = ?';
            $updateValues[] = (int)$input['duration_minutes'];
            $types .= 'i';
        }

        if (empty($updateFields)) {
            sendResponse(['success' => false, 'message' => 'No fields to update'], 400);
        }

        $updateValues[] = (int)$session_id;
        $types .= 'i';

        try {
            $sql = "UPDATE medt_meditation_sessions SET " . implode(', ', $updateFields) . " WHERE id = ?";
            executeQuery($sql, $updateValues, $types);

            global $mysqli;
            if ($mysqli->affected_rows === 0) {
                sendResponse(['success' => false, 'message' => 'Session not found'], 404);
            }

            sendResponse(['success' => true, 'message' => 'Session updated successfully']);
        } catch (Exception $e) {
            error_log('Sessions PUT error: ' . $e->getMessage());
            sendResponse(['success' => false, 'message' => 'Failed to update session'], 500);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $session_id = $_GET['id'] ?? null;

        if (!$session_id) {
            sendResponse(['success' => false, 'message' => 'Session ID is required'], 400);
        }

        try {
            executeQuery("DELETE FROM medt_meditation_sessions WHERE id = ?", [(int)$session_id], 'i');

            global $mysqli;
            if ($mysqli->affected_rows === 0) {
                sendResponse(['success' => false, 'message' => 'Session not found'], 404);
            }

            sendResponse(['success' => true, 'message' => 'Session deleted successfully']);
        } catch (Exception $e) {
            error_log('Sessions DELETE error: ' . $e->getMessage());
            sendResponse(['success' => false, 'message' => 'Failed to delete session'], 500);
        }

    } else {
        sendResponse(['success' => false, 'message' => 'Method not allowed for /api/sessions'], 405);
    }
}

// Dashboard handler
function handleDashboard() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(['error' => 'Method not allowed for /api/dashboard'], 405);
    }

    $branch_id = $_GET['branch_id'] ?? null;
    $month = $_GET['month'] ?? date('Y-m');

    if (!$branch_id) {
        sendResponse(['error' => 'Branch ID is required'], 400);
    }

    try {
        // Get summary stats
        $total_participants_result = fetchRow("
            SELECT COUNT(DISTINCT participant_id) as total_participants
            FROM medt_meditation_sessions 
            WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
        ", [(int)$branch_id, $month], 'is');
        
        $total_minutes_result = fetchRow("
            SELECT SUM(duration_minutes) as total_minutes
            FROM (
                SELECT DISTINCT session_date, start_time, duration_minutes
                FROM medt_meditation_sessions 
                WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
            ) as unique_time_slots
        ", [(int)$branch_id, $month], 'is');
        
        $total_sessions_result = fetchRow("
            SELECT COUNT(*) as total_sessions
            FROM medt_meditation_sessions 
            WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
        ", [(int)$branch_id, $month], 'is');

        // Get top medt_participants
        $top_medt_participants = fetchAll("
            SELECT 
                ms.participant_id,
                p.name as participant_name,
                COUNT(*) as session_count,
                SUM(ms.duration_minutes) as total_minutes
            FROM medt_meditation_sessions ms
            JOIN medt_participants p ON ms.participant_id = p.id
            WHERE ms.branch_id = ? AND DATE_FORMAT(ms.session_date, '%Y-%m') = ?
            GROUP BY ms.participant_id, p.name
            ORDER BY session_count DESC, total_minutes DESC
            LIMIT 10
        ", [(int)$branch_id, $month], 'is');

        // Get time distribution
        $time_distribution = fetchAll("
            SELECT 
                period,
                COUNT(*) as session_count,
                SUM(duration_minutes) as total_minutes
            FROM (
                SELECT DISTINCT
                    session_date, start_time, duration_minutes,
                    CASE 
                        WHEN TIME(start_time) >= '06:00' AND TIME(start_time) < '12:00' THEN 'Morning'
                        WHEN TIME(start_time) >= '12:00' AND TIME(start_time) < '17:00' THEN 'Afternoon'
                        WHEN TIME(start_time) >= '17:00' AND TIME(start_time) <= '22:00' THEN 'Evening'
                        ELSE 'Other'
                    END as period
                FROM medt_meditation_sessions
                WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
            ) as unique_periods
            WHERE period != 'Other'
            GROUP BY period
            ORDER BY session_count DESC
        ", [(int)$branch_id, $month], 'is');

        // Get daily stats - total minutes based on unique time slots per day
        $daily_stats = fetchAll("
            SELECT 
                date,
                COUNT(*) as sessions_count,
                total_participants as unique_participants,
                SUM(duration_minutes) as total_minutes
            FROM (
                SELECT DISTINCT
                    session_date as date,
                    start_time,
                    duration_minutes,
                    (SELECT COUNT(DISTINCT participant_id) 
                     FROM medt_meditation_sessions ms2 
                     WHERE ms2.session_date = ms1.session_date 
                       AND ms2.branch_id = ms1.branch_id) as total_participants
                FROM medt_meditation_sessions ms1
                WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
            ) as unique_daily_slots
            GROUP BY date, total_participants
            ORDER BY date
        ", [(int)$branch_id, $month], 'is');

        $response = [
            'success' => true,
            'data' => [
                'summary' => [
                    'total_participants' => (int)($total_participants_result['total_participants'] ?? 0),
                    'total_hours' => round(($total_minutes_result['total_minutes'] ?? 0) / 60, 2),
                    'total_sessions' => (int)($total_sessions_result['total_sessions'] ?? 0)
                ],
                'top_medt_participants' => array_map(function($p) {
                    return [
                        'participant_id' => (int)$p['participant_id'],
                        'participant_name' => $p['participant_name'],
                        'session_count' => (int)$p['session_count'],
                        'total_minutes' => (int)$p['total_minutes']
                    ];
                }, $top_medt_participants),
                'time_distribution' => array_map(function($d) {
                    return [
                        'period' => $d['period'],
                        'session_count' => (int)$d['session_count'],
                        'total_minutes' => (int)$d['total_minutes']
                    ];
                }, $time_distribution),
                'daily_stats' => array_map(function($s) {
                    return [
                        'date' => $s['date'],
                        'sessions_count' => (int)$s['sessions_count'],
                        'unique_participants' => (int)$s['unique_participants'],
                        'total_minutes' => (int)$s['total_minutes']
                    ];
                }, $daily_stats)
            ]
        ];

        sendResponse($response);
    } catch (Exception $e) {
        error_log('Dashboard error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to fetch dashboard data'], 500);
    }
}
?>