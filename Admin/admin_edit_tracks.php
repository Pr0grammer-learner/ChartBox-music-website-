<?php
session_start();

if (isset($_SESSION['user_id'])) {
    include("../db.php");

    // Получение роли пользователя из базы данных
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT role FROM user_roles WHERE user_id = '$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_role = $row['role'];

        // Проверка на блокировку
        if ($user_role === 'banned') {
            // Перенаправление на страницу с сообщением о блокировке
            header("Location: ../banned_page.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование треков</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../styles/admin.css">
    <link rel="icon" href="../icons/website-icon.png" type="image/png">
</head>
<body>
    <header>
        <a href="admin_panel.php" id="site-icon">
            <!-- Добавляем иконку перед названием сайта -->
            <img src="../icons/website-icon.png" alt="Site Icon">
        </a>

        <a id="logo" href="../admin_panel.php">
            <h1>Chartbox Admin</h1>
        </a>
    </header>

    <?php
        include("../db.php");

        // Проверяем, авторизован ли пользователь как администратор
        if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
            include("admin_nav.php");

            // Обработка поиска
            $search = isset($_POST['search']) ? $_POST['search'] : '';

            // Получаем треки из базы данных с учетом поиска
            $sql = "SELECT * FROM tracks WHERE title LIKE '%$search%' OR artist LIKE '%$search%'";
            $result = $conn->query($sql);

            // Выводим форму поиска
            echo '<form method="post" class="search-form">
                    <input type="text" name="search" placeholder="Поиск по названию трека или исполнителю">
                    <button type="submit">Поиск</button>
                 </form>';

            if ($result->num_rows > 0) {
                echo '<table class="admin-table">
                        <tr>
                            <th>№</th>
                            <th>Название трека</th>
                            <th>Исполнитель</th>
                            <th>Длительность</th>
                            <th>Путь к картинке</th>
                            <th>Путь к аудио</th>
                            <th>Редактировать</th>
                            <th>Удалить</th>
                        </tr>';

                $counter = 1;
                while($row = $result->fetch_assoc()) {
                    echo '<tr>
                            <td>' . $counter . '</td>
                            <td>' . htmlspecialchars($row['title']) . '</td>
                            <td>' . htmlspecialchars($row['artist']) . '</td>
                            <td>' . formatDuration($row['duration']) . '</td>
                            <td>' . htmlspecialchars($row['cover']) . '</td>
                            <td>' . htmlspecialchars($row['audio']) . '</td>
                            <td><a href="admin_edit_track.php?id=' . $row['id'] . '" class="edit-link">Редактировать</a></td>
                            <td><a href="../Obrs/delete_track.php?id=' . $row['id'] . '" class="delete-link" onclick="return confirm(\'Вы уверены, что хотите удалить трек?\')">Удалить</a></td>
                          </tr>';
                    $counter++;
                }

                echo '</table>';
            } else {
                echo '<p class="error-message">Нет треков в базе данных.</p>';
            }
        } else {
            // Если пользователь не авторизован как администратор, перенаправляем на главную страницу
            header("Location: ../index.php");
            exit();
        }

        // Закрываем соединение с базой данных
        $conn->close();

        // Функция для форматирования длительности трека
        function formatDuration($seconds) {
            $minutes = floor($seconds / 60);
            $seconds = $seconds % 60;
            return sprintf("%02d:%02d", $minutes, $seconds);
        }
    ?>
</body>
</html>