<?php
session_start();

if (isset($_SESSION['username'])) {
    header('Location: profile.php');
    exit();
}

$host = 'localhost';
$dbUsername = 'root';
$dbPassword = 'GlebDasha2001';
$dbName = 'demo';

$connection = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($connection->connect_error) {
    die('Connection failed: ' . $connection->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $birthdate = $_POST['birthdate'];
    $photo = $_FILES['photo']['name'];
    $photoLocation = $_FILES['photo']['tmp_name'];
    $photoDestination = 'uploads/' . $photo;

    move_uploaded_file($photoLocation, $photoDestination);

    if (!empty($username) && !empty($password) && !empty($name) && !empty($birthdate)) {
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $connection->query($query);

        if ($result->num_rows > 0) {
            $error_message = 'Пользователь с таким именем уже существует.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (username, password, name, birthdate, photo) VALUES ('$username', '$hashedPassword', '$name', '$birthdate', '$photoDestination')";
            $connection->query($query);

            $_SESSION['username'] = $username;

            header('Location: profile.php');
            exit();
        }
    } else {
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
