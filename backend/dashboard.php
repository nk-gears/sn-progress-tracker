<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

$branch_id = $_GET['branch_id'] ?? null;
$month = $_GET['month'] ?? date('Y-m');

if (!$branch_id) {
    sendResponse(['error' => 'Branch ID is required'], 400);
}

try {
    // Get total participants this month
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT participant_id) as total_participants
        FROM meditation_sessions 
        WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
    ");
    $stmt->execute([$branch_id, $month]);
    $total_participants = $stmt->fetchColumn();

    // Get total hours this month
    $stmt = $pdo->prepare("
        SELECT SUM(duration_minutes) as total_minutes
        FROM meditation_sessions 
        WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
    ");
    $stmt->execute([$branch_id, $month]);
    $total_minutes = $stmt->fetchColumn() ?? 0;
    $total_hours = round($total_minutes / 60, 2);

    // Get total sessions this month
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_sessions
        FROM meditation_sessions 
        WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
    ");
    $stmt->execute([$branch_id, $month]);
    $total_sessions = $stmt->fetchColumn();

    // Get daily breakdown for the month
    $stmt = $pdo->prepare("
        SELECT 
            session_date,
            COUNT(*) as sessions_count,
            COUNT(DISTINCT participant_id) as unique_participants,
            SUM(duration_minutes) as total_minutes
        FROM meditation_sessions 
        WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
        GROUP BY session_date
        ORDER BY session_date DESC
    ");
    $stmt->execute([$branch_id, $month]);
    $daily_stats = $stmt->fetchAll();

    // Get top participants this month
    $stmt = $pdo->prepare("
        SELECT 
            p.name,
            COUNT(*) as session_count,
            SUM(ms.duration_minutes) as total_minutes
        FROM meditation_sessions ms
        JOIN participants p ON ms.participant_id = p.id
        WHERE ms.branch_id = ? AND DATE_FORMAT(ms.session_date, '%Y-%m') = ?
        GROUP BY p.id, p.name
        ORDER BY session_count DESC, total_minutes DESC
        LIMIT 10
    ");
    $stmt->execute([$branch_id, $month]);
    $top_participants = $stmt->fetchAll();

    // Get time slot distribution
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN HOUR(start_time) < 12 THEN 'Morning'
                WHEN HOUR(start_time) < 17 THEN 'Afternoon'
                ELSE 'Evening'
            END as time_period,
            COUNT(*) as session_count,
            SUM(duration_minutes) as total_minutes
        FROM meditation_sessions 
        WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
        GROUP BY time_period
        ORDER BY session_count DESC
    ");
    $stmt->execute([$branch_id, $month]);
    $time_distribution = $stmt->fetchAll();

    sendResponse([
        'success' => true,
        'data' => [
            'summary' => [
                'total_participants' => (int)$total_participants,
                'total_hours' => $total_hours,
                'total_sessions' => (int)$total_sessions,
                'month' => $month
            ],
            'daily_stats' => $daily_stats,
            'top_participants' => $top_participants,
            'time_distribution' => $time_distribution
        ]
    ]);

} catch (PDOException $e) {
    sendResponse(['error' => 'Failed to fetch dashboard data'], 500);
}
?>