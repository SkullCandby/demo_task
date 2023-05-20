<!DOCTYPE html>
<?php
session_start();

// Проверка авторизации пользователя
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];

$db_host = "localhost";
$db_user = "root";
$db_pass = "GlebDasha2001";
$db_name = "demo";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();

$result = $stmt->get_result();
$userData = $result->fetch_assoc();

$name = $userData['name'];
$profilePictureUrl = $userData['photo'];

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Профиль пользователя</title>
    <link rel="stylesheet" type="text/css" href="profile.css">
</head>
<div class="container">
        <div id="login-successful" class="login-successful-hide">
            <p>Вы успешно вошли в систему! Добро пожаловать!</p>
        </div>
        <h1>Профиль пользователя</h1>
        <div id="profile">
            <img id="profile-picture" src="<?php echo $profilePictureUrl; ?>" alt="Фото профиля">
            <div id="user-info">
                <h3 id="username-info"><?php echo $username; ?></h3>
                <p id="name-info"><?php echo $name; ?></p>
            </div>
            <button class="logout-button" onclick="window.location.href='logout.php'">Выйти</button>
        </div>
    </div>

    <script>
        window.onload = function() {
            document.getElementById('login-successful').className = 'login-successful-show';

            setTimeout(function() {
                document.getElementById('login-successful').className = 'login-successful-hide';
            }, 10000);
        };
    </script>
</html>
