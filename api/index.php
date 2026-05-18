<?php
error_reporting(0);
ini_set('display_errors', 0);

// 1. Получаем URL подписки из GET-параметра (который прислал воркер)
$workerUrl = $_GET['url'] ?? '';

if (!$workerUrl && !empty($_SERVER['QUERY_STRING'])) {
    $workerUrl = str_replace('url=', '', $_SERVER['QUERY_STRING']);
}

if ($workerUrl) {
    // 2. Готовим данные для POST-запроса в формате JSON
    $postData = json_encode(['url' => $workerUrl]);

    // 3. Выполняем CURL (эквивалент твоей команды curl -X POST...)
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

    // 4. API Happ возвращает готовую строку happ://crypt5/...
    $happLink = trim($response);

    // Если в ответе есть протокол happ, делаем редирект
    if (strpos($happLink, 'happ://') !== false) {
        header("Location: " . $happLink);
        
        // Резервный способ через JS, если заголовок не сработает
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
        echo "<script>window.location.replace('$happLink');</script>";
        echo "<div style='text-align:center; margin-top:50px;'>";
        echo "<h2>Ссылка сформирована</h2>";
        echo "<a href='$happLink' style='padding:15px 25px; background:#007bff; color:white; text-decoration:none; border-radius:8px;'>ОТКРЫТЬ В HAPP</a>";
        echo "</div></body></html>";
        exit;
    } else {
        echo "Ошибка API Happ: " . htmlspecialchars($response);
    }
} else {
    echo "Ошибка: URL не передан в скрипт.";
}
