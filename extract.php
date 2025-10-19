<?php
// Simple ZIP extractor to site root
// Usage options:
// 1) POST a file field named 'file' (multipart/form-data)
// 2) GET with ?zip=filename.zip (file must exist alongside this script)
// Extracts into detected site root.

declare(strict_types=1);

header('Content-Type: application/json');
set_time_limit(300);

// Detect extraction root directory
$scriptDir = __DIR__;
// Default: extract into the same directory as this script (most shared hosts expect this)
$root = $scriptDir;
// Optional override: ?root=parent extracts one level up
if (isset($_GET['root']) && $_GET['root'] === 'parent') {
    $parent = dirname($scriptDir);
    if (@is_dir($parent) && @is_writable($parent)) {
        $root = realpath($parent) ?: $parent;
    }
}

// Resolve ZIP file source
$zipPath = null;

// Case 1: uploaded file
if (!empty($_FILES['file']) && isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    $zipPath = $_FILES['file']['tmp_name'];
}

// Case 2: query param refers to a zip in the same directory as this script
if ($zipPath === null && isset($_GET['zip'])) {
    $candidate = basename((string)$_GET['zip']); // basename to avoid path traversal
    $candidatePath = $scriptDir . DIRECTORY_SEPARATOR . $candidate;
    if (is_file($candidatePath)) {
        $zipPath = $candidatePath;
    }
}

// Default filename fallback
if ($zipPath === null) {
    foreach (['site.zip', 'build.zip', 'dist.zip'] as $defaultZip) {
        $candidatePath = $scriptDir . DIRECTORY_SEPARATOR . $defaultZip;
        if (is_file($candidatePath)) {
            $zipPath = $candidatePath;
            break;
        }
    }
}

if ($zipPath === null) {
    http_response_code(400);
    $files = array_values(array_filter(scandir($scriptDir) ?: [], function($f) use ($scriptDir) {
        return preg_match('/\.zip$/i', $f) && is_file($scriptDir . DIRECTORY_SEPARATOR . $f);
    }));
    echo json_encode([
        'success' => false,
        'message' => 'No ZIP provided. Upload via file field "file" or pass ?zip=filename.zip.',
        'script_dir' => $scriptDir,
        'available_zips' => $files
    ]);
    exit;
}

// Validate ZIP
if (!is_readable($zipPath)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ZIP file is not readable.'
    ]);
    exit;
}

// Extract
try {
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('ZipArchive extension is not enabled.');
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        throw new RuntimeException('Failed to open ZIP file.');
    }

    // Ensure root exists and is writable
    if (!is_dir($root)) {
        throw new RuntimeException('Target root directory does not exist: ' . $root);
    }
    if (!is_writable($root)) {
        throw new RuntimeException('Target root directory is not writable: ' . $root);
    }

    if (!$zip->extractTo($root)) {
        $zip->close();
        throw new RuntimeException('Failed to extract ZIP to root.');
    }
    $zip->close();

    echo json_encode([
        'success' => true,
        'message' => 'ZIP extracted successfully.',
        'target_root' => $root,
        'source_zip' => (is_uploaded_file($_FILES['file']['tmp_name'] ?? '') ? 'uploaded' : $zipPath)
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'zip' => $zipPath,
        'target_root' => $root
    ]);
}
?>
