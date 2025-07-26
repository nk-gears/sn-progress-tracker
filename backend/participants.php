<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $branch_id = $_GET['branch_id'] ?? null;
    $search = $_GET['search'] ?? '';
    $action = $_GET['action'] ?? 'list';

    if (!$branch_id) {
        sendResponse(['success' => false, 'message' => 'Branch ID is required'], 400);
    }

    try {
        if ($action === 'search') {
            // Search participants with details for autocomplete
            $sql = "SELECT id, name, age, gender, branch_id, created_at FROM participants WHERE branch_id = ?";
            $params = [$branch_id];

            if (!empty($search)) {
                $sql .= " AND name LIKE ?";
                $params[] = "%$search%";
            }

            $sql .= " ORDER BY name LIMIT 20";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $participants = $stmt->fetchAll();

            sendResponse(['success' => true, 'participants' => $participants]);

        } else {
            // Get all participants for the branch
            $sql = "SELECT id, name, age, gender, branch_id, created_at FROM participants WHERE branch_id = ?";
            $params = [$branch_id];

            if (!empty($search)) {
                $sql .= " AND name LIKE ?";
                $params[] = "%$search%";
            }

            $sql .= " ORDER BY name LIMIT 50";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $participants = $stmt->fetchAll();

            sendResponse(['success' => true, 'participants' => $participants]);
        }

    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to fetch participants'], 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $missing = validateRequired($input, ['name', 'branch_id']);

    if (!empty($missing)) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)], 400);
    }

    $name = trim($input['name']);
    $branch_id = $input['branch_id'];
    $age = isset($input['age']) && $input['age'] !== '' ? (int)$input['age'] : null;
    $gender = isset($input['gender']) && $input['gender'] !== '' ? $input['gender'] : null;

    // Validate gender if provided
    if ($gender && !in_array($gender, ['Male', 'Female', 'Other'])) {
        sendResponse(['success' => false, 'message' => 'Invalid gender value'], 400);
    }

    try {
        // Check if participant already exists in this branch
        $stmt = $pdo->prepare("SELECT id, name, age, gender FROM participants WHERE name = ? AND branch_id = ?");
        $stmt->execute([$name, $branch_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update existing participant with new info if provided and not already set
            $updateNeeded = false;
            $updateAge = $existing['age'];
            $updateGender = $existing['gender'];

            if ($age && !$existing['age']) {
                $updateAge = $age;
                $updateNeeded = true;
            }

            if ($gender && !$existing['gender']) {
                $updateGender = $gender;
                $updateNeeded = true;
            }

            if ($updateNeeded) {
                $stmt = $pdo->prepare("UPDATE participants SET age = ?, gender = ? WHERE id = ?");
                $stmt->execute([$updateAge, $updateGender, $existing['id']]);
            }

            $participant = [
                'id' => $existing['id'],
                'name' => $existing['name'],
                'age' => $updateAge,
                'gender' => $updateGender,
                'branch_id' => $branch_id
            ];

            sendResponse(['success' => true, 'participant' => $participant, 'message' => 'Existing participant found and updated']);
        }

        // Create new participant
        $stmt = $pdo->prepare("INSERT INTO participants (name, branch_id, age, gender) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $branch_id, $age, $gender]);
        $participant_id = $pdo->lastInsertId();

        $participant = [
            'id' => $participant_id,
            'name' => $name,
            'age' => $age,
            'gender' => $gender,
            'branch_id' => $branch_id
        ];

        sendResponse(['success' => true, 'participant' => $participant, 'message' => 'Participant created successfully']);

    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to create participant'], 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Extract participant ID from URL path or input
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    $participant_id = end($pathParts);
    
    // If ID not in URL, check input
    if (!is_numeric($participant_id)) {
        $participant_id = $input['id'] ?? null;
    }

    if (!$participant_id) {
        sendResponse(['success' => false, 'message' => 'Participant ID is required'], 400);
    }

    $age = isset($input['age']) && $input['age'] !== '' ? (int)$input['age'] : null;
    $gender = isset($input['gender']) && $input['gender'] !== '' ? $input['gender'] : null;

    // Validate gender if provided
    if ($gender && !in_array($gender, ['Male', 'Female', 'Other'])) {
        sendResponse(['success' => false, 'message' => 'Invalid gender value'], 400);
    }

    try {
        // Update participant
        $stmt = $pdo->prepare("UPDATE participants SET age = ?, gender = ? WHERE id = ?");
        $stmt->execute([$age, $gender, $participant_id]);

        if ($stmt->rowCount() === 0) {
            sendResponse(['success' => false, 'message' => 'Participant not found or no changes made'], 404);
        }

        sendResponse(['success' => true, 'message' => 'Participant updated successfully']);

    } catch (PDOException $e) {
        sendResponse(['success' => false, 'message' => 'Failed to update participant'], 500);
    }

} else {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>