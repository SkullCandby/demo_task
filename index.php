<?php
session_start();

$host = 'localhost';
$dbUsername = 'root';
$dbPassword = 'GlebDasha2001';
$dbName = 'demo';

$connection = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if (isset($_SESSION['username'])) {
    header('Location: profile.php');
    exit();
}

if ($connection->connect_error) {
    die(json_encode(['status' => 'error', 'message' => $connection->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $connection->query("UPDATE users SET login_attempts = 0, last_login_attempt = NULL WHERE username = '$username'");
            $_SESSION['username'] = $username;
            echo json_encode(['status' => 'success']);
            exit();
        } else {
            $loginAttempts = $user['login_attempts'];
            if (isset($_POST['form_submitted'])) {
                $loginAttempts++;
                $connection->query("UPDATE users SET login_attempts = $loginAttempts, last_login_attempt = NOW() WHERE username = '$username'");
            }
            if ($loginAttempts > 3) {
                $lastLoginAttempt = strtotime($user['last_login_attempt']);
                $timeout = 60;
                if (time() - $lastLoginAttempt < $timeout) {
                    $remainingTime = ceil(($timeout - (time() - $lastLoginAttempt)) / 60);
                    echo json_encode(['status' => 'error', 'message' => "Пожалуйста, подождите $remainingTime минут до следующей попытки входа."]);
                    exit();
                }
            }
            echo json_encode(['status' => 'error', 'message' => 'Неправильное имя пользователя или пароль.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Неправильное имя пользователя или пароль.']);
        exit();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Авторизация</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div id="login-form">
                <h1>Авторизация</h1>
                <form method="POST" action="">
                    <input type="text" name="username" id="username" placeholder="Имя пользователя" required>
                    <input type="password" name="password" id="password" placeholder="Пароль" required>
                    <button type="submit" id="login-btn">Войти</button>
                    <p id="error-msg"></p>
                    <p id='timer'></p>
                </form>
            </div>
            <div id="registration-link">
                <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function(){
        $("#login-form").on('submit', function(e){
            e.preventDefault();

            var username = $("#username").val();
            var password = $("#password").val();
            
            console.log({username: username, password: password, form_submitted: 1});
            $.ajax({
                url: 'index.php',
                type: 'POST',
                data: {username: username, password: password, form_submitted: 1},
                success: function(response) {
                    var jsonResponse = JSON.parse(response);
                    console.log(jsonResponse);
                    if (jsonResponse.status === "error") {
                        $("#error-msg").html(jsonResponse.message);
                        getRemainingTime(username); 
                    } else if (jsonResponse.status === "success") {
                        window.location.href = 'profile.php';
                    }
                }
            });
        });
        
        
        function getRemainingTime(username) {
            $.ajax({
                url: `login_attempts.php?username=${username}`,
                success: function(response) {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.status === "success") {
                        var lastAttempt = new Date(jsonResponse.data.last_login_attempt.replace(' ', 'T'));
                        var timeLeft = Math.ceil(60 - (Date.now() - lastAttempt) / 1000);

                        var left = 3 - jsonResponse.data.login_attempts;

                        if (timeLeft > 0 && left <= 0) {
                            $("#timer").text(`Пожалуйста, подождите ${timeLeft} секунд до следующей попытки входа.`);
                            $("#login-btn").prop('disabled', true).addClass('btn-disabled');
                            setTimeout(getRemainingTime, 1000, username); 
                        } else {
                            if (left <= 0) {
                                $("#timer").text(`Осталась 1 попытка входа.`);
                            }
                            else {
                                $("#timer").text(`Осталось ${left} попытки входа.`);
                            }
                            $("#login-btn").prop('disabled', false).removeClass('btn-disabled');
                        }
                    } else {
                        console.error(jsonResponse.message);
                    }
                },
                error: function(error) {
                    console.error('Ошибка:', error);
                }
            });
        }
    });
    </script>
</body>
</html>
