<?php
    include("../db.php");

    // Получение случайного пароля из сессии
    $random_password = $_SESSION['random_password'];

    // Обработка данных формы
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $enteredPassword = $_POST["entered_password"];


        // Проверка совпадения введенного пароля с случайным паролем
        if ($enteredPassword == $random_password) {
            // Пароли совпадают, обновление записи в базе данных
            $userId = $_SESSION['user_id']; // Предполагается, что вы сохраняете ID пользователя в сессии при регистрации
            $updateQuery = "UPDATE users SET valid_email = 1 WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $userId);

            if ($stmt->execute()) {
                // Успешное обновление, можете перенаправить пользователя на другую страницу
                header("Location: ../index.php");
                exit();
            } else {
                // Ошибка при обновлении данных
                $error_message = "Ошибка при обновлении данных: " . $stmt->error;
            }

            $stmt->close();
        } else {
            // Пароли не совпадают
            $error_message = "Введенный пароль неверен.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение почты</title>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #1a1a1a;
        color: #ffffff;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
    }

    .container {
        background-color: #0047ab;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        text-align: center;
    }

    form {
        margin-top: 20px;
    }

    input {
        padding: 10px;
        margin-bottom: 10px;
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f8f8f8;
        color: #333;
        font-size: 16px;
    }

    input[type="submit"] {
        background-color: #196776;
        color: #ffffff;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #004a96;
    }

    a {
        color: #ea534d;
        text-decoration: none;
        font-size: 14px;
        margin-top: 10px;
        display: inline-block;
    }

    .error-message {
        color: #ea534d;
        margin-top: 10px;
    }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" action="">
            <input type="number" name="entered_password" placeholder="Введите пароль из письма" maxlength="6" required>
            <div>
                <input type="submit" value="Подтвердить почту">
                <a href="../index.php">Подтвердить позднее</a>
            </div>
        </form>
        <?php
            // Отображение сообщения об ошибке, если есть
            if (isset($error_message)) {
                echo "<p class='error-message'>$error_message</p>";
            }
        ?>
    </div>
</body>
</html>