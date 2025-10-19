<?php
// Simple remote file downloader
// Usage:
//  - GET/POST param 'url' (required): the remote HTTP(S) URL
//  - Optional 'filename': target filename to save as (basename only)
//  - Optional 'dir': subdirectory under site root to save into (default: downloads)
// Responds with JSON about the saved file.

declare(strict_types=1);

header('Content-Type: application/json');
set_time_limit(300);

function detect_root(): string {
    $scriptDir = __DIR__;
    // Prefer DOCUMENT_ROOT if available and writable
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
        if (@is_dir($docRoot) && @is_writable($docRoot)) {
            return realpath($docRoot) ?: $docRoot;
        }
    }
    // If placed under api/, use parent as root
    if (basename($scriptDir) === 'api') {
        $parent = dirname($scriptDir);
        if (@is_dir($parent) && @is_writable($parent)) {
            return realpath($parent) ?: $parent;
        }
    }
    // Fallback to current dir
    return $scriptDir;
}

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

$url = $_POST['url'] ?? $_GET['url'] ?? '';
if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    respond(400, [
        'success' => false,
        'message' => 'Invalid or missing url parameter.'
    ]);
}

// Only allow http/https
$parts = parse_url($url);
$scheme = strtolower($parts['scheme'] ?? '');
if (!in_array($scheme, ['http', 'https'], true)) {
    respond(400, [
        'success' => false,
        'message' => 'Only http/https URLs are allowed.'
    ]);
}

$root = detect_root();
$dirParam = $_POST['dir'] ?? $_GET['dir'] ?? 'downloads';
$safeDir = trim(str_replace(['..', "\0"], '', $dirParam), "/\\");
$targetDir = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeDir;
if ($safeDir === '') {
    $targetDir = $root;
}

// Ensure target directory exists
if (!is_dir($targetDir)) {
    if (!@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        respond(500, [
            'success' => false,
            'message' => 'Failed to create target directory: ' . $targetDir
        ]);
    }
}

if (!is_writable($targetDir)) {
    respond(500, [
        'success' => false,
        'message' => 'Target directory is not writable: ' . $targetDir
    ]);
}

$nameParam = $_POST['filename'] ?? $_GET['filename'] ?? '';
$basename = $nameParam !== '' ? basename($nameParam) : basename($parts['path'] ?? 'downloaded.file');
if ($basename === '' || $basename === '.' || $basename === '..') {
    $basename = 'downloaded.file';
}

$targetPath = $targetDir . DIRECTORY_SEPARATOR . $basename;

// Download using cURL if available, else fallback to streams
$bytes = 0;
try {
    if (function_exists('curl_init')) {
        $fp = @fopen($targetPath, 'wb');
        if ($fp === false) {
            throw new RuntimeException('Failed to open target file for writing.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 180,
            CURLOPT_USERAGENT => 'Downloader/1.0',
            CURLOPT_FAILONERROR => true,
        ]);
        $ok = curl_exec($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fflush($fp);
        $stat = fstat($fp);
        $bytes = $stat['size'] ?? 0;
        fclose($fp);

        if (!$ok || $status >= 400) {
            @unlink($targetPath);
            throw new RuntimeException($err ?: ('HTTP error ' . $status));
        }
    } else {
        // Fallback using streams
        $in = @fopen($url, 'rb');
        if ($in === false) {
            throw new RuntimeException('Failed to open remote URL.');
        }
        $out = @fopen($targetPath, 'wb');
        if ($out === false) {
            fclose($in);
            throw new RuntimeException('Failed to open target file for writing.');
        }
        while (!feof($in)) {
            $chunk = fread($in, 8192);
            if ($chunk === false) break;
            $bytes += strlen($chunk);
            fwrite($out, $chunk);
        }
        fclose($in);
        fclose($out);
    }

    respond(200, [
        'success' => true,
        'message' => 'File downloaded successfully',
        'saved_as' => $targetPath,
        'bytes' => $bytes
    ]);
} catch (Throwable $e) {
    @unlink($targetPath);
    respond(500, [
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

