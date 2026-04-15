<?php
header('Content-Type: text/html; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config/db.php';
require 'core/auth.php';
require 'core/csrf.php';

checkAuth();

/* Только POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный запрос");
}

/* CSRF */
if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf']) ||
    !hash_equals($_SESSION['csrf'], $_POST['csrf_token'])
) {
    die("CSRF ошибка");
}

$id = (int)$_POST['id'];

/* Проверка обмена */
$stmt = $pdo->prepare("
    SELECT * FROM exchanges 
    WHERE id = ? AND to_user = ? AND status = 'pending'
");
$stmt->execute([$id, $_SESSION['user_id']]);

$exchange = $stmt->fetch();

if (!$exchange) {
    die("Нет доступа");
}

/* 1. обновляем статус */
$pdo->prepare("
    UPDATE exchanges 
    SET status = 'accepted' 
    WHERE id = ?
")->execute([$id]);

/* 2. удаляем товары */
$pdo->prepare("
    DELETE FROM items 
    WHERE id IN (?, ?)
")->execute([
    $exchange['offered_item'],
    $exchange['requested_item']
]);

header("Location: /my_exchanges.php");
exit;