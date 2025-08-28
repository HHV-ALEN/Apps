<?php
// AES 256-CBC
define('MAIL_SECRET_KEY', 'TuClaveDe32CaracteresSegura!!!'); // 32 chars
define('MAIL_SECRET_IV', 'Inicializa123456'); // ✅ Ejemplo de 16

function encryptPassword($password) {
    $output = openssl_encrypt($password, "AES-256-CBC", MAIL_SECRET_KEY, 0, MAIL_SECRET_IV);
    return base64_encode($output);
}

function decryptPassword($encrypted) {
    $output = openssl_decrypt(base64_decode($encrypted), "AES-256-CBC", MAIL_SECRET_KEY, 0, MAIL_SECRET_IV);
    return $output;
}