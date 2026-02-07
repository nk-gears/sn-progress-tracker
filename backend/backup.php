<?php
// Data Export Script for medt_event_register with centre information
require_once 'config.php';

// Set CSV export headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="event_register_backup_' . date('Y-m-d_H-i-s') . '.csv"');

// Get optional centre_id from query string
$centre_id = isset($_GET['centre_id']) ? trim($_GET['centre_id']) : null;

try {
    // Build query with join to get centre information
    $query = "
        SELECT
            e.id,
            e.name,
            e.mobile,
            e.centre_id,
            c.center_code,
            c.state,
            c.district,
            c.locality,
            c.address,
            c.contact_no,
            e.created_at,
            e.updated_at
        FROM medt_event_register e
        LEFT JOIN medt_center_addresses c ON e.centre_id = c.id
    ";

    $params = [];
    $types = '';

    // Add WHERE clause if centre_id is provided
    if ($centre_id !== null && $centre_id !== '') {
        $query .= " WHERE e.centre_id = ?";
        $params[] = $centre_id;
        $types = 's';
    }

    $query .= " ORDER BY e.created_at DESC";

    // Execute query
    $result = executeQuery($query, $params, $types);

    if (!$result) {
        throw new Exception('Query failed: ' . $mysqli->error);
    }

    // Get column headers
    $headers = [
        'ID',
        'Name',
        'Mobile',
        'Centre ID',
        'Center Code',
        'State',
        'District',
        'Locality',
        'Address',
        'Contact No',
        'Created At',
        'Updated At'
    ];

    // Open output stream for writing CSV
    $output = fopen('php://output', 'w');

    // Write header row
    fputcsv($output, $headers);

    // Write data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['mobile'],
            $row['centre_id'] ?? '',
            $row['center_code'] ?? '',
            $row['state'] ?? '',
            $row['district'] ?? '',
            $row['locality'] ?? '',
            $row['address'] ?? '',
            $row['contact_no'] ?? '',
            $row['created_at'] ?? '',
            $row['updated_at'] ?? ''
        ]);
    }

    fclose($output);

} catch (Exception $e) {
    // If error occurs, send error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Export failed',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>
