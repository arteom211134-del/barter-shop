<?php
require 'config/db.php';
require 'core/auth.php';

checkAuth();

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

/* Получаем вещь */
$stmt = $pdo->prepare("SELECT * FROM items WHERE id=? AND user_id=?");
$stmt->execute([$id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Нет доступа");
}

/* Обновление */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);

    $stmt = $pdo->prepare("
        UPDATE items SET title=?, description=? WHERE id=?
    ");
    $stmt->execute([$title, $desc, $id]);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Редактировать</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
<div class="card p-4 shadow">

<h3> Редактировать вещь</h3>

<form method="POST">

<div class="mb-3">
<label>Название</label>
<input name="title" class="form-control" value="<?= htmlspecialchars($item['title']) ?>">
</div>

<div class="mb-3">
<label>Описание</label>
<textarea name="description" class="form-control"><?= htmlspecialchars($item['description']) ?></textarea>
</div>

<button class="btn btn-primary w-100">Сохранить</button>

</form>

</div>
</div>

</body>
</html>
