<?php
require 'config/db.php';
require 'core/auth.php';

checkAuth();

$stmt = $pdo->prepare("
    SELECT e.*,
           i1.title AS offered_title,
           i2.title AS requested_title,
           u1.email AS from_email,
           u2.email AS to_email
    FROM exchanges e
    JOIN items i1 ON e.offered_item = i1.id
    JOIN items i2 ON e.requested_item = i2.id
    JOIN users u1 ON e.from_user = u1.id
    JOIN users u2 ON e.to_user = u2.id
    WHERE e.from_user = ? OR e.to_user = ?
    ORDER BY e.id DESC
");

$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$exchanges = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Мои обмены</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

    <h2 class="mb-4">Мои обмены</h2>

    <a href="index.php" class="btn btn-secondary mb-3">← Назад</a>

    <?php if(empty($exchanges)): ?>
        <div class="alert alert-info">
            У вас пока нет обменов
        </div>
    <?php else: ?>

        <div class="row">

        <?php foreach($exchanges as $e): ?>

            <div class="col-md-6 mb-3">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h5 class="card-title">
                            Обмен #<?= $e['id'] ?>
                        </h5>

                        <p class="mb-1">
                            <b>Вы предлагаете:</b> <?= htmlspecialchars($e['offered_title']) ?>
                        </p>

                        <p class="mb-1">
                            <b>В обмен на:</b> <?= htmlspecialchars($e['requested_title']) ?>
                        </p>

                        <p class="mb-2 text-muted">
                            От: <?= htmlspecialchars($e['from_email']) ?> → 
                            Кому: <?= htmlspecialchars($e['to_email']) ?>
                        </p>

                        <!-- СТАТУС -->
                        <?php if($e['status'] === 'pending'): ?>
                            <span class="badge bg-warning text-dark">Ожидает</span>
                        <?php elseif($e['status'] === 'accepted'): ?>
                            <span class="badge bg-success">Принят</span>
                        <?php elseif($e['status'] === 'declined'): ?>
                            <span class="badge bg-danger">Отклонён</span>
                        <?php endif; ?>

                        <!-- КНОПКИ -->
                        <div class="mt-3">

                            <!-- ЧАТ (ВСЕГДА ДОСТУПЕН) -->
                            <a href="chat.php?id=<?= $e['id'] ?>" 
                               class="btn btn-primary btn-sm w-100 mb-2">
                                Открыть чат
                            </a>

                            <!-- ПРИНЯТЬ / ОТКЛОНИТЬ -->
                            <?php if($e['status'] === 'pending' && $e['to_user'] == $_SESSION['user_id']): ?>

                                <form method="POST" action="accept_exchange.php">
                                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf'] ?>">

                                    <button class="btn btn-success btn-sm w-100 mb-2">
                                        Принять обмен
                                    </button>
                                </form>

                                <form method="POST" action="decline_exchange.php">
                                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf'] ?>">

                                    <button class="btn btn-danger btn-sm w-100">
                                        Отклонить
                                    </button>
                                </form>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

</body>
</html>