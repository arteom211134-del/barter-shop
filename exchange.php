<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'config/db.php';
require 'core/auth.php';

checkAuth();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Неверный товар");
}

$item_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

/*
    Получаем вещь, которую хотят обменять
*/
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Вещь не найдена");
}

/*
    Нельзя обмениваться на свою вещь
*/
if ($item['user_id'] == $user_id) {
    header('Content-Type: text/html; charset=utf-8');
die("Нельзя обмениваться на свою вещь");
}

/*
    Получаем мои вещи
*/
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ?");
$stmt->execute([$user_id]);
$my_items = $stmt->fetchAll();

/*
    Создание заявки
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['my_item'])) {
        die("Выберите вещь");
    }

    $my_item = (int)$_POST['my_item'];

    /*
        Создаём обмен
    */
    $stmt = $pdo->prepare("
        INSERT INTO exchanges 
        (from_user, to_user, offered_item, requested_item)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $item['user_id'],
        $my_item,
        $item_id
    ]);

    header("Location: my_exchanges.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Обмен</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-body">

            <h3> Предложить обмен</h3>

            <p>
                Вы хотите получить: <b><?= htmlspecialchars($item['title']) ?></b>
            </p>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Выберите вашу вещь</label>

                    <select name="my_item" class="form-control" required>
                        <?php foreach($my_items as $m): ?>
                            <option value="<?= $m['id'] ?>">
                                <?= htmlspecialchars($m['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                </div>

                <button class="btn btn-warning w-100">
                     Отправить предложение
                </button>

            </form>

            <a href="index.php" class="btn btn-secondary w-100 mt-3">
                ← Назад
            </a>

        </div>

    </div>

</div>

</body>
</html>