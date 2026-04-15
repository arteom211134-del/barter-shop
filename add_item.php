<?php
require 'config/db.php';
require 'core/auth.php';

checkAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    //  проверка файла
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        $error = "Загрузите изображение";
    }

    // валидация
    elseif ($title === '' || $description === '') {
        $error = "Заполните все поля";
    }

    else {
        $allowed = ['image/jpeg','image/png','image/webp'];

        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowed)) {
            $error = "Разрешены только JPG, PNG, WEBP";
        } else {

            //  уникальное имя файла
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newName = uniqid() . '.' . $ext;

            $uploadPath = 'uploads/' . $newName;

            move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);

            $stmt = $pdo->prepare("
                INSERT INTO items (user_id, title, description, image)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                $title,
                $description,
                $newName
            ]);

            $success = "Вещь успешно добавлена!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Добавить вещь</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Добавить вещь</h4>
                </div>

                <div class="card-body">

                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">Название</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Описание</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Фото</label>
                            <input type="file" name="image" class="form-control" required>
                        </div>

                        <button class="btn btn-success w-100">
                             Добавить вещь
                        </button>

                    </form>

                    <a href="index.php" class="btn btn-secondary w-100 mt-3">
                        ← Назад
                    </a>

                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>