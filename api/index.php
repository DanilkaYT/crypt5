<?php
error_reporting(0);
ini_set('display_errors', 0);

$publicKey = "-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAlBetA0wjbaj+h7oJ/d/h
pNrXvAcuhOdFGEFcfCxSWyLzWk4SAQ05gtaEGZyetTax2uqagi9HT6lapUSUe2S8
nMLJf5K+LEs9TYrhhBdx/B0BGahA+lPJa7nUwp7WfUmSF4hir+xka5ApHjzkAQn6
cdG6FKtSPgq1rYRPd1jRf2maEHwiP/e/jqdXLPP0SFBjWTMt/joUDgE7v/IGGB0L
Q7mGPAlgmxwUHVqP4bJnZ//5sNLxWMjtYHOYjaV+lixNSfhFM3MdBndjpkmgSfmg
D5uYQYDL29TDk6Eu+xetUEqry8ySPjUbNWdDXCglQWMxDGjaqYXMWgxBA1UKjUBW
wbgr5yKTJ7mTqhlYEC9D5V/LOnKd6pTSvaMxkHXwk8hBWvUNWAxzAf5JZ7EVE3jt
0j682+/hnmL/hymUE44yMG1gCcWvSpB3BTlKoMnl4yrTakmdkbASeFRkN3iMRewa
IenvMhzJh1fq7xwX94otdd5eLB2vRFavrnhOcN2JJAkKTnx9dwQwFpGEkg+8U613
+Tfm/f82l56fFeoFN98dD2mUFLFZoeJ5CG81ZeXrH83niI0joX7rtoAZIPWzq3Y1
Zb/Zq+kK2hSIhphY172Uvs8X2Qp2ac9UoTPM71tURsA9IvPNvUwSIo/aKlX5KE3I
VE0tje7twWXL5Gb1sfcXRzsCAwEAAQ==
-----END PUBLIC KEY-----";

$urlToEncrypt = $_GET['url'] ?? '';

if (!$urlToEncrypt) {
    die("No URL provided");
}

if (openssl_public_encrypt($urlToEncrypt, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING)) {
    $base64 = base64_encode($encrypted);
    $happLink = "happ://crypt5/" . $base64;

    // --- МАГИЯ РЕДИРЕКТА ---
    // Отправляем заголовок перемещения
    header("Location: " . $happLink);
    
    // На случай, если браузер блокирует автоматический редирект, 
    // выведем еще и HTML-кнопку для ручного нажатия
    echo "<!DOCTYPE html><html><head><meta http-equiv='refresh' content='0;url=$happLink'></head>";
    echo "<body style='display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;'>";
    echo "<script>window.location.href = '$happLink';</script>";
    echo "<a href='$happLink' style='padding:20px;background:#007bff;color:white;text-decoration:none;border-radius:10px;'>Открыть в Happ (если не открылось)</a>";
    echo "</body></html>";
} else {
    echo "Encryption failed";
}
