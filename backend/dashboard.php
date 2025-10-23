<?php
require_once 'config-mysqli.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

$branch_id = $_GET['branch_id'] ?? null;
$month = $_GET['month'] ?? date('Y-m');

if (!$branch_id) {
    sendResponse(['error' => 'Branch ID is required'], 400);
}

try {
    // Get total medt_participants this month
    $total_medt_participants_result = fetchRow("
        SELECT COUNT(DISTINCT participant_id) as total_medt_participants
        FROM medt_meditation_sessions 
        WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
    ", [(int)$branch_id, $month], 'is');
    
    $total_medt_participants = (int)($total_medt_participants_result['total_medt_participants'] ?? 0);

    // Get total hours this month - based on unique time slots
    $total_minutes_result = fetchRow("
        SELECT SUM(duration_minutes) as total_minutes
        FROM (
            SELECT DISTINCT session_date, start_time, duration_minutes
            FROM medt_meditation_sessions 
            WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
        ) as unique_time_slots
    ", [(int)$branch_id, $month], 'is');
    
    $total_minutes = (int)($total_minutes_result['total_minutes'] ?? 0);
    $total_hours = round($total_minutes / 60, 2);

    // Get total sessions this month
    $total_sessions_result = fetchRow("
        SELECT COUNT(*) as total_sessions
        FROM medt_meditation_sessions 
        WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
    ", [(int)$branch_id, $month], 'is');
    
    $total_sessions = (int)($total_sessions_result['total_sessions'] ?? 0);

    // Get top medt_participants this month
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

    // Convert to integers
    foreach ($top_medt_participants as &$participant) {
        $participant['participant_id'] = (int)$participant['participant_id'];
        $participant['session_count'] = (int)$participant['session_count'];
        $participant['total_minutes'] = (int)$participant['total_minutes'];
    }

    // Get time distribution (Morning/Afternoon/Evening) - based on unique time slots
    $time_distribution = fetchAll("
        SELECT 
            period,
            COUNT(*) as session_count,
            SUM(duration_minutes) as total_minutes
        FROM (
            SELECT DISTINCT
                session_date, start_time, duration_minutes,
                CASE 
                    WHEN TIME(start_time) >= '05:00' AND TIME(start_time) < '12:00' THEN 'Morning'
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

    // Convert to integers
    foreach ($time_distribution as &$dist) {
        $dist['session_count'] = (int)$dist['session_count'];
        $dist['total_minutes'] = (int)$dist['total_minutes'];
    }

    // Get daily breakdown for the month - based on unique time slots per day
    $daily_stats = fetchAll("
        SELECT 
            date,
            COUNT(*) as sessions_count,
            total_medt_participants as unique_medt_participants,
            SUM(duration_minutes) as total_minutes
        FROM (
            SELECT DISTINCT
                session_date as date,
                start_time,
                duration_minutes,
                (SELECT COUNT(DISTINCT participant_id) 
                 FROM medt_meditation_sessions ms2 
                 WHERE ms2.session_date = ms1.session_date 
                   AND ms2.branch_id = ms1.branch_id) as total_medt_participants
            FROM medt_meditation_sessions ms1
            WHERE branch_id = ? AND DATE_FORMAT(session_date, '%Y-%m') = ?
        ) as unique_daily_slots
        GROUP BY date, total_medt_participants
        ORDER BY date
    ", [(int)$branch_id, $month], 'is');

    // Convert to integers
    foreach ($daily_stats as &$stat) {
        $stat['sessions_count'] = (int)$stat['sessions_count'];
        $stat['unique_medt_participants'] = (int)$stat['unique_medt_participants'];
        $stat['total_minutes'] = (int)$stat['total_minutes'];
    }

    $response = [
        'success' => true,
        'data' => [
            'summary' => [
                'total_medt_participants' => $total_medt_participants,
                'total_hours' => $total_hours,
                'total_sessions' => $total_sessions
            ],
            'top_medt_participants' => $top_medt_participants,
            'time_distribution' => $time_distribution,
            'daily_stats' => $daily_stats
        ]
    ];

    sendResponse($response);

} catch (Exception $e) {
    error_log('Dashboard error: ' . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Failed to fetch dashboard data'], 500);
}
?>