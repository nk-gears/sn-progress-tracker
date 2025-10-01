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
    case 'members':
        handleParticipants();
        break;
        
    case 'sessions':
        handleSessions();
        break;
        
    case 'dashboard':
    case 'stats':
        handleDashboard();
        break;
        
    case 'profile':
        handleProfile();
        break;
        
    case 'onboard':
        handleOnboard();
        break;
        
    case 'bulk-users':
        handleBulkUsers();
        break;
        
    case 'admin-report':
        handleAdminReport();
        break;
        
    case 'branches':
        handleBranches();
        break;
        
    default:
        sendResponse([
            'success' => false,
            'error' => 'Invalid endpoint',
            'message' => "Endpoint '$endpoint' not found. Available endpoints: auth, participants, sessions, dashboard, profile, onboard, bulk-users, admin-report, branches",
            'available_endpoints' => [
                'POST /api/auth - User authentication',
                'GET /api/participants - Get participants',
                'POST /api/participants - Create participant',
                'PUT /api/participants - Update participant',
                'GET /api/sessions - Get sessions',
                'POST /api/sessions - Create session',
                'PUT /api/sessions - Update session',
                'DELETE /api/sessions - Delete session',
                'GET /api/dashboard - Get analytics',
                'PUT /api/profile/phone - Update phone number',
                'PUT /api/profile/password - Update password',
                'POST /api/onboard - Onboard new user with branch mapping',
                'POST /api/bulk-users - Bulk create users with branch mapping',
                'POST /api/admin-report - Generate admin reports',
                'GET /api/branches - Get all branches (public)'
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

        if (!password_verify($password, $user['password'])) {
            sendResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

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
        $participant_id = $_GET['participant_id'] ?? null;

        if (!$branch_id) {
            sendResponse(['success' => false, 'message' => 'Branch ID is required'], 400);
        }

        // Handle last session request for a specific participant
        if ($action === 'last_session' && $participant_id) {
            try {
                $lastSession = fetchRow("
                    SELECT start_time, duration_minutes, session_date
                    FROM medt_meditation_sessions 
                    WHERE participant_id = ? AND branch_id = ? 
                    ORDER BY session_date DESC, start_time DESC 
                    LIMIT 1
                ", [(int)$participant_id, (int)$branch_id], 'ii');
                
                sendResponse([
                    'success' => true, 
                    'last_session' => $lastSession
                ]);
                return;
            } catch (Exception $e) {
                error_log('Last session error: ' . $e->getMessage());
                sendResponse(['success' => false, 'message' => 'Failed to fetch last session'], 500);
                return;
            }
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

            $limit = ($action === 'search') ? 50 : 5000;
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
            
            // Validate that name contains only letters and spaces
            if (!preg_match('/^[A-Za-z\s]+$/', $name)) {
                sendResponse(['success' => false, 'message' => 'Name can only contain letters and spaces'], 400);
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
            
            // Validate that name contains only letters and spaces
            if (!preg_match('/^[A-Za-z\s]+$/', $name)) {
                sendResponse(['success' => false, 'message' => 'Name can only contain letters and spaces'], 400);
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
        error_log('PUT participants - received data: ' . json_encode($input));
        
        if (!isset($input['id'])) {
            sendResponse(['success' => false, 'message' => 'Participant ID is required'], 400);
        }

        $participant_id = (int)$input['id'];
        $updateFields = [];
        $updateValues = [];
        $types = '';

        if (isset($input['name']) && !empty(trim($input['name']))) {
            $name = trim($input['name']);
            
            // Validate that name contains only letters and spaces
            if (!preg_match('/^[A-Za-z\s]+$/', $name)) {
                sendResponse(['success' => false, 'message' => 'Name can only contain letters and spaces'], 400);
            }
            
            $updateFields[] = 'name = ?';
            $updateValues[] = $name;
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

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $participant_id = $_GET['id'] ?? null;

        if (!$participant_id) {
            sendResponse(['success' => false, 'message' => 'Participant ID is required'], 400);
        }

        try {
            // First, delete all sessions for this participant
            executeQuery("DELETE FROM medt_meditation_sessions WHERE participant_id = ?", [(int)$participant_id], 'i');

            // Then delete the participant
            executeQuery("DELETE FROM medt_participants WHERE id = ?", [(int)$participant_id], 'i');

            global $mysqli;
            if ($mysqli->affected_rows === 0) {
                sendResponse(['success' => false, 'message' => 'Participant not found'], 404);
            }

            sendResponse(['success' => true, 'message' => 'Participant and all related sessions deleted successfully']);
        } catch (Exception $e) {
            error_log('Participants DELETE error: ' . $e->getMessage());
            sendResponse(['success' => false, 'message' => 'Failed to delete participant'], 500);
        }

    } else {
        sendResponse(['success' => false, 'message' => 'Method not allowed for /api/medt_participants'], 405);
    }
}

// Sessions handler
function handleSessions() {
    global $path_segments;
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $branch_id = $_GET['branch_id'] ?? null;

        if (!$branch_id) {
            sendResponse(['success' => false, 'message' => 'Branch ID is required'], 400);
        }

        try {
            // Check if this is the /sessions/all endpoint
            if (isset($path_segments[1]) && $path_segments[1] === 'all') {
                // Get all sessions for the branch
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
                    WHERE ms.branch_id = ?
                    ORDER BY ms.session_date DESC, ms.start_time DESC
                ", [$branch_id], 'i');
            } else {
                // Get sessions for a specific date (original behavior)
                $date = $_GET['date'] ?? date('Y-m-d');
                
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
            }

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
        if ($duration_minutes < 30 || $duration_minutes > 960 || $duration_minutes % 30 !== 0) {
            sendResponse(['success' => false, 'message' => 'Duration must be between 30 and 960 minutes in 30-minute increments'], 400);
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

            // Check for duplicate session (same participant, date, and start time)
            $existingSession = fetchRow("
                SELECT id FROM medt_meditation_sessions 
                WHERE participant_id = ? AND session_date = ? AND start_time = ?
            ", [$participant_id, $session_date, $start_time], 'iss');

            if ($existingSession) {
                sendResponse([
                    'success' => false, 
                    'message' => "Session already exists for {$participant['name']} on {$session_date} at {$start_time}",
                    'error_type' => 'duplicate_session'
                ], 409); // 409 Conflict
                return;
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
            if ($input['duration_minutes'] < 30 || $input['duration_minutes'] > 960 || $input['duration_minutes'] % 30 !== 0) {
                sendResponse(['success' => false, 'message' => 'Duration must be between 30 and 960 minutes in 30-minute increments'], 400);
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
// Helper function to calculate total minutes accounting for overlapping sessions
function calculateTotalMinutesWithOverlap($sessions) {
    if (empty($sessions)) {
        return 0;
    }

    // Group sessions by date
    $sessionsByDate = [];
    foreach ($sessions as $session) {
        $date = $session['session_date'];
        if (!isset($sessionsByDate[$date])) {
            $sessionsByDate[$date] = [];
        }

        // Convert time to minutes from midnight
        list($hours, $minutes) = explode(':', $session['start_time']);
        $startMinutes = ($hours * 60) + $minutes;
        $endMinutes = $startMinutes + $session['duration_minutes'];

        $sessionsByDate[$date][] = [
            'start' => $startMinutes,
            'end' => $endMinutes
        ];
    }

    $totalMinutes = 0;

    // Process each date separately
    foreach ($sessionsByDate as $date => $daysSessions) {
        // Sort by start time
        usort($daysSessions, function($a, $b) {
            return $a['start'] - $b['start'];
        });

        // Merge overlapping intervals
        $merged = [];
        foreach ($daysSessions as $session) {
            if (empty($merged)) {
                $merged[] = $session;
            } else {
                $last = &$merged[count($merged) - 1];

                // If current session overlaps with or is adjacent to the last merged session
                if ($session['start'] <= $last['end']) {
                    // Extend the end time if needed
                    $last['end'] = max($last['end'], $session['end']);
                } else {
                    // No overlap, add as new interval
                    $merged[] = $session;
                }
            }
        }

        // Sum up the merged intervals for this date
        foreach ($merged as $interval) {
            $totalMinutes += ($interval['end'] - $interval['start']);
        }
    }

    return $totalMinutes;
}

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
        // Get summary stats (all-time totals)
        $total_participants_result = fetchRow("
            SELECT COUNT(DISTINCT participant_id) as total_participants
            FROM medt_meditation_sessions 
            WHERE branch_id = ?
        ", [(int)$branch_id], 'i');
        
        // Calculate total hours by merging overlapping time slots per date
        // This prevents double-counting when sessions overlap
        $sessions_for_hours = fetchAll("
            SELECT DISTINCT session_date, start_time, duration_minutes
            FROM medt_meditation_sessions
            WHERE branch_id = ?
            ORDER BY session_date, start_time
        ", [(int)$branch_id], 'i');

        $total_minutes = calculateTotalMinutesWithOverlap($sessions_for_hours);
        $total_minutes_result = ['total_minutes' => $total_minutes];
        
        $total_sessions_result = fetchRow("
            SELECT COUNT(*) as total_sessions
            FROM medt_meditation_sessions 
            WHERE branch_id = ?
        ", [(int)$branch_id], 'i');

        // Get top medt_participants (all-time)
        $top_medt_participants = fetchAll("
            SELECT 
                ms.participant_id,
                p.name as participant_name,
                COUNT(*) as session_count,
                SUM(ms.duration_minutes) as total_minutes
            FROM medt_meditation_sessions ms
            JOIN medt_participants p ON ms.participant_id = p.id
            WHERE ms.branch_id = ?
            GROUP BY ms.participant_id, p.name
            ORDER BY session_count DESC, total_minutes DESC
            LIMIT 10
        ", [(int)$branch_id], 'i');

        // Get time distribution (all-time)
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
                WHERE branch_id = ?
            ) as unique_periods
            WHERE period != 'Other'
            GROUP BY period
            ORDER BY session_count DESC
        ", [(int)$branch_id], 'i');

        // Get recent daily stats (last 30 days)
        // First get all distinct sessions for the last 30 days
        $daily_sessions = fetchAll("
            SELECT DISTINCT
                session_date,
                start_time,
                duration_minutes
            FROM medt_meditation_sessions
            WHERE branch_id = ? AND session_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY session_date DESC, start_time
        ", [(int)$branch_id], 'i');

        // Group sessions by date and calculate overlap-aware totals
        $sessions_by_date = [];
        foreach ($daily_sessions as $session) {
            $date = $session['session_date'];
            if (!isset($sessions_by_date[$date])) {
                $sessions_by_date[$date] = [];
            }
            $sessions_by_date[$date][] = $session;
        }

        // Calculate stats for each date
        $daily_stats = [];
        foreach ($sessions_by_date as $date => $sessions) {
            // Get unique participants for this date
            $participant_count = fetchRow("
                SELECT COUNT(DISTINCT participant_id) as total_participants
                FROM medt_meditation_sessions
                WHERE branch_id = ? AND session_date = ?
            ", [(int)$branch_id, $date], 'is');

            // Calculate total minutes with overlap handling
            $total_minutes = calculateTotalMinutesWithOverlap($sessions);

            $daily_stats[] = [
                'date' => $date,
                'sessions_count' => count($sessions),
                'unique_participants' => (int)$participant_count['total_participants'],
                'total_minutes' => $total_minutes
            ];
        }

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

// Profile handler
function handleProfile() {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        sendResponse(['success' => false, 'message' => 'Method not allowed for /api/profile'], 405);
    }

    // Parse the path to get the profile action
    $request_uri = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($request_uri);
    $path = trim($parsed_url['path'], '/');
    
    // Remove any subdirectory prefix and api.php
    $path = preg_replace('/^.*\/api\.php\/?/', '', $path);
    $path = preg_replace('/^.*\/api\/?/', '', $path);
    
    $path_segments = array_filter(explode('/', $path));
    
    // Get the action (phone or password)
    $action = '';
    if (count($path_segments) >= 2 && $path_segments[0] === 'profile') {
        $action = $path_segments[1];
    }

    $input = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'phone':
            handlePhoneUpdate($input);
            break;
            
        case 'password':
            handlePasswordUpdate($input);
            break;
            
        default:
            sendResponse([
                'success' => false, 
                'message' => "Invalid profile action. Available actions: phone, password"
            ], 400);
    }
}

// Handle phone number update
function handlePhoneUpdate($input) {
    $missing = validateRequired($input, ['userId', 'newPhone', 'currentPassword']);
    
    if (!empty($missing)) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)], 400);
    }

    $userId = (int)$input['userId'];
    $newPhone = trim($input['newPhone']);
    $currentPassword = $input['currentPassword'];

    // Validate phone number format
    if (!preg_match('/^\d{10}$/', $newPhone)) {
        sendResponse(['success' => false, 'message' => 'Phone number must be 10 digits'], 400);
    }

    try {
        // Get current user data
        $user = fetchRow(
            "SELECT id, name, mobile, password FROM medt_users WHERE id = ?",
            [$userId],
            'i'
        );

        if (!$user) {
            sendResponse(['success' => false, 'message' => 'User not found'], 404);
        }

        if (!password_verify($currentPassword, $user['password'])) {
            sendResponse(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        // Check if new phone number is already in use
        $existingUser = fetchRow(
            "SELECT id FROM medt_users WHERE mobile = ? AND id != ?",
            [$newPhone, $userId],
            'si'
        );

        if ($existingUser) {
            sendResponse(['success' => false, 'message' => 'Phone number already in use'], 400);
        }

        // Update phone number
        executeQuery(
            "UPDATE medt_users SET mobile = ? WHERE id = ?",
            [$newPhone, $userId],
            'si'
        );

        global $mysqli;
        if ($mysqli->affected_rows === 0) {
            sendResponse(['success' => false, 'message' => 'Failed to update phone number'], 500);
        }

        // Return updated user data
        $updatedUser = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'mobile' => $newPhone
        ];

        sendResponse([
            'success' => true,
            'message' => 'Phone number updated successfully',
            'user' => $updatedUser
        ]);

    } catch (Exception $e) {
        error_log('Phone update error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to update phone number'], 500);
    }
}

// Handle password update
function handlePasswordUpdate($input) {
    $missing = validateRequired($input, ['userId', 'currentPassword', 'newPassword']);
    
    if (!empty($missing)) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)], 400);
    }

    $userId = (int)$input['userId'];
    $currentPassword = $input['currentPassword'];
    $newPassword = $input['newPassword'];

    // Validate new password length
    if (strlen($newPassword) < 6) {
        sendResponse(['success' => false, 'message' => 'New password must be at least 6 characters long'], 400);
    }

    try {
        // Get current user data
        $user = fetchRow(
            "SELECT id, password FROM medt_users WHERE id = ?",
            [$userId],
            'i'
        );

        if (!$user) {
            sendResponse(['success' => false, 'message' => 'User not found'], 404);
        }

        if (!password_verify($currentPassword, $user['password'])) {
            sendResponse(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        executeQuery(
            "UPDATE medt_users SET password = ? WHERE id = ?",
            [$hashedPassword, $userId],
            'si'
        );

        global $mysqli;
        if ($mysqli->affected_rows === 0) {
            sendResponse(['success' => false, 'message' => 'Failed to update password'], 500);
        }

        sendResponse([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);

    } catch (Exception $e) {
        error_log('Password update error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to update password'], 500);
    }
}

// User Onboarding handler
function handleOnboard() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(['success' => false, 'message' => 'Only POST method allowed'], 405);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $missing = validateRequired($input, ['name', 'mobile', 'password', 'branch_name']);

    if (!empty($missing)) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)], 400);
        return;
    }

    $name = trim($input['name']);
    $mobile = trim($input['mobile']);
    $password = $input['password'];
    $branchName = trim($input['branch_name']);
    $email = isset($input['email']) ? trim($input['email']) : null;

    // Validate mobile number format
    if (!preg_match('/^\d{10}$/', $mobile)) {
        sendResponse(['success' => false, 'message' => 'Mobile number must be exactly 10 digits'], 400);
        return;
    }

    // Validate password length
    if (strlen($password) < 6) {
        sendResponse(['success' => false, 'message' => 'Password must be at least 6 characters long'], 400);
        return;
    }

    try {
        // Resolve branch ID from branch name
        $branch = fetchRow(
            "SELECT id, name FROM medt_branches WHERE LOWER(TRIM(name)) = LOWER(?) LIMIT 1",
            [$branchName],
            's'
        );

        if (!$branch) {
            // Auto-create branch if it doesn't exist
            $insertResult = executeInsert(
                "INSERT INTO medt_branches (name, location) VALUES (?, ?)",
                [$branchName, $branchName . ', Tamil Nadu'],
                'ss'
            );
            
            if (!$insertResult || !$insertResult['insert_id']) {
                sendResponse([
                    'success' => false, 
                    'message' => "Failed to create branch '$branchName'."
                ], 500);
                return;
            }
            
            $branchId = (int)$insertResult['insert_id'];
            $branch = ['id' => $branchId, 'name' => $branchName];
        } else {
            $branchId = (int)$branch['id'];
        }

        // Check if user already exists
        $existingUser = fetchRow(
            "SELECT id, name, mobile FROM medt_users WHERE mobile = ?",
            [$mobile],
            's'
        );

        if ($existingUser) {
            // User exists - update their branch access
            $userId = (int)$existingUser['id'];
            
            // Check if user already has access to this branch
            $existingAccess = fetchRow(
                "SELECT id FROM medt_user_branches WHERE user_id = ? AND branch_id = ?",
                [$userId, $branchId],
                'ii'
            );

            if (!$existingAccess) {
                // Add branch access
                executeInsert(
                    "INSERT INTO medt_user_branches (user_id, branch_id) VALUES (?, ?)",
                    [$userId, $branchId],
                    'ii'
                );
            }

            // Update user details if provided
            $updateData = [];
            $updateParams = [];
            $updateTypes = '';

            if ($name && $name !== $existingUser['name']) {
                $updateData[] = "name = ?";
                $updateParams[] = $name;
                $updateTypes .= 's';
            }

            if (!empty($updateData)) {
                $updateParams[] = $userId;
                $updateTypes .= 'i';
                
                executeQuery(
                    "UPDATE medt_users SET " . implode(', ', $updateData) . " WHERE id = ?",
                    $updateParams,
                    $updateTypes
                );
            }

            sendResponse([
                'success' => true,
                'message' => "User '{$existingUser['name']}' updated successfully",
                'user_id' => $userId,
                'branch_id' => $branchId,
                'branch_name' => $branch['name'],
                'action' => 'updated'
            ]);

        } else {
            // Create new user
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $userId = executeInsert(
                "INSERT INTO medt_users (name, mobile, email, password) VALUES (?, ?, ?, ?)",
                [$name, $mobile, $email, $hashedPassword],
                'ssss'
            );

            if (!$userId) {
                sendResponse(['success' => false, 'message' => 'Failed to create user'], 500);
                return;
            }

            // Add branch access
            executeInsert(
                "INSERT INTO medt_user_branches (user_id, branch_id) VALUES (?, ?)",
                [$userId, $branchId],
                'ii'
            );

            sendResponse([
                'success' => true,
                'message' => "User '$name' created successfully",
                'user_id' => $userId,
                'branch_id' => $branchId,
                'branch_name' => $branch['name'],
                'action' => 'created'
            ]);
        }

    } catch (Exception $e) {
        error_log('Onboard error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to onboard user'], 500);
    }
}

// Bulk Users Creation handler
function handleBulkUsers() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(['success' => false, 'message' => 'Only POST method allowed'], 405);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate that input is an array of user objects
    if (!is_array($input) || empty($input)) {
        sendResponse(['success' => false, 'message' => 'Input must be a non-empty array of user objects'], 400);
        return;
    }

    $results = [
        'success' => true,
        'processed' => 0,
        'created_users' => 0,
        'updated_users' => 0,
        'skipped_users' => 0,
        'created_branches' => 0,
        'errors' => []
    ];

    // Process each user in the array
    foreach ($input as $index => $userData) {
        try {
            $userResult = processBulkUser($userData, $index);
            
            // Update counters based on result
            if ($userResult['success']) {
                $results['processed']++;
                
                if ($userResult['user_action'] === 'created') {
                    $results['created_users']++;
                } elseif ($userResult['user_action'] === 'updated') {
                    $results['updated_users']++;
                } else {
                    $results['skipped_users']++;
                }
                
                $results['created_branches'] += $userResult['created_branches'];
            } else {
                $results['errors'][] = "User #{$index}: " . $userResult['message'];
            }
            
        } catch (Exception $e) {
            $results['errors'][] = "User #{$index}: " . $e->getMessage();
            error_log("Bulk user processing error for user #{$index}: " . $e->getMessage());
        }
    }

    // Determine overall success
    $results['success'] = $results['processed'] > 0 || empty($results['errors']);
    $results['message'] = sprintf(
        'Processed %d/%d users. Created: %d, Updated: %d, Skipped: %d, New branches: %d',
        $results['processed'],
        count($input),
        $results['created_users'],
        $results['updated_users'],
        $results['skipped_users'],
        $results['created_branches']
    );

    sendResponse($results);
}

// Process a single user in the bulk operation
function processBulkUser($userData, $index) {
    // Validate required fields for each user
    $missing = validateRequired($userData, ['username', 'mobile_number', 'default_password', 'branch_names']);
    
    if (!empty($missing)) {
        return [
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missing)
        ];
    }

    $username = trim($userData['username']);
    $mobileNumber = trim($userData['mobile_number']);
    $defaultPassword = $userData['default_password'];
    $branchNames = $userData['branch_names'];

    // Validate mobile number format
    if (!preg_match('/^\d{10}$/', $mobileNumber)) {
        return [
            'success' => false,
            'message' => 'Mobile number must be exactly 10 digits'
        ];
    }

    // Validate password length
    if (strlen($defaultPassword) < 6) {
        return [
            'success' => false,
            'message' => 'Password must be at least 6 characters long'
        ];
    }

    // Validate branch_names is an array
    if (!is_array($branchNames) || empty($branchNames)) {
        return [
            'success' => false,
            'message' => 'branch_names must be a non-empty array'
        ];
    }

    $result = [
        'success' => true,
        'user_action' => 'skipped',
        'created_branches' => 0,
        'branch_mappings' => []
    ];

    // Check if user already exists
    $existingUser = fetchRow(
        "SELECT id, name, mobile FROM medt_users WHERE mobile = ?",
        [$mobileNumber],
        's'
    );

    $userId = null;
    
    if ($existingUser) {
        // User exists - check if we need to update name
        $userId = (int)$existingUser['id'];
        
        if ($username !== $existingUser['name']) {
            // Update user's name
            executeQuery(
                "UPDATE medt_users SET name = ? WHERE id = ?",
                [$username, $userId],
                'si'
            );
            $result['user_action'] = 'updated';
        } else {
            $result['user_action'] = 'existed';
        }
    } else {
        // Create new user
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $insertResult = executeInsert(
            "INSERT INTO medt_users (name, mobile, password) VALUES (?, ?, ?)",
            [$username, $mobileNumber, $hashedPassword],
            'sss'
        );
        
        if (!$insertResult || !$insertResult['insert_id']) {
            return [
                'success' => false,
                'message' => 'Failed to create user'
            ];
        }
        
        $userId = (int)$insertResult['insert_id'];
        $result['user_action'] = 'created';
    }

    // Process branch mappings
    foreach ($branchNames as $branchName) {
        $branchName = trim($branchName);
        
        if (empty($branchName)) {
            continue;
        }

        // Find or create branch
        $branch = fetchRow(
            "SELECT id FROM medt_branches WHERE LOWER(TRIM(name)) = LOWER(?) LIMIT 1",
            [$branchName],
            's'
        );

        $branchId = null;
        
        if (!$branch) {
            // Create new branch
            $branchInsertResult = executeInsert(
                "INSERT INTO medt_branches (name, location) VALUES (?, ?)",
                [$branchName, $branchName . ', Tamil Nadu'],
                'ss'
            );
            
            if (!$branchInsertResult || !$branchInsertResult['insert_id']) {
                continue; // Skip this branch if creation failed
            }
            
            $branchId = (int)$branchInsertResult['insert_id'];
            $result['created_branches']++;
        } else {
            $branchId = (int)$branch['id'];
        }

        // Check if user-branch mapping already exists
        $existingMapping = fetchRow(
            "SELECT id FROM medt_user_branches WHERE user_id = ? AND branch_id = ?",
            [$userId, $branchId],
            'ii'
        );

        if (!$existingMapping) {
            // Create user-branch mapping
            executeInsert(
                "INSERT INTO medt_user_branches (user_id, branch_id) VALUES (?, ?)",
                [$userId, $branchId],
                'ii'
            );
            
            $result['branch_mappings'][] = [
                'branch_name' => $branchName,
                'action' => 'mapped'
            ];
        } else {
            $result['branch_mappings'][] = [
                'branch_name' => $branchName,
                'action' => 'existed'
            ];
        }
    }

    return $result;
}

// Admin Report handler
function handleAdminReport() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(['success' => false, 'message' => 'Method not allowed for /api/admin-report'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    error_log('Admin report - received data: ' . json_encode($input));

    $branch_ids = $input['branch_ids'] ?? [];
    $start_date = $input['start_date'] ?? null;
    $end_date = $input['end_date'] ?? null;

    if (empty($branch_ids)) {
        sendResponse(['success' => false, 'message' => 'Branch IDs are required'], 400);
    }

    try {
        $reports = [];
        
        // Create placeholders for branch IDs
        $placeholders = str_repeat('?,', count($branch_ids) - 1) . '?';
        
        foreach ($branch_ids as $branch_id) {
            // Get branch name
            $branch = fetchRow("SELECT name FROM medt_branches WHERE id = ?", [$branch_id], 'i');
            if (!$branch) {
                continue; // Skip if branch not found
            }
            
            // Build date filter condition
            $dateCondition = '';
            $dateParams = [];
            $dateTypes = '';
            
            if ($start_date && $end_date) {
                $dateCondition = ' AND session_date BETWEEN ? AND ?';
                $dateParams = [$start_date, $end_date];
                $dateTypes = 'ss';
            } elseif ($start_date) {
                $dateCondition = ' AND session_date >= ?';
                $dateParams = [$start_date];
                $dateTypes = 's';
            } elseif ($end_date) {
                $dateCondition = ' AND session_date <= ?';
                $dateParams = [$end_date];
                $dateTypes = 's';
            }
            
            // Get summary statistics for this branch
            $summary_params = array_merge([$branch_id], $dateParams);
            $summary_types = 'i' . $dateTypes;
            
            // Total unique participants
            $total_participants = fetchRow("
                SELECT COUNT(DISTINCT participant_id) as total_participants
                FROM medt_meditation_sessions 
                WHERE branch_id = ? $dateCondition
            ", $summary_params, $summary_types);
            
            // Total sessions
            $total_sessions = fetchRow("
                SELECT COUNT(*) as total_sessions
                FROM medt_meditation_sessions 
                WHERE branch_id = ? $dateCondition
            ", $summary_params, $summary_types);
            
            // Total hours (accounting for overlapping sessions)
            $sessions_for_hours = fetchAll("
                SELECT DISTINCT session_date, start_time, duration_minutes
                FROM medt_meditation_sessions
                WHERE branch_id = ? $dateCondition
                ORDER BY session_date, start_time
            ", $summary_params, $summary_types);

            $total_minutes_value = calculateTotalMinutesWithOverlap($sessions_for_hours);
            $total_minutes = ['total_minutes' => $total_minutes_value];
            
            // Get daily stats
            // First get all distinct sessions for the date range
            $daily_sessions = fetchAll("
                SELECT DISTINCT
                    session_date,
                    start_time,
                    duration_minutes
                FROM medt_meditation_sessions
                WHERE branch_id = ? $dateCondition
                ORDER BY session_date DESC, start_time
            ", $summary_params, $summary_types);

            // Group sessions by date and calculate overlap-aware totals
            $sessions_by_date = [];
            foreach ($daily_sessions as $session) {
                $date = $session['session_date'];
                if (!isset($sessions_by_date[$date])) {
                    $sessions_by_date[$date] = [];
                }
                $sessions_by_date[$date][] = $session;
            }

            // Calculate stats for each date
            $daily_stats = [];
            foreach ($sessions_by_date as $date => $sessions) {
                // Get unique participants for this date
                $participant_params = [(int)$branch_id, $date];
                $participant_types = 'is';
                $participant_count = fetchRow("
                    SELECT COUNT(DISTINCT participant_id) as total_participants
                    FROM medt_meditation_sessions
                    WHERE branch_id = ? AND session_date = ?
                ", $participant_params, $participant_types);

                // Calculate total minutes with overlap handling
                $total_minutes = calculateTotalMinutesWithOverlap($sessions);

                $daily_stats[] = [
                    'date' => $date,
                    'sessions_count' => count($sessions),
                    'unique_participants' => (int)$participant_count['total_participants'],
                    'total_minutes' => $total_minutes
                ];
            }
            
            $reports[] = [
                'branch_id' => $branch_id,
                'branch_name' => $branch['name'],
                'summary' => [
                    'participants' => (int)($total_participants['total_participants'] ?? 0),
                    'sessions' => (int)($total_sessions['total_sessions'] ?? 0),
                    'hours' => round(($total_minutes['total_minutes'] ?? 0) / 60, 2)
                ],
                'daily_stats' => array_map(function($stat) {
                    return [
                        'date' => $stat['date'],
                        'sessions_count' => (int)$stat['sessions_count'],
                        'unique_participants' => (int)$stat['unique_participants'],
                        'total_minutes' => (int)$stat['total_minutes']
                    ];
                }, $daily_stats)
            ];
        }
        
        sendResponse([
            'success' => true,
            'data' => $reports
        ]);
        
    } catch (Exception $e) {
        error_log('Admin report error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to generate admin report'], 500);
    }
}

// Branches handler (public endpoint)
function handleBranches() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(['success' => false, 'message' => 'Method not allowed for /api/branches'], 405);
    }

    try {
        $branches = fetchAll("SELECT id, name FROM medt_branches ORDER BY name");
        sendResponse([
            'success' => true,
            'branches' => $branches
        ]);
    } catch (Exception $e) {
        error_log('Branches error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to fetch branches'], 500);
    }
}

// Public function to get all branches (for admin report)
function getAllBranches() {
    try {
        return fetchAll("SELECT id, name FROM medt_branches ORDER BY name");
    } catch (Exception $e) {
        error_log('Get all branches error: ' . $e->getMessage());
        return [];
    }
}
?>