<?php
echo '<pre>';
echo 'PHP: ' . PHP_VERSION . "\n";
echo 'php.ini: ' . (php_ini_loaded_file() ?: 'не загружен') . "\n";
echo 'Xdebug: ' . (phpversion('xdebug') ?: 'не загружен') . "\n";
echo 'Xdebug mode: ' . ini_get('xdebug.mode') . "\n";
echo 'Xdebug port: ' . ini_get('xdebug.client_port') . "\n";
echo '</pre>';
echo '</pre>';
