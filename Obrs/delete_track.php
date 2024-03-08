<?php
include("../db.php");

if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    // Проверяем, передан ли идентификатор трека для удаления
    if (isset($_GET['id'])) {
        $trackId = $_GET['id'];

        // Удаляем трек из базы данных
        $deleteSql = "DELETE FROM tracks WHERE id = $trackId";
        $deleteResult = $conn->query($deleteSql);

        if ($deleteResult) {
            // Успешное удаление, перенаправляем обратно на страницу редактирования треков
            header("Location: ../Admin/admin_edit_tracks.php");
            exit();
        } else {
            echo '<p class="error-message">Ошибка при удалении трека.</p>';
        }
    } else {
        echo '<p class="error-message">Не указан идентификатор трека для удаления.</p>';
    }
} else {
    // Если пользователь не авторизован как администратор, перенаправляем на главную страницу
    header("Location: ../index.php");
    exit();
}

$conn->close();
?>
