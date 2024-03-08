<?php
    session_start();

    // Уничтожаем все сессии
    session_destroy();

    // Перенаправляем пользователя на страницу входа (можно изменить на любую другую страницу)
    header('Location: ../index.php');
    exit();
?>
