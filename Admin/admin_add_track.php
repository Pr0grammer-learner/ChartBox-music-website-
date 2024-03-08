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
    <title>Admin Add Track</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/admin.css">
    <link rel="icon" href="../icons/website-icon.png" type="image/png">
</head>
<body>
    <header>
        <a href="../admin_panel.php" id="site-icon">
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
        if (((isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin')) || (isset($_SESSION['user_id']) && $_SESSION['valid'] == 1))  {
                
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Обработка формы при отправке
                $title = $_POST['title'];
                $artist = $_POST['artist'];
                $duration = $_POST['duration'];

                // Проверяем, загружен ли файл обложки
                if ($_FILES['cover']['error'] == UPLOAD_ERR_OK) {
                    $coverFileName = $_FILES['cover']['name'];
                    $coverTmpName = $_FILES['cover']['tmp_name'];
                    $coverPath = '../cover/' . $coverFileName;

                    // Перемещаем загруженный файл в папку "covers"
                    move_uploaded_file($coverTmpName, $coverPath);
                }

                // Проверяем, загружен ли аудиофайл
                if ($_FILES['audio']['error'] == UPLOAD_ERR_OK) {
                    $audioFileName = $_FILES['audio']['name'];
                    $audioTmpName = $_FILES['audio']['tmp_name'];
                    $audioPath = '../music/' . $audioFileName;

                    // Перемещаем загруженный аудиофайл в папку "music"
                    move_uploaded_file($audioTmpName, $audioPath);
                }

                // Добавляем информацию о треке в базу данных
                $sql = "INSERT INTO tracks (title, artist, duration, cover, audio) VALUES ('$title', '$artist', '$duration', '$coverPath', '$audioPath')";
                $result = $conn->query($sql);

                if ($result) {
                    echo '<p class="success-message">Трек успешно добавлен в базу данных.</p>';
                } else {
                    echo '<p class="error-message">Ошибка при добавлении трека в базу данных.</p>';
                }
            }
        } else {
            // Если пользователь не авторизован как администратор, перенаправляем на главную страницу
            header("Location: index.php");
            exit();
        }
    ?>

    <main>
        <section class="admin-form">
            <h2>Добавить трек</h2>
            <form action="admin_add_track.php" method="post" enctype="multipart/form-data">
                <label for="title">Название трека:</label>
                <input type="text" id="title" name="title" required>

                <label for="artist">Исполнитель:</label>
                <input type="text" id="artist" name="artist" required>

                <label for="duration">Длительность (в секундах):</label>
                <input type="number" id="duration" name="duration" required>

                <label for="cover">Обложка (изображение):</label>
                <input type="file" id="cover" name="cover" accept="image/*" required>

                <label for="audio">Аудиофайл:</label>
                <input type="file" id="audio" name="audio" accept="audio/*" required>

                <input type="hidden" name="popka" value=<?php $email_valid?>>
                <?php echo $email_valid?>
                <button type="submit">Добавить трек</button>
            </form>
        </section>
    </main>
</body>
</html>
