<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../config/db.php';
require '../core/auth.php';
require 'check_admin.php';

$message = "";

$tables = [];
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tableName = $_POST['table_name'];
    $count = (int)$_POST['count'];

    if (!in_array($tableName, $tables)) {
        die("Таблица не найдена");
    }

    $exportDir = '../exports/';
    if (!is_dir($exportDir)) mkdir($exportDir);

    $filename = $exportDir . $tableName . '_' . date('Y-m-d_H-i-s') . '.csv';
    $fp = fopen($filename, 'w');

    $stmt = $pdo->query("SELECT * FROM `$tableName`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        $message = "Таблица пуста!";
    } else {

        fputcsv($fp, array_keys($rows[0]));

        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);

        $message .= "Бэкап сохранен: $filename<br>";

        $template = $rows[array_rand($rows)];
        $inserted = 0;

        for ($i = 0; $i < $count; $i++) {

            $cols = [];
            $vals = [];

            foreach ($template as $key => $value) {

                if ($key === 'id') continue;

                if (is_numeric($value)) {
                    $percent = mt_rand(-15, 15) / 100;
                    $newValue = round($value * (1 + $percent), 2);
                } else {
                    $newValue = $value . '_' . mt_rand(1000, 9999);
                }

                $cols[] = "`$key`";
                $vals[] = $pdo->quote($newValue);
            }

            $sql = "INSERT INTO `$tableName` (" . implode(',', $cols) . ")
                    VALUES (" . implode(',', $vals) . ")";

            try {
                $pdo->exec($sql);
                $inserted++;
            } catch (Exception $e) {
                continue;
            }
        }

        $message .= "Сгенерировано строк: $inserted из $count.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Seeder</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-5 bg-light">

<div class="container">

<div class="card shadow">

<div class="card-header bg-primary text-white">
<h3>Seeder</h3>
</div>

<div class="card-body">

<?php if($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Таблица:</label>
<select name="table_name" class="form-select">
<?php foreach($tables as $t): ?>
<option value="<?= $t ?>"><?= $t ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="mb-3">
<label>Количество:</label>
<input type="number" name="count" class="form-control" value="10" min="1">
</div>

<button class="btn btn-success w-100">Запустить</button>

</form>

<a href="../index.php" class="btn btn-secondary mt-3">Назад</a>

</div>
</div>

</div>

</body>
</html>