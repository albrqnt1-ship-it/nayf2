<?php
require_once 'config.php';

checkAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'طريقة غير صالحة');
}

$cardNumber = sanitizeInput($_POST['card_number'] ?? '');
$status = sanitizeInput($_POST['status'] ?? '');

if (empty($cardNumber) || empty($status)) {
    sendResponse(false, 'البيانات غير مكتملة');
}

if (!in_array($status, ['active', 'suspended'])) {
    sendResponse(false, 'الحالة غير صالحة');
}

$cards = readJSON('cards.json');

foreach ($cards as &$card) {
    if ($card['card_number'] === $cardNumber) {
        $card['status'] = $status;
        writeJSON('cards.json', $cards);
        sendResponse(true, 'تم تحديث حالة البطاقة');
    }
}

sendResponse(false, 'البطاقة غير موجودة');
