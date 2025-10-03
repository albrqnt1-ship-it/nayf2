<?php
require_once 'config.php';

checkCardSession();

$cardNumber = $_SESSION['card_number'];
$cards = readJSON('cards.json');

$cardFound = null;
foreach ($cards as $card) {
    if ($card['card_number'] === $cardNumber) {
        $cardFound = $card;
        break;
    }
}

if (!$cardFound) {
    session_destroy();
    sendResponse(false, 'البطاقة غير موجودة', ['logout' => true]);
}

if ($cardFound['status'] === 'suspended') {
    session_destroy();
    sendResponse(false, 'البطاقة معلقة. سيتم تسجيل الخروج', ['logout' => true]);
}

sendResponse(true, 'البطاقة نشطة', ['status' => 'active']);
