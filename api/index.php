<?php
// Полностью отключаем вывод ошибок в текст, чтобы не ломать JSON
error_reporting(0);
ini_set('display_errors', 0);

// Разрешаем запросы ототовсюду (важно для Cloudflare)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

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

// Берем URL из GET-параметра
$url = isset($_GET['url']) ? $_GET['url'] : '';

if (empty($url)) {
    echo json_encode(["error" => "empty_url"]);
    exit;
}

// Попробуем зашифровать. Для HAPP чаще всего нужен именно PKCS1
if (openssl_public_encrypt($url, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING)) {
    echo json_encode([
        "crypt5" => "happ://crypt5/" . base64_encode($encrypted)
    ]);
} else {
    echo json_encode([
        "error" => "openssl_fail",
        "msg" => openssl_error_string()
    ]);
}
