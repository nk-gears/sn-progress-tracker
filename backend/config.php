<?php
// MySQLi Version - No PDO Required!
header('Content-Type: application/json');

// CORS (duplicate safety with api.php; consistent values)
// Allow overriding via env var ALLOWED_ORIGINS (comma-separated)
$env_origins = getenv('ALLOWED_ORIGINS');
$allowed_origins = $env_origins
    ? array_filter(array_map('trim', explode(',', $env_origins)))
    : [
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        // Add production origins here as needed, e.g. 'https://yourdomain.com'
    ];

$request_origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($request_origin && in_array($request_origin, $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $request_origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

// Database configuration
// Development (local)
$host = '192.168.1.13';
$dbname = 'meditation_tracker';
$username = 'mediuser';
$password = 'mediuser123!';

// Production
// $host = 'localhost';
// $dbname = 'u388678206_wB70c';
// $username = 'u388678206_NQZQ0';
// $password = 'PLjGQbBDYn';
// Create MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => 'Could not connect to MySQL: ' . $mysqli->connect_error
    ]);
    exit;
}

// Set charset
$mysqli->set_charset("utf8mb4");

function validateRequired($data, $required_fields) {
    $missing = [];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing[] = $field;
        }
    }
    return $missing;
}

function sendResponse($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

// Helper function to escape strings for MySQL
function escapeString($str) {
    global $mysqli;
    return $mysqli->real_escape_string($str);
}

// Helper function for prepared statements
function executeQuery($query, $params = [], $types = '') {
    global $mysqli;
    
    if (empty($params)) {
        $result = $mysqli->query($query);
        if (!$result) {
            throw new Exception('Query failed: ' . $mysqli->error);
        }
        return $result;
    }
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}

// Helper function to get single row
function fetchRow($query, $params = [], $types = '') {
    $result = executeQuery($query, $params, $types);
    return $result->fetch_assoc();
}

// Helper function to get all rows
function fetchAll($query, $params = [], $types = '') {
    $result = executeQuery($query, $params, $types);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// Helper function for INSERT operations
function executeInsert($query, $params = [], $types = '') {
    global $mysqli;
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $insert_id = $mysqli->insert_id;
    $affected_rows = $mysqli->affected_rows;
    $stmt->close();
    
    return ['insert_id' => $insert_id, 'affected_rows' => $affected_rows];
}
?>
