<?php
require_once 'config-mysqli.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$missing = validateRequired($input, ['mobile', 'password']);

if (!empty($missing)) {
    sendResponse(['error' => 'Missing required fields: ' . implode(', ', $missing)], 400);
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

    // Generate a simple token (in production, use proper JWT or session management)
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
?>