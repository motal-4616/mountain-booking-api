<?php

/**
 * Laravel Development Server - Custom Router
 * 
 * This file handles requests from PHP's built-in server.
 * It adds support for serving files from Railway Volume storage
 * where symlinks may not work properly.
 */

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Serve storage files directly from the Railway Volume mount
// The volume is at /app/storage/app/public, which is one level up from public/
if (preg_match('#^/storage/(.+)$#', $uri, $matches)) {
    $storagePath = dirname($publicPath) . '/storage/app/public/' . $matches[1];
    if (file_exists($storagePath) && is_file($storagePath)) {
        $mime = mime_content_type($storagePath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($storagePath));
        header('Cache-Control: public, max-age=31536000');
        readfile($storagePath);
        return;
    }
}

// If the file exists in public/, let PHP serve it directly
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

// Log the request
$formattedDateTime = date('D M j H:i:s Y');
$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];
file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

// Pass to Laravel
require_once $publicPath.'/index.php';
