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

if (!isset($_FILES['videos']) && !isset($_FILES['posters'])) {
    sendResponse(false, 'لم يتم رفع أي ملفات');
}

$uploadedEpisodes = [];
$errors = [];

$videosCount = isset($_FILES['videos']) ? count($_FILES['videos']['name']) : 0;
$postersCount = isset($_FILES['posters']) ? count($_FILES['posters']['name']) : 0;

$currentEpisodesCount = count($cardFound['episodes']);
$totalAfterUpload = $currentEpisodesCount + $videosCount;

if ($totalAfterUpload > $cardFound['max_episodes']) {
    sendResponse(false, 'عدد الحلقات الإجمالي سيتجاوز الحد المسموح به (' . $cardFound['max_episodes'] . '). الحلقات الحالية: ' . $currentEpisodesCount . '، تحاول رفع: ' . $videosCount);
}

define('MAX_VIDEO_SIZE', 400 * 1024 * 1024);
define('MAX_POSTER_SIZE', 10 * 1024 * 1024);

for ($i = 0; $i < $videosCount; $i++) {
    if ($_FILES['videos']['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }
    
    $videoName = $_FILES['videos']['name'][$i];
    $videoTmpName = $_FILES['videos']['tmp_name'][$i];
    $videoSize = $_FILES['videos']['size'][$i];
    $videoExt = strtolower(pathinfo($videoName, PATHINFO_EXTENSION));
    
    if ($videoSize > MAX_VIDEO_SIZE) {
        $errors[] = "حجم الفيديو كبير جداً: $videoName (الحد الأقصى: 400 ميجابايت)";
        continue;
    }
    
    $allowedVideoExts = ['mp4', 'mkv', 'avi', 'mov', 'webm'];
    if (!in_array($videoExt, $allowedVideoExts)) {
        $errors[] = "صيغة الفيديو غير مدعومة: $videoName";
        continue;
    }
    
    $videoFileName = uniqid() . '_' . time() . '.' . $videoExt;
    $videoPath = UPLOAD_PATH . '/episodes/' . $videoFileName;
    
    if (!move_uploaded_file($videoTmpName, $videoPath)) {
        $errors[] = "فشل رفع الفيديو: $videoName";
        continue;
    }
    
    $posterFileName = '';
    if (isset($_FILES['posters']['name'][$i]) && $_FILES['posters']['error'][$i] === UPLOAD_ERR_OK) {
        $posterName = $_FILES['posters']['name'][$i];
        $posterTmpName = $_FILES['posters']['tmp_name'][$i];
        $posterSize = $_FILES['posters']['size'][$i];
        $posterExt = strtolower(pathinfo($posterName, PATHINFO_EXTENSION));
        
        if ($posterSize > MAX_POSTER_SIZE) {
            $errors[] = "حجم الملصق كبير جداً: $posterName (الحد الأقصى: 10 ميجابايت)";
            continue;
        }
        
        $allowedImageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($posterExt, $allowedImageExts)) {
            $posterFileName = uniqid() . '_' . time() . '.' . $posterExt;
            $posterPath = UPLOAD_PATH . '/posters/' . $posterFileName;
            move_uploaded_file($posterTmpName, $posterPath);
        }
    }
    
    $uploadedEpisodes[] = [
        'id' => uniqid(),
        'title' => pathinfo($videoName, PATHINFO_FILENAME),
        'video_file' => $videoFileName,
        'poster_file' => $posterFileName,
        'uploaded_at' => date('Y-m-d H:i:s')
    ];
}

$cards[$cardIndex]['episodes'] = array_merge($cards[$cardIndex]['episodes'], $uploadedEpisodes);
writeJSON('cards.json', $cards);

sendResponse(true, 'تم رفع الحلقات بنجاح', [
    'uploaded_count' => count($uploadedEpisodes),
    'episodes' => $uploadedEpisodes,
    'errors' => $errors
]);
