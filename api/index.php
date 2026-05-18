<?php
error_reporting(0);
ini_set('display_errors', 0);

$urlToEncrypt = $_GET['url'] ?? '';

if (!$urlToEncrypt) {
    die("No URL provided");
}

// Данные для отправки в Happ API
$postData = json_encode(['url' => $urlToEncrypt]);

// Настройка CURL запроса к официальному API
$ch = curl_init("https://crypto.happ.su/api-v2.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && !empty($response)) {
    // API Happ возвращает сразу готовую ссылку happ://crypt5/...
    $happLink = trim($response);

    // Выполняем редирект
    header("Location: " . $happLink);
    
    // Страховка для браузеров
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='0;url=$happLink'></head>";
    echo "<body style='display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;'>";
    echo "<script>window.location.replace('$happLink');</script>";
    echo "<a href='$happLink' style='padding:15px 30px;background:#007bff;color:white;text-decoration:none;border-radius:8px;'>ОТКРЫТЬ В HAPP</a>";
    echo "</body></html>";
} else {
    echo "Error from Happ API (Status $httpCode): " . htmlspecialchars($response);
}
