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
    <script src="script.js"></script>
    <style>
        
        h2 {
            margin: 0;
        }

        main {
            display: flex;
        }

        .user-info {
            width: 30%;
            padding: 20px;
            border-right: 2px solid #333; /* Цвет и толщина вертикальной полосы */
        }

        .collection {
            width: 70%;
            padding: 20px;
        }

        .user-info h2 {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
    </style>
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
        } else {
            // Отображаем стандартную навигацию для неавторизованных пользователей
            echo '<nav>
                    <a href="index.php">Главная</a>
                    <a href="search.php">Поиск</a>
                    <a href="Auth/auth.php">Войти/зарегистрироваться</a>
                </nav>';
        }
    ?>

    <main>
        <div class="user-info">
            <h2>О себе</h2>
            <?php
                // Выводим информацию о пользователе
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];

                    // Подключение к базе данных
                    include("db.php");

                    // Запрос к базе данных для получения информации о пользователе
                    $sql = "SELECT * FROM users WHERE id = $user_id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        // Получаем информацию о пользователе
                        $user = $result->fetch_assoc();
                        $username = $user['login'];
                        $email = $user['email'];
                        $email_valid = $user['valid_email'];
                        
                        // Выводим информацию
                        echo "<p>Username: $username</p>";
                        echo "<p>Email: $email</p>";
                        if ($email_valid == 1) {
                            $_SESSION["valid"] = 1;
                            echo '<a href="Admin/admin_add_track.php?valid=1">Загрузить трек</a>';
                        }
                        echo '<form action="Obrs/logout.php" method="post">';
                        echo '<button type="submit">Выйти</button>';
                        echo '</form>';
                    } else {
                        echo "Не удалось получить информацию о пользователе";
                    }

                    // Закрываем соединение с базой данных
                    $conn->close();
                } else {
                    echo "Вы не авторизованы";
                }
            ?>
        </div>

        <div class="collection">
    <h2>Моя коллекция</h2>
    <?php
        // Подключение к базе данных
        include("db.php");

        // Выводим карточки треков из коллекции пользователя
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];

            // Проверяем, есть ли у пользователя список добавленных треков
            $user_music_table = "music_" . $user_id;
            $check_table_sql = "SHOW TABLES LIKE '$user_music_table'";
            
            // Проверяем подключение к базе данных перед выполнением запроса
            if ($conn->ping()) {
                $table_exists = $conn->query($check_table_sql)->num_rows > 0;

                if ($table_exists) {
                    // Получаем треки из коллекции пользователя
                    $sql = "SELECT * FROM $user_music_table";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $index = 0; // Инициализируем индекс для data-index
                        while ($row = $result->fetch_assoc()) {
                            // Получаем информацию о треке из таблицы tracks
                            $track_id = $row['track_id'];
                            $track_sql = "SELECT * FROM tracks WHERE id = $track_id";
                            $track_result = $conn->query($track_sql);

                            if ($track_result->num_rows > 0) {

                                $track = $track_result->fetch_assoc();
                                // Генерируем уникальный ID для карточки
                                $trackCardId = 'track-card-' . $track['id'];

                                // Используйте PHP-блок для вывода HTML-кода
                                echo '<div class="track-card" id="' . $trackCardId . '" 
                                data-track-id="' . $track['id'] . '" 
                                data-title="' . htmlspecialchars($track['title']) . '" 
                                data-artist="' . htmlspecialchars($track['artist']) . '" 
                                data-image="' . htmlspecialchars($track['cover']) . '"
                                data-index="' . $index . '">';

                                echo '<div class="cover-container">';
                                echo '<img src="' . $track['cover'] . '" alt="Cover">';
                                echo '<button class="play-button" data-audio="' . $track['audio'] . '" onclick="playTrack(this, \'' . $trackCardId . '\')">&#9654;</button>';
                                echo '</div>';

                                echo '<div class="track-details">';
                                echo '<h3>' . $track['title'] . '</h3>';
                                echo '<p class="transparent-text">' . $track['artist'] . '</p>';
                                echo '</div>';

                                echo '<form class="add-track-form" action="Obrs/add_remove_track.php" method="post">';
                                echo '<input type="hidden" name="track_id" value="' . $track['id'] . '">';
                                echo '<button type="submit" id="add-remove-button" aria-label="Добавить">';
                                
                                // Проверяем, добавлен ли трек
                                $trackAdded = isTrackAdded($user_id, $track['id'], $conn);

                                if ($trackAdded) {
                                    // Если трек добавлен, используем иконку "like-track"
                                    echo '<img src="icons/like-track.png" alt="Добавить">';
                                } else {
                                    // Если трек не добавлен, используем иконку "like"
                                    echo '<img src="icons/like.png" alt="Добавить">';
                                }

                                echo '</button>';
                                echo '</form>';

                                echo '<p class="duration">' . formatDuration($track['duration']) . '</p>';
                                echo '</div>';

                                $index++; // Увеличиваем индекс для следующей карточки
                            }
                        }
                    } else {
                        echo "Ваша коллекция пуста";
                    }
                } else {
                    echo "Ваша коллекция пуста";
                }
            } else {
                echo "Не удалось подключиться к базе данных";
            }
        }

        // Закрываем соединение с базой данных
        $conn->close();

        // Функция для форматирования длительности трека
        function formatDuration($seconds) {
            $minutes = floor($seconds / 60);
            $seconds = $seconds % 60;
            return sprintf("%02d:%02d", $minutes, $seconds);
        }

        // Функция для проверки, добавлен ли трек
        function isTrackAdded($user_id, $track_id, $conn) {
            $user_music_table = "music_" . $user_id;
            $sql = "SELECT * FROM $user_music_table WHERE track_id = $track_id";
            $result = $conn->query($sql);
            return $result->num_rows > 0;
        }
    ?>
    </div>
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

</body>
</html>
