<?php

header('Content-Type: text/plain; charset=utf-8');

echo 'PHP: ' . PHP_VERSION . PHP_EOL;
echo 'php.ini: ' . (php_ini_loaded_file() ?: 'не найден') . PHP_EOL;
echo 'OpenSSL: ' . (extension_loaded('openssl') ? 'включён' : 'выключен') . PHP_EOL;

if (!function_exists('openssl_get_cipher_methods')) {
    exit('Функция openssl_get_cipher_methods недоступна');
}

echo 'Версия: ' . OPENSSL_VERSION_TEXT . PHP_EOL;

$ciphers = array_map('strtolower', openssl_get_cipher_methods());

echo 'AES-256-CBC: '
    . (in_array('aes-256-cbc', $ciphers, true) ? 'доступен' : 'недоступен')
    . PHP_EOL;