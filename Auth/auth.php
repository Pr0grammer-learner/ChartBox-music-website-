<?php
// Проверяем, были ли переданы ошибки
$errors = array();

if (isset($_GET['passwords_match']) && $_GET['passwords_match'] == 'false') {
    $errors[] = 'Пароли не совпадают.';
}

if (isset($_GET['invalid_email']) && $_GET['invalid_email'] == 'true') {
    $errors[] = 'Некорректный адрес электронной почты.';
}

// Проверка наличия ошибки требований к паролю
if (isset($_GET['password_error'])) {
    $errors[] = $_GET['password_error'];
}

// Проверка ошибки уникальности логина
if (isset($_GET['login_exists']) && $_GET['login_exists'] == 'true') {
    $errors[] = 'Пользователь с таким логином уже существует.';
}

// Проверка ошибки уникальности почты
if (isset($_GET['email_exists']) && $_GET['email_exists'] == 'true') {
    $errors[] = 'Пользователь с такой электронной почтой уже существует.';
}

// Сохраняем введенные данные
$enteredUsername = isset($_GET['login']) ? $_GET['login'] : '';
$enteredEmail = isset($_GET['mail']) ? $_GET['mail'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="../styles/auth.css">
</head>
<body>
    <!-- Выводим ошибки сверху формы -->
    <?php foreach ($errors as $error): ?>
        <p style="color: red; text-align: -webkit-center; height: 0px;"><?php echo $error; ?></p>
    <?php endforeach; ?>
    <div class='container'>
        <div class='button-reg'>
            <div class='login'>Регистрация</div>
            <div class='singin'>Вход</div>
        </div>
        <form name='enter' method="post" action="../obrs/process_registration.php">
            <input type='text' name='login' class='login-enter' placeholder='Введите имя пользователя' value="<?php echo htmlspecialchars($enteredUsername); ?>" required></input>
            <input type='text' name='mail' class='mail-enter' placeholder='Введите вашу электронную почту' value="<?php echo htmlspecialchars($enteredEmail); ?>" required></input>
            
            <!-- Добавлен атрибут title с подсказкой -->
            <input type='password' name='password' class='password-enter' placeholder='Введите пароль'
                   title='Пароль должен состоять минимум из 8 символов и содержать минимум одну цифру и один спецсимвол.'
                   required></input>
            
            <input type='password' name='rapiat-password' class='second-password' placeholder='Повторите пароль' required></input>
            <input type='submit' name='submit-reg' class='submit-reg' value='Зарегистрироваться'></input>
            
            <?php
                $isChecked = (isset($_GET['agree']) && $_GET['agree'] == 'true') ? 'checked' : '';
                echo '<input type="checkbox" name="conditions" class="check-conditions" required ' . $isChecked .'>';
            ?>
            <a href='arrangement.php' class='agree'>Я уже прочитал и согласен с</br> пользовательским соглашением</a>
        <input type='submit' name='submit-enter' class='submit-enter' value='Войти' required></input>
        </form>
    </div>
</body>
    <script src="../Scripts/auth.js"></script>
</html>

