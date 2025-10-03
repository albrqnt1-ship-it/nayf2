<?php
require_once 'config.php';

checkAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'طريقة غير صالحة');
}

$cardNumber = sanitizeInput($_POST['card_number'] ?? '');
$maxEpisodes = intval($_POST['max_episodes'] ?? 0);
$maxDevices = intval($_POST['max_devices'] ?? 1);

if (empty($cardNumber)) {
    sendResponse(false, 'الرجاء إدخال رقم البطاقة');
}

if ($maxEpisodes <= 0) {
    sendResponse(false, 'الرجاء تحديد عدد الحلقات المطلوبة');
}

if ($maxDevices <= 0) {
    sendResponse(false, 'الرجاء تحديد عدد الأجهزة المسموح بها');
}

$cards = readJSON('cards.json');

foreach ($cards as $card) {
    if ($card['card_number'] === $cardNumber) {
        sendResponse(false, 'رقم البطاقة موجود مسبقاً');
    }
}

$newCard = [
    'card_number' => $cardNumber,
    'max_episodes' => $maxEpisodes,
    'max_devices' => $maxDevices,
    'status' => 'active',
    'episodes' => [],
    'created_at' => date('Y-m-d H:i:s')
];

$cards[] = $newCard;
writeJSON('cards.json', $cards);

sendResponse(true, 'تم إنشاء البطاقة بنجاح', ['card' => $newCard]);
