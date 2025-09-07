<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/style.css">
    <title>排球紀錄網站-查看賽事</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .button-container {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .button-container button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .button-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body style="border: 5px solid white">
    <header>
        <nav>
            <p>Login/Logout</p>
        </nav>
    </header>
    <hr>
    <div class="wrapper">
        <div class="button-container">
            <button onclick="window.location.href='home.php'">回到主頁</button>
        </div>
        <h1>賽事列表</h1>
        <table>
            <thead>
                <tr>
                    <th>賽事名稱</th>
                    <th>日期</th>
                    <th>主隊</th>
                    <th>客隊</th>
                    <th>結果</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "db_library";                     
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql = "SELECT match_id, match_name, match_date, match_result FROM match_info";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td style='text-align: left;'>" . $row["match_name"] . "</td>";
                            echo "<td>" . $row["match_date"] . "</td>";
                            echo "<td>" . explode(' vs ', $row["match_name"])[0] . "</td>";
                            echo "<td>" . explode(' vs ', $row["match_name"])[1] . "</td>";
                            echo "<td>" . $row["match_result"] . "</td>";
                            //echo "<td><a href='match_detail.php?match_id=" . $row["match_id"] . "'>查看詳情</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>目前沒有賽事記錄</td></tr>";
                    }

                    $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
