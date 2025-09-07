<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/style.css">
    <title>排球紀錄網站-排行榜</title>
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
        <h1>排行榜</h1>
        <h2>學校排行</h2>
        <table>
            <thead>
                <tr>
                    <th>學校</th>
                    <th>勝場</th>
                    <th>總賽數</th>
                    <th>勝率</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "db_library";
                    
                    // Create connection
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Initialize arrays to store school data
                    $schools = [];

                    // Fetch data from match_info table
                    $sql = "SELECT home_team, away_team, match_result FROM match_info";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $home_team = $row["home_team"];
                            $away_team = $row["away_team"];
                            $match_result = $row["match_result"];

                            // Initialize home team data if not exists
                            if (!isset($schools[$home_team])) {
                                $schools[$home_team] = ["win" => 0, "total" => 0];
                            }

                            // Initialize away team data if not exists
                            if (!isset($schools[$away_team])) {
                                $schools[$away_team] = ["win" => 0, "total" => 0];
                            }

                            // Increment total matches for both teams
                            $schools[$home_team]["total"]++;
                            $schools[$away_team]["total"]++;

                            // Increment win matches based on match result
                            if ($match_result == $home_team) {
                                $schools[$home_team]["win"]++;
                            } elseif ($match_result == $away_team) {
                                $schools[$away_team]["win"]++;
                            }
                        }
                    } else {
                        echo "<tr><td colspan='4'>目前沒有數據</td></tr>";
                    }

                    // Sort schools by win rate in descending order
                    uasort($schools, function($a, $b) {
                        $win_rate_a = $a["win"] / $a["total"];
                        $win_rate_b = $b["win"] / $b["total"];
                        return $win_rate_b <=> $win_rate_a;
                    });

                    // Display the data
                    foreach ($schools as $school => $data) {
                        $win_rate = ($data["win"] / $data["total"]) * 100;
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($school) . "</td>";
                        echo "<td>" . htmlspecialchars($data["win"]) . "</td>";
                        echo "<td>" . htmlspecialchars($data["total"]) . "</td>";
                        echo "<td>" . round($win_rate, 2) . "%</td>";
                        echo "</tr>";
                    }

                    $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
