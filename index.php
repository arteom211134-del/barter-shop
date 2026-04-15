<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'config/db.php';


/*
    Берём вещи + пользователя
*/
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
<title>Barter Shop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>

<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark px-4">
    <span class="navbar-brand">Barter Shop</span>

    <div class="ms-auto">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="my_exchanges.php" class="btn btn-outline-light btn-sm">
                Мои обмены
            </a>

            <a href="add_item.php" class="btn btn-success btn-sm">
                + Добавить вещь
            </a>
            
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <a href="admin/admin_panel.php" class="btn btn-warning btn-sm">
        Админка
    </a>
<?php endif; ?>

            <a href="logout.php" class="btn btn-danger btn-sm">
                Выйти
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-sm">Войти</a>
            <a href="register.php" class="btn btn-success btn-sm">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container mt-4">

    <h3 class="mb-4"> Последние вещи</h3>

    <div class="row">

        <?php if(!empty($items)): ?>
            <?php foreach($items as $i): ?>
                <div class="col-md-4 mb-4">

                    <div class="card shadow-sm h-100">

                        <img src="uploads/<?= htmlspecialchars($i['image']) ?>"
                             class="card-img-top"
                             style="height:200px; object-fit:cover;"
                             onerror="this.src='https://via.placeholder.com/300'">

                        <div class="card-body">
                            <h5><?= htmlspecialchars($i['title']) ?></h5>
                            <p><?= htmlspecialchars($i['description']) ?></p>
                        </div>

                        <div class="card-footer bg-white">

                            <small class="text-muted">
                                 <?= htmlspecialchars($i['email']) ?>
                            </small>

<a href="exchange.php?id=<?= $i['id'] ?>" 
   class="btn btn-warning btn-sm w-100 mt-2">
    Предложить обмен
</a>
                                 <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $i['user_id']): ?>
    <a href="edit_item.php?id=<?= $i['id'] ?>" 
       class="btn btn-outline-primary btn-sm w-100 mt-2">
        Редактировать
    </a>
<?php endif; ?>
                            </a>

                        </div>

                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    Пока нет вещей
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>