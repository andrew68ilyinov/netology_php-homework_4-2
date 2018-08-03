<?php

# Local
$servername = 'localhost';
$user = 'root';
$passw = '';
$database = 'global';

# Host
//$servername = 'localhost';
//$username = 'ailinov';
//$password = 'neto1587';
//$database = 'global';

$pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8", $user, $passw);

if (!$pdo)
{
    die('Нет соединения!');
}

$selectAll = "SELECT * FROM tasks";
$addBtn = 'Добавить';
if($_GET) {
    $id = $_GET['id'];
    if ($_GET['action'] === 'delete') {
        $delPrepare = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $delPrepare->execute([$id]);
        $description = $delPrepare->fetch()['description'];
    }
    if ($_GET['action'] === 'done') {
        $donePrepare = $pdo->prepare("UPDATE tasks SET is_done = TRUE WHERE id = ? LIMIT 1");
        $donePrepare->execute([$id]);
        $description = $donePrepare->fetch()['description'];
    }
    if ($_GET['action'] === 'edit') {
        $idPrepare = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $idPrepare->execute([$id]);
        $description = $idPrepare->fetch()['description'];
        $addBtn = 'Сохранить';
    }
}
if (isset($_POST['add'])) {
    $myDescription = $_POST['description'];
    $id = $_POST['id'];
    if ($id) {
        $editPrepare = $pdo->prepare("UPDATE tasks SET description = ? WHERE id = ? LIMIT 1");
        $editPrepare->execute([$myDescription, $id]);
    } else {
        $addPrepare = $pdo->prepare("INSERT INTO tasks (description, is_done, date_added) VALUES (?, ?, CURRENT_TIMESTAMP)");
        $addPrepare->execute([$myDescription, false]);
    }
}
$mySort = ['description', 'date_added', 'is_done'];
if (isset($_POST['sort'])) {
    if(array_search($_POST['sortBy'], $mySort) !== false) {
        $sortBy = addslashes($_POST['sortBy']);
        $selectAll .= " ORDER BY $sortBy";
    }
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>ДЗ к занятию 4-2 "Запросы SELECT, INSERT, UPDATE и DELETE"</title>
    <style>
        table {
            margin-top: 10px;
            border-spacing: 0;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid #ccc;
            padding: 5px;
        th {
            background: #eee;
        }
    </style>
</head>

<body>
<h1>Список дел на сегодня</h1>

<div style="float: left">
    <form method="POST" action="/">
        <input type="text" name="description" placeholder="Описание задачи" value="<?= $_GET ? $description : "" ?>">
        <input type="submit" value="<?= $addBtn ?>" name="save">
    </form>
</div>

<div style="float: left; margin-left: 20px;">
    <form method="POST">
      <label for="sort">Сортировать по:</label>
        <select name="sort_by">
          <option value="date_added">Дате добавления</option>
          <option value="is_done">Статусу</option>
          <option value="description">Описанию</option>
        </select>
        <input type="submit" name="sort" value="Отсортировать">
    </form>
</div>
<div style="clear: both"></div>

<table>
    <tr>
        <th>Описание задачи</th>
        <th>Дата добавления</th>
        <th>Статус</th>
        <th></th>
    </tr>
    
    <?php
    
    $result = $pdo->prepare($selectAll);
    $result->execute();
    $mylist = $result->fetchAll();
    foreach ($mylist as $row) {
        $id = $row['id'];
        echo '<tr>
        <td>' . $row['description'] . '</td>
        <td>' . $row['date_added'] . '</td>
        <td>';
        if (intval($row['is_done']) === 1) {
            echo '<span style="color: green">Выполнено</span>';
        } elseif (intval ($row['is_done']) === 0) {
            echo '<span style="color: orange">В процессе</span>';
        } else
            echo '<span style="color: red">В неопределенном состоянии</span>';
        echo '</td>
            <td><a href="index.php?id=' . $id . '&action=edit">Редактировать</a>
              <a href="index.php?id=' . $id . '&action=done">Выполнить</a>
              <a href="index.php?id=' . $id . '&action=delete">Удалить</a></td>';
    }
    ?>
</table>

</body>
</html>