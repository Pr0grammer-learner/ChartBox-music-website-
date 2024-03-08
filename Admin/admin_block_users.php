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
    // Получаем список пользователей
    $sql = "SELECT * FROM users";
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
    <title>Блокировка пользователей</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="icon" href="../icons/website-icon.png" type="image/png">
    <link rel="stylesheet" href="../styles/admin_block_users.css">
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
        <section class="admin-table">
            <h2>Блокировка пользователей</h2>

            <?php
            if ($result->num_rows > 0) {
                echo '<table class="admin-table">
                        <tr>
                            <th>Login</th>
                            <th>Email</th>
                            <th>Valid_email</th>
                            <th>Действие</th>
                        </tr>';

                while ($row = $result->fetch_assoc()) {
                    echo '<tr>
                            <td>' . htmlspecialchars($row['login']) . '</td>
                            <td>' . htmlspecialchars($row['email']) . '</td>
                            <td>' . htmlspecialchars($row['valid_email']) . '</td>
                            <td>
                                <form action="../Obrs/block_user.php" method="post">
                                    <input type="hidden" name="user_id" value="' . $row['id'] . '">
                                    <button type="submit">Забанить</button>
                                </form>
                            </td>
                          </tr>';
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