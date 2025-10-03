<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'طريقة غير صالحة');
}

$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    sendResponse(false, 'الرجاء إدخال اسم المستخدم وكلمة المرور');
}

$admin = readJSON('admin.json');

if ($admin['username'] !== $username) {
    sendResponse(false, 'اسم المستخدم أو كلمة المرور غير صحيحة');
}

if (!password_verify($password, $admin['password'])) {
    sendResponse(false, 'اسم المستخدم أو كلمة المرور غير صحيحة');
}

$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_username'] = $username;

sendResponse(true, 'تم تسجيل الدخول بنجاح');
