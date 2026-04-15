<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
    Генерация CSRF токена
*/
if (empty($_SESSION['csrf'])) {

    // PHP 7+
    if (function_exists('random_bytes')) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    // Beget fallback (обычно доступен)
    elseif (function_exists('openssl_random_pseudo_bytes')) {
        $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
    }

    // самый старый fallback
    else {
        $_SESSION['csrf'] = sha1(uniqid(mt_rand(), true));
    }
}

/*
    Гарантируем, что токен всегда строка
*/
$_SESSION['csrf'] = (string)$_SESSION['csrf'];