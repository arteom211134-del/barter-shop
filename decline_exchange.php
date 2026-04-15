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

/* Проверка доступа */
$stmt = $pdo->prepare("
    SELECT id 
    FROM exchanges 
    WHERE id = ? AND to_user = ?
");
$stmt->execute([$id, $_SESSION['user_id']]);

if (!$stmt->fetch()) {
    die("Нет доступа");
}

/* отклонение */
$pdo->prepare("
    UPDATE exchanges 
    SET status = 'declined' 
    WHERE id = ?
")->execute([$id]);

header("Location: /my_exchanges.php");
exit;