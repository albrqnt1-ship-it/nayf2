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
    sendResponse(false, 'البطاقة غير موجودة');
}

sendResponse(true, 'تم جلب الحلقات', [
    'episodes' => $cardFound['episodes'],
    'card_info' => [
        'card_number' => $cardFound['card_number'],
        'total_episodes' => count($cardFound['episodes'])
    ]
]);
