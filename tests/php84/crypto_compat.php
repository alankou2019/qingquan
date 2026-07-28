<?php

$root = dirname(dirname(__DIR__));
require_once $root . '/app/code/Dacang/Helper/ErrorCode.php';
require_once $root . '/app/code/Dacang/Helper/PKCS7Encoder.php';
require_once $root . '/app/code/Dacang/Helper/Prpcrypt.php';

function failTest($message)
{
    fwrite(STDERR, "FAIL: " . $message . PHP_EOL);
    exit(1);
}

function padForBlock($text, $blockSize)
{
    $padding = $blockSize - (strlen($text) % $blockSize);
    return $text . str_repeat(chr($padding), $padding);
}

$desKey = '12345678';
$desPlaintext = padForBlock('legacy-des-value', 8);
$tripleDesKey = $desKey . $desKey . $desKey;
$opensslDes = openssl_encrypt(
    $desPlaintext,
    'DES-EDE3',
    $tripleDesKey,
    OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
);
if ($opensslDes === false) {
    failTest('OpenSSL DES-EDE3 encryption is unavailable');
}

$aesKey = '0123456789abcdef0123456789abcdef';
$aesIv = substr($aesKey, 0, 16);
$aesPlaintext = padForBlock('legacy-aes-value', 32);
$opensslAes = openssl_encrypt(
    $aesPlaintext,
    'AES-256-CBC',
    $aesKey,
    OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
    $aesIv
);
if ($opensslAes === false) {
    failTest('OpenSSL AES-256-CBC encryption is unavailable');
}

if (function_exists('mcrypt_encrypt')) {
    $mcryptDes = mcrypt_encrypt(MCRYPT_DES, $desKey, $desPlaintext, MCRYPT_MODE_ECB);
    if (!hash_equals($mcryptDes, $opensslDes)) {
        failTest('DES output differs between mcrypt and OpenSSL');
    }

    $mcryptAes = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $aesKey, $aesPlaintext, MCRYPT_MODE_CBC, $aesIv);
    if (!hash_equals($mcryptAes, $opensslAes)) {
        failTest('AES output differs between mcrypt and OpenSSL');
    }
}

$encodingAesKey = rtrim(base64_encode($aesKey), '=');
$crypt = new \ScshuxCms\Dacang\Helper\Prpcrypt($encodingAesKey);
$encrypted = $crypt->encrypt('round-trip-content', 'test-corp');
if ($encrypted[0] !== 0 || empty($encrypted[1])) {
    failTest('Prpcrypt encryption failed');
}

$decrypted = $crypt->decrypt($encrypted[1], 'test-corp');
if ($decrypted[0] !== 0 || $decrypted[1] !== 'round-trip-content') {
    failTest('Prpcrypt round trip failed');
}

echo 'PASS: crypto compatibility checks' . PHP_EOL;
