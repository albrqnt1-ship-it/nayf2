<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'طريقة غير صالحة');
}

$cardNumber = sanitizeInput($_POST['card_number'] ?? '');

if (empty($cardNumber)) {
    sendResponse(false, 'الرجاء إدخال رقم البطاقة');
}

$cards = readJSON('cards.json');
$devices = readJSON('devices.json');

$cardFound = null;
foreach ($cards as $card) {
    if ($card['card_number'] === $cardNumber) {
        $cardFound = $card;
        break;
    }
}

if (!$cardFound) {
    sendResponse(false, 'رقم البطاقة غير صحيح');
}

if ($cardFound['status'] === 'suspended') {
    sendResponse(false, 'البطاقة معلقة. يرجى الاتصال بالإدارة');
}

$deviceId = getDeviceFingerprint();
$cardDevices = array_filter($devices, function($d) use ($cardNumber) {
    return $d['card_number'] === $cardNumber;
});

$deviceExists = false;
foreach ($cardDevices as $device) {
    if ($device['device_id'] === $deviceId) {
        $deviceExists = true;
        break;
    }
}

if (!$deviceExists) {
    if (count($cardDevices) >= $cardFound['max_devices']) {
        sendResponse(false, 'تم الوصول إلى الحد الأقصى للأجهزة المسموح بها');
    }
    
    $devices[] = [
        'card_number' => $cardNumber,
        'device_id' => $deviceId,
        'connected_at' => date('Y-m-d H:i:s'),
        'last_seen' => date('Y-m-d H:i:s')
    ];
    writeJSON('devices.json', $devices);
} else {
    foreach ($devices as &$device) {
        if ($device['card_number'] === $cardNumber && $device['device_id'] === $deviceId) {
            $device['last_seen'] = date('Y-m-d H:i:s');
            break;
        }
    }
    writeJSON('devices.json', $devices);
}

$_SESSION['card_number'] = $cardNumber;
$_SESSION['device_id'] = $deviceId;
$_SESSION['logged_in'] = true;

sendResponse(true, 'تم تسجيل الدخول بنجاح', [
    'card_number' => $cardNumber,
    'episodes_count' => count($cardFound['episodes'])
]);
