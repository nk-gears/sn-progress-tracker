<?php
/**
 * Google Drive Image Proxy
 * Fetches images from Google Drive API and serves them to avoid CORS issues
 */

// Your Google Drive API Key
define('GOOGLE_DRIVE_API_KEY', 'AIzaSyASwOg_OMFtyElq0E5PDJURevxART436Hc'); // Replace with your actual API key

// Get file ID from query parameter
$fileId = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($fileId)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Error: File ID is required';
    exit;
}

// Validate file ID format (basic validation)
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $fileId)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Error: Invalid file ID format';
    exit;
}

// Google Drive API endpoint to download file
$apiUrl = "https://www.googleapis.com/drive/v3/files/{$fileId}?alt=media&key=" . GOOGLE_DRIVE_API_KEY;

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Set header callback to capture content type
$contentType = 'image/jpeg'; // default
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$contentType) {
    $len = strlen($header);
    if (stripos($header, 'Content-Type:') === 0) {
        $contentType = trim(substr($header, 13));
    }
    return $len;
});

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Check for errors
if ($response === false) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'cURL Error',
        'message' => $error,
        'fileId' => $fileId
    ]);
    exit;
}

if ($httpCode !== 200) {
    header("HTTP/1.1 $httpCode Error");
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Drive API Error',
        'httpCode' => $httpCode,
        'fileId' => $fileId,
        'apiUrl' => $apiUrl,
        'response' => substr($response, 0, 500)
    ]);
    exit;
}

// Set appropriate headers
header("Content-Type: $contentType");
header('Cache-Control: public, max-age=86400'); // Cache for 24 hours
header('Access-Control-Allow-Origin: *'); // Allow CORS

// Output the image
echo $response;
?>
