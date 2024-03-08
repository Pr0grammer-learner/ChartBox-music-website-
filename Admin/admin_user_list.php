<?php
include("../db.php");

// Проверяем, авторизован ли пользователь как администратор
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    // Получаем списки пользователей с разными ролями
    $userList = getUsersByRole('user');
    $adminList = getUsersByRole('admin');
    $bannedList = getUsersByRole('banned');
} else {
    // Если пользователь не авторизован как администратор, перенаправляем на главную страницу
    header("Location: ../index.php");
    exit();
}

// Функция для получения пользователей по роли
function getUsersByRole($role)
{
    include("../db.php");

    $sql = "SELECT u.id, u.login, u.email, u.valid_email, r.role
            FROM users u
            JOIN user_roles r ON u.id = r.user_id
            WHERE r.role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список пользователей</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/admin_block_users.css">  
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

    <?php include("admin_nav.php"); ?>

    <main>
        <section class="admin-user-list">
            <div class="user-links">
                <a href="?role=user">Пользователи</a>
                <a href="?role=admin">Администраторы</a>
                <a href="?role=banned">Заблокированные</a>
            </div>

            <?php 
            // Проверяем, есть ли параметр запроса role и устанавливаем соответствующий заголовок
            $role = isset($_GET['role']) ? $_GET['role'] : 'user';
            switch ($role) {
                case 'user':
                    echo '<h2>Пользователи (User)</h2>';
                    renderUserTable($userList);
                    break;
                case 'admin':
                    echo '<h2>Администраторы (Admin)</h2>';
                    renderUserTable($adminList);
                    break;
                case 'banned':
                    echo '<h2>Заблокированные (Banned)</h2>';
                    renderUserTable($bannedList);
                    break;
                default:
                    // Если указана неверная роль, выводим список пользователей
                    echo '<h2>Пользователи (User)</h2>';
                    renderUserTable($userList);
            }
            ?>
        </section>
    </main>
</body>
</html>

<?php
// Функция для отображения таблицы пользователей
function renderUserTable($userList)
{
    if (!empty($userList)) {
        echo '<table class="admin-table">
                <tr>
                    <th>№</th>
                    <th>Login</th>  
                    <th>Email</th>
                    <th>Valid Email</th>
                    <th>Role</th>
                </tr>';

        $counter = 1;
        foreach ($userList as $user) {
            echo '<tr>
                    <td>' . $counter . '</td>
                    <td>' . htmlspecialchars($user['login']) . '</td>
                    <td>' . htmlspecialchars($user['email']) . '</td>
                    <td>' . htmlspecialchars($user['valid_email']) . '</td>
                    <td>' . htmlspecialchars($user['role']) . '</td>
                </tr>';
            $counter++;
        }

        echo '</table>';
    } else {
        echo '<p class="error-message">Нет пользователей в данной категории.</p>';
    }
}
?>
