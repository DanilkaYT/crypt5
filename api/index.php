<?php
error_reporting(0);
ini_set('display_errors', 0);

// 1. Получаем URL, который прислал воркер
$workerUrl = $_GET['url'] ?? '';

// 2. Если URL пустой, пробуем достать его из всей строки запроса (на случай спецсимволов)
if (!$workerUrl && !empty($_SERVER['QUERY_STRING'])) {
    $workerUrl = str_replace('url=', '', $_SERVER['QUERY_STRING']);
}

// 3. Если URL всё-таки есть, собираем финальную ссылку и редиректим
if ($workerUrl) {
    // Формируем ссылку на официальное API Happ, как ты просил
    $finalHappApiLink = "https://crypto.happ.su/api-v2.php/?url=" . $workerUrl;

    // Делаем редирект
    header("Location: " . $finalHappApiLink);
    
    // Дублируем для браузеров (JS + HTML)
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='0;url=$finalHappApiLink'></head>";
    echo "<body><script>window.location.replace('$finalHappApiLink');</script>";
    echo "<a href='$finalHappApiLink'>Перейти к активации подписки</a></body></html>";
    exit;
} else {
    echo "Ошибка: URL от воркера не получен. Проверьте кнопку в боте.";
}
