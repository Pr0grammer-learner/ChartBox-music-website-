<?php
session_start();

if (isset($_SESSION['user_id'])) {
    include("db.php");

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
            header("Location: banned_page.php");
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
    <title>Музыкальный Сайт</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="icon" href="/icons/website-icon.png" type="image/png">
</head>
<body>
    <header>
         <!-- Добавляем иконку перед названием сайта -->
        <a href="index.php" id="site-icon">
            <img src="icons/website-icon.png" alt="Site Icon">
        </a>

        <a id="logo" href="index.php"><h1>Chartbox</h1></a>
    </header>

    <?php
        include("db.php");
        // Проверяем, авторизован ли пользователь
        if (isset($_SESSION['user_id'])) {
            // Проверяем роль пользователя и отображаем соответствующую навигацию
            if ($_SESSION['role'] == 'user') {
                echo '<nav>
                        <a href="index.php">Главная</a>
                        <a href="search.php">Поиск</a>
                        <a href="user_page.php">Моя страница</a>
                        <a href="Obrs/logout.php">Выйти</a>
                    </nav>';
            } elseif ($_SESSION['role'] == 'admin') {
                echo '<nav>
                        <a href="index.php">Главная</a>
                        <a href="search.php">Поиск</a>
                        <a href="admin_panel.php">Панель администратора</a>
                        <a href="user_page.php">Моя страница</a>
                        <a href="Obrs/logout.php">Выйти</a>
                    </nav>';
            }
            else {
                // Отображаем стандартную навигацию для неавторизованных пользователей
                echo '<nav>
                        <a href="index.php">Главная</a>
                        <a href="search.php">Поиск</a>
                        <a href="Auth/auth.php">Войти/зарегистрироваться</a>
                    </nav>';
            }
        } 
        else {
            // Отображаем стандартную навигацию для неавторизованных пользователей
            echo '<nav>
                    <a href="index.php">Главная</a>
                    <a href="search.php">Поиск</a>
                    <a href="Auth/auth.php">Войти/зарегистрироваться</a>
                </nav>';
        }
    ?>

    <main>
        <section class="playlist">
            <h2>Популярные песни</h2>
            <?php
                include("db.php");
                
                // Проверяем соединение
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                
                // Запрос к базе данных для получения треков
                $sql = "SELECT * FROM tracks";
                $result = $conn->query($sql);
                
                // Выводим карточки треков
                if ($result->num_rows > 0) {
                    $index = 0;
                    while ($row = $result->fetch_assoc()) {
                        // Генерируем уникальный ID для карточки
                        $trackCardId = 'track-card-' . $row['id'];
                    
                        echo '<div class="track-card" id="' . $trackCardId . '"
                                data-track-id="' . $row['id'] . '"
                                data-title="' . htmlspecialchars($row['title']) . '"
                                data-artist="' . htmlspecialchars($row['artist']) . '"
                                data-image="' . htmlspecialchars($row['cover']) . '"
                                data-index="' . $index . '">'; // Добавляем data-index
                    
                        echo '<div class="cover-container">';
                        echo '<img src="' . $row['cover'] . '" alt="Cover">';
                        echo '<button class="play-button" data-audio="' . $row['audio'] . '" onclick="playTrack(this, \'' . $trackCardId . '\')">';
                        echo '<img id="icon-button-play" src="icons/play.png" alt="Play">';
                        echo '</button>';
                        
                        echo '</button>';
                        echo '</div>';
                        
                        echo '<div class="track-details">';
                        echo '<h3>' . $row['title'] . '</h3>';
                        echo '<p class="transparent-text">' . $row['artist'] . '</p>';
                        echo '</div>';

                        // Добавляем форму с кнопкой "Добавить" и скрытым полем track_id
                        echo '<form class="add-track-form" action="Obrs/add_remove_track.php" method="post">';
                        echo '<input type="hidden" name="track_id" value="' . $row['id'] . '">';
                        echo '<button type="submit" id="add-remove-button" aria-label="Добавить">';
                        // Проверяем, добавлен ли трек
                        $trackAdded = isTrackAdded($_SESSION['user_id'], $row['id'], $conn);

                        if ($trackAdded) {
                            // Если трек добавлен, используем иконку "like-track"
                            echo '<img src="icons/like-track.png" alt="Добавить">';
                        } else {
                            // Если трек не добавлен, используем иконку "like"
                            echo '<img src="icons/like.png" alt="Добавить">';
                        }
                        echo '</button>';
                        echo '</form>';
                        echo '<p class="duration">' . formatDuration($row['duration']) . '</p>';
                        echo '</div>';

                        $index++;
                    }}
                else {
                    echo "0 results";
                }
                
                // Закрываем соединение
                $conn->close();
                
                // Функция для форматирования длительности трека
                function formatDuration($seconds) {
                    $minutes = floor($seconds / 60);
                    $seconds = $seconds % 60;
                    return sprintf("%02d:%02d", $minutes, $seconds);
                }

                function isTrackAdded($user_id, $track_id, $conn) {
                    // Проверяем, что пользователь авторизован
                    if ($user_id === null) {
                        return false;
                    }
                
                    $stmt = $conn->prepare("SELECT * FROM music_" . $user_id . " WHERE track_id = ?");
                    $stmt->bind_param("i", $track_id);
                    $stmt->execute();
                    $stmt->store_result();
                    $result = $stmt->num_rows > 0;
                    $stmt->close();
                    return $result;
                }

            ?>
        </section>
    </main>
    <div id="player-container">
        <div id="track-info">
            <img id="track-image" src="" alt="Track Image">
            <div class="details">
                <h4 id="track-title"></h4>
                <p id="track-artist" class="transparent-text"></p>
            </div>
            <form id="addRemoveForm" action="Obrs/add_remove_track.php" method="post">
                <input type="hidden" name="track_id" id="trackIdInput" value="">
                <button type="submit" id="add-remove-button" aria-label="Добавить">
                    <img src="icons/like.png" alt="Добавить">
                </button>
            </form>

        </div>
        <div id="center-player">
        <div id="center-controls">
            <img id="prev-icon" src="icons/prev.png" alt="Предыдущий трек" onclick="playPrevTrack()">
            <img id="play-pause-button" src="icons/play.png" alt="Play">
            <img id="next-icon" src="icons/next.png" alt="Следующий трек" onclick="playNextTrack()">
        </div>


            <div id="progress-container">
                <div id="time-left">00:00</div>
                <progress id="progress-bar" value="0" max="100"></progress>
                <div id="time-right">00:00</div>
            </div>
        </div>

        <div id="volume-container">
            <button id="volume-button">
                <img id="volume-icon" src="icons/volume.png" alt="Громкость">
            </button>
            <input type="range" id="volume-control" min="0" max="1" step="0.01" value="1">
        </div>

    </div>

<audio id="audio-player"></audio>

<script src="script.js"></script>
</body>
</html>
