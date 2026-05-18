<?php
error_reporting(0);
ini_set('display_errors', 0);

// 1. Получаем URL от воркера (через GET-параметр ?url=...)
$workerUrl = $_GET['url'] ?? '';

if (!$workerUrl && !empty($_SERVER['QUERY_STRING'])) {
    $workerUrl = str_replace('url=', '', $_SERVER['QUERY_STRING']);
}

if ($workerUrl) {
    // 2. Готовим данные для POST-запроса к API Happ
    $postData = json_encode(['url' => $workerUrl]);

    // 3. Выполняем POST через cURL
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

    // 4. Очищаем ответ (ожидаем строку happ://crypt5/...)
    $happLink = trim($response);

    if ($httpCode === 200 && strpos($happLink, 'happ://') !== false) {
        // Мгновенный редирект для телефона
        header("Location: " . $happLink);
        
        // Резервный запуск через JS и кнопку
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
        echo "<meta http-equiv='refresh' content='0;url=$happLink'></head>";
        echo "<body style='display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;'>";
        echo "<script>window.location.replace('$happLink');</script>";
        echo "<a href='$happLink' style='padding:15px 30px;background:#007bff;color:white;text-decoration:none;border-radius:10px;'>ОТКРЫТЬ В HAPP</a>";
        echo "</body></html>";
        exit;
    } else {
        echo "Ошибка API Happ: " . htmlspecialchars($response);
    }
} else {
    echo "Ошибка: URL не передан в скрипт.";
}
