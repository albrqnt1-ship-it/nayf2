<?php
session_start();

define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('UPLOAD_PATH', BASE_PATH . '/uploads');

header('Content-Type: application/json; charset=utf-8');

function readJSON($filename) {
    $filepath = DATA_PATH . '/' . $filename;
    if (!file_exists($filepath)) {
        return [];
    }
    $content = file_get_contents($filepath);
    return json_decode($content, true) ?: [];
}

function writeJSON($filename, $data) {
    $filepath = DATA_PATH . '/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    $tempFile = $filepath . '.tmp';
    if (file_put_contents($tempFile, $json) === false) {
        return false;
    }
    
    if (!rename($tempFile, $filepath)) {
        unlink($tempFile);
        return false;
    }
    
    return true;
}

function getDeviceFingerprint() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return md5($userAgent . $ip);
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function sendResponse($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function checkAdmin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        sendResponse(false, 'غير مصرح');
    }
}

function checkCardSession() {
    if (!isset($_SESSION['card_number'])) {
        sendResponse(false, 'الرجاء تسجيل الدخول');
    }
}
