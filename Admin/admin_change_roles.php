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
    // Обработка поиска
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Получаем список пользователей с учетом поиска
    $sql = "SELECT * FROM users WHERE login LIKE '%$search%' OR email LIKE '%$search%'";
    $result = $conn->query($sql);
} else {
    // Если пользователь не авторизован как администратор, перенаправляем на главную страницу
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Изменение ролей пользователей</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="icon" href="../icons/website-icon.png" type="image/png">
    <link rel="stylesheet" href="../styles/admin.css">
    <link rel="stylesheet" href="../styles/admin_common.css">
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

    <nav>
        <a href="../index.php">Главная</a>
        <a href="../search.php">Поиск</a>
        <a href="../admin_panel.php">Панель администратора</a>
        <a href="../user_page.php">Моя страница</a>
        <a href="../Obrs/logout.php">Выйти</a>
    </nav>

    <main>
        <section class="admin-form">
            <h2>Изменение ролей пользователей</h2>

            <!-- Строка поиска -->
            <div class="search-box">
                <form action="admin_change_roles.php" method="get">
                    <input type="text" class="search-input" name="search" placeholder="Поиск по логину или email" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-button">Поиск</button>
                </form>
            </div>

            <?php
            if ($result->num_rows > 0) {
                echo '<table class="admin-table">
                        <tr>
                            <th>№</th>
                            <th>Login</th>
                            <th>Email</th>
                            <th>Валидный Email</th>
                            <th>Роль</th>
                        </tr>';

                $counter = 1;
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>
                            <td>' . $counter . '</td>
                            <td>' . htmlspecialchars($row['login']) . '</td>
                            <td>' . htmlspecialchars($row['email']) . '</td>
                            <td>' . htmlspecialchars($row['valid_email']) . '</td>
                            <td>
                                <form action="../Obrs/update_user_role.php" method="post">
                                    <input type="hidden" name="user_id" value="' . $row['id'] . '">
                                    <select name="role">
                                        <option value="user" ' . (($row["role"] == "user") ? "selected" : "") . '>Пользователь</option>
                                        <option value="admin" ' . (($row["role"] == "admin") ? "selected" : "") . '>Администратор</option>
                                    </select>
                                    <button type="submit">Обновить роль</button>
                                </form>
                            </td>
                          </tr>';
                    $counter++;
                }
                echo '</table>';
            } else {
                echo '<p class="error-message">Нет пользователей в базе данных.</p>';
            }
            ?>
        </section>
    </main>

    <script src="script.js"></script>
</body>
</html>