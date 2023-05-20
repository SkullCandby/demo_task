<?php
session_start();

// Проверка, если пользователь уже авторизован, перенаправить на страницу профиля
if (isset($_SESSION['username'])) {
    header('Location: profile.php');
    exit();
}

// Подключение к базе данных
$host = 'localhost';
$dbUsername = 'root';
$dbPassword = 'GlebDasha2001';
$dbName = 'demo';

$connection = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($connection->connect_error) {
    die('Connection failed: ' . $connection->connect_error);
}

// Проверка, если пользователь отправил форму регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обработка отправленных данных регистрации
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $birthdate = $_POST['birthdate'];
    $photo = $_FILES['photo']['name'];
    $photoLocation = $_FILES['photo']['tmp_name'];
    $photoDestination = 'uploads/' . $photo;

    move_uploaded_file($photoLocation, $photoDestination);

    // Проверка, что пользователь заполнил все поля
    if (!empty($username) && !empty($password) && !empty($name) && !empty($birthdate)) {
        // Проверка наличия пользователя с указанным логином
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $connection->query($query);

        if ($result->num_rows > 0) {
            // Ошибка регистрации - пользователь уже существует
            $error_message = 'Пользователь с таким именем уже существует.';
        } else {
            // Хэширование пароля
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Создание новой записи пользователя в базе данных
            $query = "INSERT INTO users (username, password, name, birthdate, photo) VALUES ('$username', '$hashedPassword', '$name', '$birthdate', '$photoDestination')";
            $connection->query($query);

            // Регистрация успешна - сохранение данных в сессии
            $_SESSION['username'] = $username;

            // Перенаправление пользователя на страницу профиля
            header('Location: profile.php');
            exit();
        }
    } else {
        // Ошибка регистрации - не заполнены все поля
        $error_message = 'Пожалуйста, заполните все поля.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div id="registration-form">
                <h1>Регистрация</h1>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="text" name="username" placeholder="Имя пользователя" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <input type="text" name="name" placeholder="Полное имя" required>
                    <input type="date" name="birthdate" placeholder="Дата рождения" required>
                    <input type="file" name="photo" placeholder="Фото" required>
                    <button type="submit" id="register-btn">Зарегистрироваться</button>
                    <p id="error-msg"><?php echo isset($error_message) ? $error_message : ''; ?></p>
                </form>
            </div>
            <div id="login-link">
                <p>Уже есть аккаунт? <a href="index.php">Войти</a></p>
            </div>
        </div>
    </div>
</body>
</html>
