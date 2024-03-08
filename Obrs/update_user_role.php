<?php

    include("../db.php");

    // Проверка соединения
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Получение данных из запроса
    $userID = $_POST['user_id']; // Предполагается, что вы передаете user_id через POST запрос
    $newRole = $_POST['role'];   // Получаем новую роль из формы

    // Защита от SQL-инъекций
    $userID = $conn->real_escape_string($userID);
    $newRole = $conn->real_escape_string($newRole);

    // Логика обновления роли пользователя в базе данных
    $sql = "UPDATE user_roles SET role = '$newRole' WHERE user_id = $userID";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../Admin/admin_change_roles.php"); // Возвращает пользователя на предыдущую страницу
    } else {
        echo "Ошибка при обновлении роли пользователя: " . $conn->error;
    }

    // Закрытие соединения с базой данных
    $conn->close();
?>