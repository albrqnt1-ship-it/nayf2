<?php
require_once 'config.php';

checkAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'طريقة غير صالحة');
}

$cardNumber = sanitizeInput($_POST['card_number'] ?? '');

if (empty($cardNumber)) {
    sendResponse(false, 'رقم البطاقة مطلوب');
}

$cards = readJSON('cards.json');
$cardIndex = -1;
$cardFound = null;

foreach ($cards as $index => $card) {
    if ($card['card_number'] === $cardNumber) {
        $cardIndex = $index;
        $cardFound = $card;
        break;
    }
}

if (!$cardFound) {
    sendResponse(false, 'البطاقة غير موجودة');
}

foreach ($cardFound['episodes'] as $episode) {
    if ($episode['video_file']) {
        $videoPath = UPLOAD_PATH . '/episodes/' . $episode['video_file'];
        if (file_exists($videoPath)) {
            unlink($videoPath);
        }
    }
    if ($episode['poster_file']) {
        $posterPath = UPLOAD_PATH . '/posters/' . $episode['poster_file'];
        if (file_exists($posterPath)) {
            unlink($posterPath);
        }
    }
}

array_splice($cards, $cardIndex, 1);
writeJSON('cards.json', $cards);

$devices = readJSON('devices.json');
$devices = array_filter($devices, function($d) use ($cardNumber) {
    return $d['card_number'] !== $cardNumber;
});
writeJSON('devices.json', array_values($devices));

sendResponse(true, 'تم حذف البطاقة بنجاح');
