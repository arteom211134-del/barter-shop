<?php
session_start();
require 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $pass  = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email && $pass) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && isset($user['password_hash']) && $user['password_hash'] != '') {
                if (password_verify($pass, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = isset($user['role']) ? $user['role'] : 'user';

                    if ($user['role'] === 'admin') {
                        header("Location: /admin/admin_panel.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                } else {
                    $error = 'Неверный логин или пароль';
                }
            } else {
                $error = 'Неверный логин или пароль';
            }

        } catch (Exception $e) {
            // Сохраняем ошибку в лог
            file_put_contents('error_log.txt', date('Y-m-d H:i:s').' '.$e->getMessage().PHP_EOL, FILE_APPEND);
            $error = 'Произошла ошибка, попробуйте позже';
        }
    } else {
        $error = 'Заполните все поля';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Вход</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

<h3>Вход</h3>

<?php if ($error != ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post">
    <input class="form-control mb-2" name="email" type="email" placeholder="Email" required>
    <input class="form-control mb-2" name="password" type="password" placeholder="Пароль" required>
    <button class="btn btn-dark">Войти</button>
</form>

</body>
</html>
