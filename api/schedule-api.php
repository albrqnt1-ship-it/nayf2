<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $schedule = readJSON('schedule.json');
    sendResponse(true, 'تم جلب الجدول', $schedule);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkAdmin();
    
    $scheduleData = json_decode(file_get_contents('php://input'), true);
    
    if (!$scheduleData || !isset($scheduleData['schedule'])) {
        sendResponse(false, 'بيانات غير صالحة');
    }
    
    writeJSON('schedule.json', $scheduleData);
    sendResponse(true, 'تم تحديث الجدول بنجاح');
}

sendResponse(false, 'طريقة غير صالحة');
