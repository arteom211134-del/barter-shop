<?php
session_start();
require 'config/db.php';
require 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Проверка CSRF
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF-атака заблокирована");
    }

    $id = (int)$_POST['id'];

    // Soft Delete
    $stmt = $pdo->prepare("UPDATE products SET is_deleted = 1 WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: admin_panel.php");
    exit;
}
?>
