<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/style.css">
    <title>排球紀錄網站-註冊</title>
</head>

<body>
<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_library";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $password_again = $_POST["password_again"];
    $check_username_sql = "SELECT * FROM user WHERE username = '$username'";
    $check_result = $conn->query($check_username_sql);
    if ($check_result->num_rows > 0) {
        $_SESSION['message'] = "Username already exists.";
        header('Location: login.php');
    }
    if ($password !== $password_again) {
        echo 'Passwords do not match.';
    } 
    else {
        $sql = "INSERT INTO user (username, user_password) VALUES ('$username', '$password')";
        
        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Registration success!";
            header('Location: login.php');
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    

    $conn->close();
}
?>
<?php
if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
?>

<div class="wrapper" style="border: 5px solid white">
    <form id="user" action="register.php" method="post">
        <label for="username">請輸入帳號</label>
        <input id="username" name="username" type="text" required>
        <br>
        <label for="password">請輸入密碼</label>
        <input id="password" name="password" type="text" required>
        <br>
        <label for="password_again">請再次輸入密碼</label>
        <input id="password_again" name="password_again" type="text" required>
        <br>
        <input type="submit" class="input-button" value="註冊">
    </form>
</div>
</body>
</html>