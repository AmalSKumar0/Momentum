<?php
// Vercel Entrypoint / Router for PHP
$requestUri = $_SERVER['REQUEST_URI'];

// Strip query string parameters to find the clean path
$path = parse_url($requestUri, PHP_URL_PATH);

// Define the root of the project
$baseDir = realpath(__DIR__ . '/..');

// Construct path to target file
$targetFile = $baseDir . $path;

// If a directory is requested, check for index.php inside it
if (is_dir($targetFile)) {
    $targetFile = rtrim($targetFile, '/') . '/index.php';
}

$resolvedTarget = realpath($targetFile);

// 1. Security Check: Protect against directory traversal (must be inside baseDir)
if ($resolvedTarget === false || strpos($resolvedTarget, $baseDir) !== 0) {
    http_response_code(403);
    echo "403 Forbidden";
    exit;
}

// 2. Serve PHP files
if (pathinfo($resolvedTarget, PATHINFO_EXTENSION) === 'php') {
    require $resolvedTarget;
    exit;
}

// 3. Fallback: Serve static files directly if they bypassed vercel.json routes
if (file_exists($resolvedTarget) && !is_dir($resolvedTarget)) {
    $mimeType = @mime_content_type($resolvedTarget) ?: 'application/octet-stream';
    header("Content-Type: " . $mimeType);
    readfile($resolvedTarget);
    exit;
}

// 4. Default: File not found
http_response_code(404);
echo "404 Not Found";
