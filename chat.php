<?php
header('Content-Type: text/html; charset=utf-8');

require 'config/db.php';
require 'core/auth.php';

checkAuth();

$exchange_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

/*
    Проверяем доступ к чату
*/
$check = $pdo->prepare("
    SELECT * FROM exchanges
    WHERE id = ? AND (from_user = ? OR to_user = ?)
");
$check->execute([$exchange_id, $user_id, $user_id]);

if(!$check->fetch()) {
    die("Доступ запрещён");
}

/*
    Отправка сообщения
*/
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg'])) {

    $msg = trim($_POST['msg']);

    if($msg !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO messages (exchange_id, sender_id, message, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$exchange_id, $user_id, $msg]);
    }

    header("Location: chat.php?id=".$exchange_id);
    exit;
}

/*
    Получаем сообщения
*/
$msgs = $pdo->prepare("
    SELECT m.*, u.email
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.exchange_id = ?
    ORDER BY m.id ASC
");
$msgs->execute([$exchange_id]);
$messages = $msgs->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Чат обмена</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.chat-box {
    height: 400px;
    overflow-y: auto;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
}

.msg {
    margin-bottom: 10px;
    padding: 8px 12px;
    border-radius: 10px;
    max-width: 70%;
}

.me {
    background: #d1e7dd;
    margin-left: auto;
    text-align: right;
}

.other {
    background: #ffffff;
}
</style>
</head>

<body class="bg-light">

<div class="container mt-4">

    <h3>💬 Чат обмена #<?= $exchange_id ?></h3>

    <a href="my_exchanges.php" class="btn btn-secondary btn-sm mb-3">
        ← Назад
    </a>

    <!-- CHAT -->
    <div id="chat" class="chat-box">

        <?php foreach($messages as $m): ?>

            <?php $isMe = ($m['sender_id'] == $user_id); ?>

            <div class="msg <?= $isMe ? 'me' : 'other' ?>">

                <small>
                    <b><?= htmlspecialchars($m['email']) ?></b>
                </small>
                <br>

                <?= htmlspecialchars($m['message']) ?>

            </div>

        <?php endforeach; ?>

    </div>

    <!-- FORM -->
    <form method="POST" class="mt-3 d-flex gap-2">

        <input type="text"
               name="msg"
               class="form-control"
               placeholder="Напишите сообщение..."
               autocomplete="off"
               required>

        <button class="btn btn-primary">
            ➤
        </button>

    </form>

</div>

<!-- AUTO SCROLL -->
<script>
let chat = document.getElementById("chat");
chat.scrollTop = chat.scrollHeight;
</script>

</body>
</html>