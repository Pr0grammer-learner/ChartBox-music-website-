<?php
session_start();
include("../db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    $user_id = $_POST['user_id'];

    // Обновляем роль пользователя на 'banned'
    $sql = "UPDATE user_roles SET role = 'banned' WHERE user_id = '$user_id'";
    $result = $conn->query($sql);

    if ($result) {
        // Успешное выполнение запроса
        header("Location: ../Admin/admin_block_users.php");
        exit();
    } else {
        // Ошибка при выполнении запроса
        echo "Ошибка при блокировке пользователя.";
    }
} else {
    // Если запрос не является POST-запросом или пользователь не авторизован как администратор
    header("Location: ../index.php");
    exit();
}
?>
