<?php
session_start();
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="css/style.css">
        <title>排球紀錄網站-首頁</title>
    </head>

    <body style="border: 5px solid white">
        <header>
            <nav>
                Welcome, <?php echo htmlspecialchars($username); ?>! 
            </nav>
        </header>
        <hr>
        <button onclick="document.location='newfile.php'">建立新賽事</button>
        <button onclick="document.location='match.php'">查看我的賽事</button>
        <button onclick="document.location='rank.php'">查看排行榜</button>
        <button onclick="document.location='login.php'">登出</button>
    </body>
</html>