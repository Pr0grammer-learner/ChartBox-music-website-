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

<?php
include("../db.php");

if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    // Проверяем, передан ли идентификатор трека
    if (isset($_GET['id'])) {
        $trackId = $_GET['id'];

        // Получаем данные трека из базы данных
        $sql = "SELECT * FROM tracks WHERE id = $trackId";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $track = $result->fetch_assoc();

            // Здесь будет код для обработки формы при отправке
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Обработка формы при отправке
                $title = $_POST['title'];
                $artist = $_POST['artist'];
                $duration = $_POST['duration'];

                // Добавьте обработку загрузки и сохранения файлов, если необходимо

                // Обновляем данные трека в базе данных
                $updateSql = "UPDATE tracks SET title='$title', artist='$artist', duration='$duration' WHERE id=$trackId";
                $updateResult = $conn->query($updateSql);

                if ($updateResult) {
                    echo '<p class="success-message">Изменения сохранены.</p>';
                } else {
                    echo '<p class="error-message">Ошибка при сохранении изменений.</p>';
                }
            }
        } else {
            echo '<p class="error-message">Трек не найден.</p>';
        }
    } else {
        echo '<p class="error-message">Не указан идентификатор трека.</p>';
    }
} else {
    // Если пользователь не авторизован как администратор, перенаправляем на главную страницу
    header("Location: ../index.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование трека</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" href="./icons/website-icon.png" type="image/png">
    <link rel="stylesheet" href="../styles/admin.css">
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

    <?php include("admin_nav.php"); ?>

    <main>
        <section class="admin-form">
            <h2>Редактировать трек</h2>
            <form action="admin_edit_track.php?id=<?php echo $trackId; ?>" method="post" enctype="multipart/form-data">
                <label for="title">Название трека:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($track['title']); ?>" required>

                <label for="artist">Исполнитель:</label>
                <input type="text" id="artist" name="artist" value="<?php echo htmlspecialchars($track['artist']); ?>" required>

                <label for="duration">Продолжительность (в секундах):</label>
                <input type="number" id="duration" name="duration" value="<?php echo htmlspecialchars($track['duration']); ?>" required>

                <button type="submit">Сохранить изменения</button>
                <a href="admin_edit_tracks.php" class="back-link">Вернуться назад</a>
            </form>
        </section>
    </main>

    <script src="script.js"></script>
</body>
</html>