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
    <link rel="stylesheet" href="styles/admin.css">
    <link rel="icon" href="/icons/website-icon.png" type="image/png">
    <script src="script.js"></script>
</head>
<body>
    <header>
        <a href="admin_panel.php" id="site-icon">
            <!-- Добавляем иконку перед названием сайта -->
            <img src="icons/website-icon.png" alt="Site Icon">
        </a>

        <a id="logo" href="admin_panel.php">
            <h1>Chartbox Admin</h1>
        </a>
    </header>

    <?php
        include("db.php");
        // Проверяем, авторизован ли пользователь
        if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'){
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
        }
        else{
            // Если пользователь не авторизован как администратор, перенаправляем на главную страницу
            header("Location: index.php");
            exit();
        }
    ?>

    <main>
    <section class="admin-panel">
        <h2>Панель администратора</h2>
        
        <div class="admin-table">
            <div class="admin-column">
                <div class="admin-row">
                    <a href="Admin/admin_change_roles.php">Изменение ролей пользователей</a>
                </div>
                <div class="admin-row">
                    <a href="Admin/admin_block_users.php">Блокировка пользователей</a>
                </div>
                <div class="admin-row">
                    <a href="Admin/admin_user_list.php">Список всех пользователей</a>
                </div>
            </div>
            
            <div class="admin-column">
                <div class="admin-row">
                    <a href="Admin/admin_add_track.php">Добавить трек</a>
                </div>
                <div class="admin-row">
                    <a href="Admin/admin_edit_tracks.php">Редактирование муз карточек</a>
                </div>
            </div>
        </div>
    </section>
    </main>

</body>
</html>
