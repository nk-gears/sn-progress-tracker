<?php
require_once 'config.php';

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
    $stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE mobile = ?");
    $stmt->execute([$mobile]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        sendResponse(['error' => 'Invalid credentials'], 401);
    }

    // Get user's branches
    $stmt = $pdo->prepare("
        SELECT b.id, b.name, b.location 
        FROM branches b 
        JOIN user_branches ub ON b.id = ub.branch_id 
        WHERE ub.user_id = ?
        ORDER BY b.name
    ");
    $stmt->execute([$user['id']]);
    $branches = $stmt->fetchAll();

    // Generate a simple token (in production, use proper JWT or session management)
    $token = 'auth_token_' . $user['id'] . '_' . time();

    sendResponse([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'mobile' => $mobile
        ],
        'branches' => $branches,
        'token' => $token
    ]);

} catch (PDOException $e) {
    sendResponse(['error' => 'Authentication failed'], 500);
}
?>