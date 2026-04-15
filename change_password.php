<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF ошибка");
    }

    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $repeat = $_POST['repeat_password'];

    if (strlen($new) < 8) {
        $error = "Пароль минимум 8 символов";
    } elseif ($new !== $repeat) {
        $error = "Пароли не совпадают";
    } else {

        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($old, $user['password_hash'])) {
            $error = "Старый пароль неверный";
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
            $upd->execute([$hash, $_SESSION['user_id']]);
            $success = "Пароль изменён";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Смена пароля</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Смена пароля</h4>
                </div>
                <div class="card-body">

                    <!-- Вывод сообщений -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <!-- Форма смены пароля -->
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Старый пароль</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Повтор нового пароля</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Сменить пароль</button>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="profile.php">Назад в личный кабинет</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>