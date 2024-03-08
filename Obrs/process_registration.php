<?php
include("../db.php");

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Обработка данных формы
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit-reg"])) {
    $login = $_POST["login"];
    $mail = $_POST["mail"];
    $password = $_POST["password"];
    $repeatPassword = $_POST["rapiat-password"];

    // Проверка совпадения паролей
    if ($password != $repeatPassword) {
        header("Location: ../Auth/auth.php?passwords_match=false&login=$login&mail=$mail");
        exit();
    }

    // Валидация электронной почты
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Auth/auth.php?invalid_email=true&login=$login&mail=$mail");
        exit();
    }

    // Проверка требований к паролю
    $passwordRequirements = "/^(?=.*\d)(?=.*[@.,!#$%^&*])[A-Za-z\d@.,!#$%^&*]{8,}$/";

    if (!preg_match($passwordRequirements, $password)) {
        // Пароль не соответствует требованиям
        $passwordErrorMessage = 'Пароль должен состоять минимум из 8 символов и содержать минимум одну цифру и один спецсимвол.';
        header("Location: ../Auth/auth.php?invalid_password=true&login=$login&mail=$mail&password_error=$passwordErrorMessage");
        exit();
    }

    // Проверка уникальности логина
    $stmtCheckLogin = $conn->prepare("SELECT id FROM users WHERE login = ?");
    $stmtCheckLogin->bind_param("s", $login);
    $stmtCheckLogin->execute();
    $stmtCheckLogin->store_result();

    if ($stmtCheckLogin->num_rows > 0) {
        // Логин уже занят, отправка ошибки
        $loginErrorMessage = 'Пользователь с таким логином уже существует.';
        header("Location: ../Auth/auth.php?login_exists=true&mail=$mail&login_error=$loginErrorMessage");
        exit();
    }

    // Проверка уникальности электронной почты
    $stmtCheckEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheckEmail->bind_param("s", $mail);
    $stmtCheckEmail->execute();
    $stmtCheckEmail->store_result();

    if ($stmtCheckEmail->num_rows > 0) {
        // Электронная почта уже используется, отправка ошибки
        $emailErrorMessage = 'Пользователь с такой электронной почтой уже существует.';
        header("Location: ../Auth/auth.php?email_exists=true&login=$login&mail=$mail&email_error=$emailErrorMessage");
        exit();
    }

    // Закрытие запросов на проверку уникальности логина и почты
    $stmtCheckLogin->close();
    $stmtCheckEmail->close();

    // Генерация случайного пароля
    $randomPassword = rand(100000, 999999);

    // Сохранение случайного пароля в сессии
    $_SESSION['random_password'] = $randomPassword;

    // Хеширование пароля
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Вставка данных в таблицу users
    $stmt = $conn->prepare("INSERT INTO users (login, password, email, valid_email) VALUES (?, ?, ?, FALSE)");
    $stmt->bind_param("sss", $login, $hashedPassword, $mail);

    if ($stmt === false) {
        die('Ошибка подготовки запроса (' . $conn->errno . ') ' . $conn->error);
    }

    if ($stmt->execute()) {
        // Получаем user_id только что зарегистрированного пользователя
        $user_id = $stmt->insert_id;

        // Сохраняем user_id в сессии
        $_SESSION['user_id'] = $user_id;

        // Создаем таблицу для музыки, если ее не существует
        $musicTable = "music_" . $user_id;
        $sqlCreateTable = "CREATE TABLE IF NOT EXISTS $musicTable (track_id INT PRIMARY KEY)";
        $conn->query($sqlCreateTable);

        // Значение роли по умолчанию
        $defaultRole = "user";

        // Вставка роли в таблицу user_roles
        // Создание подготовленного запроса для вставки роли
        $stmtRoles = $conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, ?)");
        $stmtRoles->bind_param("is", $user_id, $defaultRole);
        $stmtRoles->execute();

        // Успешная регистрация, отправка письма с подтверждением
        $subject = "Подтверждение регистрации";
        $message = "Ваш случайный пароль для подтверждения почты: $randomPassword";
        $headers = "From: chartbox@inbox.ru"; // Замените на вашу электронную почту

        mail($mail, $subject, $message, $headers);

        // Закрытие подготовленных запросов
        $stmt->close();
        $stmtRoles->close();

        // Перенаправление на страницу подтверждения
        header("Location: confirmation_page.php");
        exit();
    } 
    else {
        // Ошибка вставки данных
        $_SESSION['error_message'] = "Ошибка: " . $stmt->error;
        header("Location: ../Auth/auth.php");
        exit();
    }

}

// Обработка данных формы
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit-enter"])) {
    $login = $_POST["login"];
    $password = $_POST["password"];

    // Получение данных пользователя из базы данных
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users JOIN user_roles ON users.id = user_roles.user_id WHERE login = ?");
    $stmt->bind_param("s", $login);

    if ($stmt === false) {
        die('Ошибка подготовки запроса (' . $conn->errno . ') ' . $conn->error);
    }

    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Если пользователь найден, получаем данные
        $stmt->bind_result($user_id, $hashedPassword, $role);
        $stmt->fetch();

        // Проверка пароля
        if (password_verify($password, $hashedPassword)) {
            // Успешный вход, устанавливаем переменные в сессии
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;

            // Закрытие подготовленного запроса
            $stmt->close();
            // Перенаправление на защищенную страницу или куда вам нужно
            header("Location: ../index.php");
            exit();
        } 
        else {
            // Неверный пароль
            $_SESSION['login_error'] = "Неверный пароль.";
            header("Location: ../Auth/auth.php");
            exit();
        }
    } 
    else {
        // Пользователь не найден
        $_SESSION['login_error'] = "Пользователь с таким логином не найден.";
        header("Location: ../Auth/auth.php");
        exit();
    }

}

// Закрытие соединения с базой данных
$conn->close();
?>