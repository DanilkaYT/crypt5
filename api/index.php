<?php
// Убираем вывод ошибок, чтобы не ломать JSON или редиректы
error_reporting(0);
ini_set('display_errors', 0);

// Заголовки для CORS
header('Access-Control-Allow-Origin: *');

// Железобетонный способ вытащить URL, даже если внутри него есть свои параметры (?token=...)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$url = '';

// Ищем, где начинается 'url=' и забираем всё до конца строки
$pos = strpos($requestUri, 'url=');
if ($pos !== false) {
    $url = substr($requestUri, $pos + 4);
} else {
    // Резервный вариант
    $url = $_GET['url'] ?? '';
}

// Если URL вообще не передали — отдаем ошибку
if (!$url) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "No URL provided", "debug_uri" => $requestUri]);
    exit;
}

// 2. Готовим данные для POST-запроса к API Happ
$postData = json_encode(['url' => $url]);

// 3. Стучимся в API 
$ch = curl_init("https://crypto.happ.su/api-v2.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 4. Очищаем ответ
$happLink = trim($response);

// 5. Проверяем, что API вернул успешный статус
if ($httpCode === 200 && strpos($happLink, 'happ://crypt5/') !== false) {
    
    header("Location: " . $happLink);
    
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<meta http-equiv='refresh' content='0;url={$happLink}'></head>";
    echo "<body style='display:flex;justify-content:center;align-items:center;height:100vh;background-color:#121212;margin:0;font-family:sans-serif;'>";
    echo "<script>window.location.replace('{$happLink}');</script>";
    echo "<a href='{$happLink}' style='padding:16px 32px;background:#007bff;color:white;text-decoration:none;border-radius:12px;font-weight:bold;font-size:18px;'>ОТКРЫТЬ В HAPP</a>";
    echo "</body></html>";
    exit;
    
} else {
    header('Content-Type: application/json');
    echo json_encode([
        "error" => "Encryption failed at Happ API",
        "api_response" => htmlspecialchars($response),
        "http_code" => $httpCode
    ]);
}
?>
}
?>
