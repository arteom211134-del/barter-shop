<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../config/db.php';
require '../core/auth.php';
require 'check_admin.php';

checkAuth();

if ($_SESSION['user_role'] !== 'admin') {
    die("Нет доступа");
}

/* УДАЛЕНИЕ */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM items WHERE id=?");
    $stmt->execute([$id]);

    header("Location: admin_panel.php");
    exit;
}

/* СПИСОК */
$stmt = $pdo->query("
    SELECT items.*, users.email 
    FROM items
    JOIN users ON items.user_id = users.id
    ORDER BY items.id DESC
");

$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Админка</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

<h2>Админка — вещи</h2>

<a href="../index.php" class="btn btn-secondary mb-3">← Назад</a>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Название</th>
<th>Пользователь</th>
<th>Действия</th>
</tr>

<?php foreach($items as $i): ?>
<tr>
<td><?= $i['id'] ?></td>
<td><?= htmlspecialchars($i['title']) ?></td>
<td><?= htmlspecialchars($i['email']) ?></td>

<td>
<a href="?delete=<?= $i['id'] ?>" 
   class="btn btn-danger btn-sm"
   onclick="return confirm('Удалить?')">
   Удалить
</a>
</td>

</tr>
<?php endforeach; ?>

</table>

</div>

</body>
</html>