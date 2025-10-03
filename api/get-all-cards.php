<?php
require_once 'config.php';

checkAdmin();

$cards = readJSON('cards.json');
$devices = readJSON('devices.json');

$cardsWithDeviceCount = array_map(function($card) use ($devices) {
    $cardDevices = array_filter($devices, function($d) use ($card) {
        return $d['card_number'] === $card['card_number'];
    });
    
    $card['connected_devices'] = count($cardDevices);
    $card['episodes_count'] = count($card['episodes']);
    return $card;
}, $cards);

sendResponse(true, 'تم جلب البطاقات', ['cards' => $cardsWithDeviceCount]);
