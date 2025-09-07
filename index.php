<html lang="zh-Hant-TW">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="css/style.css">
        <title>排球紀錄網站-紀錄</title>
    </head>
    <body>
        <?php
            $servername = "localhost";
            $username = "root";
            $password = "nieves0112";
            $dbname = "team15";
            error_reporting(E_ALL);
            ini_set('display_errors', 1);   

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $current_home_score = 0;
            $current_away_score = 0;
            if (isset($_POST['newfile'])){
                $hometeam = $_POST["home_team"];
                $awayteam = $_POST["away_team"];
                $set_num = $_POST["set_num"]; //這是三局兩勝or五局三勝的
                $one = $_POST["1"];
                $two = $_POST["2"];
                $three = $_POST["3"];
                $four = $_POST["4"];
                $five = $_POST["5"];
                $six = $_POST["6"];
                $libero = $_POST["libero"];
                $sql = "INSERT INTO match_info (match_name, match_date, match_format, away_team, home_team) VALUES ('$hometeam vs $awayteam', NOW(), '$set_num', '$awayteam','$hometeam')";
                if ($conn->query($sql) === TRUE) {
                    $match_id = $conn->insert_id;
                    $current_set = "SELECT current_set FROM match_info WHERE match_id = '$match_id'";
                    $result = $conn->query($current_set);
                    if ($result) {
                        $row = $result->fetch_assoc();
                        $current_set = $row['current_set'];
                    } else {
                        echo "Error: " . $conn->error;
                    }
                    // 這裡先將空白的資料insert好，在下面才能直接用update的方式做，DDL都已經改好了，預設對方失誤、換人跟暫停都是0，所以只需要insert目前局數跟賽事ID
                    $sql_error_substitution_timeout = "INSERT INTO error_substitution_timeout (set_number, match_id) VALUES ('$current_set', '$match_id')";
                    if ($conn->query($sql_error_substitution_timeout) === TRUE) echo "";
                    else echo "Error: " . $sql_error_substitution_timeout . "<br>" . $conn->error;
                    $sql_lineup_sheet = "INSERT INTO lineup_sheet VALUES('$current_set', '$match_id', '$one', '$two', '$three', '$four', '$five', '$six', '$libero')";
                    if ($conn->query($sql_lineup_sheet) === TRUE) echo "";
                    else echo "Error: " . $sql_lineup_sheet . "<br>" . $conn->error;
                }
                else echo "Error: " . $sql . "<br>" . $conn->error;
                $conn->close();
            }
        ?>
        <div class="wrapper">
            <div class="header-flex-box-container">
                <div class="header-box header-box-item-1">
                    <div>
                        <span id="leftteam">
                            <?php echo $_POST["home_team"]; ?>
                        </span> vs 
                        <span id="rightteam">
                            <?php echo $_POST["away_team"]; ?>
                        </span>
                    </div>
                    <!-- 目前比分跟局數這塊也是要參照第94-125行那樣在有刷新頁面或是有更動的時候進行更新 -->
                    <div>
                        <h4>目前比分</h4>
                        <?php
                            $servername = "localhost";
                            $username = "root";
                            $password = "nieves0112";
                            $dbname = "team15";                          
                            error_reporting(E_ALL);
                            ini_set('display_errors', 1);   
                            $conn = new mysqli($servername, $username, $password, $dbname);
                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }
                            if (isset($_POST['match_id'])) {
                                $match_id = $_POST['match_id'];
                                $sql = "SELECT current_home_score, current_away_score, current_set, match_format, away_team, current_home_set, current_away_set
                                        FROM match_info WHERE match_id = $match_id";
                                $result = $conn->query($sql);
                                $row = $result->fetch_assoc();
                                $current_home_score = $row['current_home_score'];
                                $current_away_score = $row['current_away_score'];
                                $away_team = $row['away_team'];
                                $match_format = $row['match_format'];
                                $hometeam = $_POST["home_team"];
                                $awayteam = $_POST["away_team"];
                                $home_set = $row["current_home_set"];
                                $away_set = $row["current_away_set"];
                                $set_now = $row['current_set'];
                            }

                            if (isset($_POST['leftadd'])) {
                                $match_id = $_POST['match_id'];
                                if ($current_home_score < 24) {
                                    $sql = "UPDATE match_info SET current_home_score = current_home_score + 1 WHERE match_id = $match_id AND current_set  = $set_now";
                                    if ($conn->query($sql) === TRUE) {
                                        $current_home_score += 1;
                                    } else {
                                        echo "Error: " . $sql . "<br>" . $conn->error;
                                    }
                                } else {
                                    $current_home_score = 0;
                                    $current_away_score = 0;
                                    $home_set = $home_set + 1;
                                    $set_now = $set_now + 1;
                                    $flag = 1;
                            
                                    $sql = "UPDATE match_info SET current_home_score = '$current_home_score', current_away_score = '$current_away_score', current_set = '$set_now', current_home_set = '$home_set', current_away_set = '$away_set' WHERE match_id = '$match_id'";
                                    if ($conn->query($sql) === TRUE) {
                                        echo "";
                                    } else {
                                        echo "Error: " . $sql . "<br>" . $conn->error;
                                    }

                                    $lineup_change = "UPDATE lineup_sheet SET set_number = '$set_now' WHERE match_id = '$match_id'";
                                    if ($conn->query($lineup_change) === TRUE) echo "";
                                    else echo "Error: " . $lineup_change . "<br>" . $conn->error;
                                    $sql_error_substitution_timeout = "INSERT INTO error_substitution_timeout (set_number, match_id) VALUES ('$set_now', '$match_id')";
                                    if ($conn->query($sql_error_substitution_timeout) === TRUE) echo "";
                                    else echo "Error: " . $sql_error_substitution_timeout . "<br>" . $conn->error;
                                    if (($match_format == "three_set" && $home_set == 2) || ($match_format == "five_set" && $home_set == 3)) {
                                        $sql = "UPDATE match_info SET match_result = '$hometeam' WHERE match_id = '$match_id'";
                                        $conn->query($sql);
                                        echo "主隊獲勝！";
                                        header("Location: home.php");
                                        exit();
                                    }
                                }
                            } elseif (isset($_POST['rightadd'])) {
                                $match_id = $_POST['match_id'];
                                if ($current_away_score < 24) {
                                    $sql = "UPDATE match_info SET current_away_score = current_away_score + 1 WHERE match_id = $match_id AND current_set  = $set_now";
                                    if ($conn->query($sql) === TRUE) {
                                        $current_away_score += 1;
                                    } else {
                                        echo "Error: " . $sql . "<br>" . $conn->error;
                                    }
                                } else {
                                    $current_home_score = 0;
                                    $current_away_score = 0;
                                    $away_set = $away_set + 1;
                                    $set_now = $set_now + 1;
                                    $flag = 1;
                            
                                    $sql = "UPDATE match_info SET current_home_score = '$current_home_score', current_away_score = '$current_away_score', current_set = '$set_now', current_home_set = '$home_set', current_away_set = '$away_set' WHERE match_id = '$match_id'";
                                    if ($conn->query($sql) === TRUE) {
                                        echo "";
                                    } else {
                                        echo "Error: " . $sql . "<br>" . $conn->error;
                                    }
                                    $lineup_change = "UPDATE lineup_sheet SET set_number = '$set_now' WHERE match_id = '$match_id'";
                                    if ($conn->query($lineup_change) === TRUE) echo "";
                                    else echo "Error: " . $lineup_change . "<br>" . $conn->error;
                                    $sql_error_substitution_timeout = "INSERT INTO error_substitution_timeout (set_number, match_id) VALUES ('$set_now', '$match_id')";
                                    if ($conn->query($sql_error_substitution_timeout) === TRUE) echo "";
                                    else echo "Error: " . $sql_error_substitution_timeout . "<br>" . $conn->error;
                                    if (($match_format == "three_set" && $away_set == 2) || ($match_format == "five_set" && $away_set == 3)) {
                                        $sql = "UPDATE match_info SET match_result = '$awayteam' WHERE match_id = '$match_id'";
                                        $conn->query($sql);
                                        echo "客隊獲勝！";
                                        header("Location: home.php");
                                        exit();
                                    }
                                }
                            }
                            
                            $conn->close();
                            ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="leftadd" id="leftadd" value = "+">
                        </form>
                        <span id="leftscore"><?php echo $current_home_score; ?></span><span id="score"> : </span><span id="rightscore"><?php echo $current_away_score; ?></span>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="rightadd" id="rightadd" value = "+">
                        </form>
                    </div>
                    <div>
                        <h4>局數</h4>
                        <?php
                            $servername = "localhost";
                            $username = "root";
                            $password = "nieves0112";
                            $dbname = "team15";
                            $home_set = 0;
                            $away_set = 0;                        
                            error_reporting(E_ALL);
                            ini_set('display_errors', 1);   
                            $conn = new mysqli($servername, $username, $password, $dbname);
                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }
                            if (isset($_POST['match_id'])) {
                                $match_id = $_POST['match_id'];
                                $sql = "SELECT current_home_set, current_away_set FROM match_info WHERE match_id = $match_id";
                                $result = $conn->query($sql);
                                if ($result) {
                                    $row = $result->fetch_assoc();
                                    $home_set = $row['current_home_set'];
                                    $away_set = $row['current_away_set'];
                                } else {
                                    echo "Error: " . $conn->error;
                                }
                            }
                            $conn->close();
                        ?>
                        <span id="set_now"><?php echo $home_set; ?></span><span id="score"> : </span><span id="total_set"><?php echo $away_set; ?></span>
                    </div>
                </div>
                <div class="header-box header-box-item-2">
                    <div>
                        <h4>提示訊息</h4>
                        <p id="info_change"></p>
                        <p id="info_timeout"></p>
                        <p id="info"></p>
                        <!-- 這邊就是替代之前的js檔 -->
                        <?php
                            $servername = "localhost";
                            $username = "root";
                            $password = "nieves0112";
                            $dbname = "team15";                          
                            error_reporting(E_ALL);
                            ini_set('display_errors', 1); 
                            $conn = new mysqli($servername, $username, $password, $dbname);
                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }
                            if(isset($_POST['other_mistake'])){
                                echo "對方失誤";
                                $match_id = $_POST["match_id"];
                                $sql = "UPDATE error_substitution_timeout SET away_error = away_error+1 WHERE match_id = $match_id";
                                if ($conn->query($sql) === TRUE) echo "";
                                else echo "Error: " . $sql . "<br>" . $conn->error;  
                            }
                            elseif(isset($_POST['substi'])){
                                echo "換人";
                                $match_id = $_POST["match_id"];
                                $sql = "UPDATE error_substitution_timeout SET substitution = substitution+1 WHERE match_id = $match_id";
                                if ($conn->query($sql) === TRUE) echo "";
                                else echo "Error: " . $sql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['timeout'])){
                                echo "暫停";
                                $match_id = $_POST["match_id"];
                                $sql = "UPDATE error_substitution_timeout SET time_out = time_out+1 WHERE match_id = $match_id";
                                if ($conn->query($sql) === TRUE) echo "";
                                else echo "Error: " . $sql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_nice1'])) {
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號舉球/修正好球";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_mistake1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號舉球/修正失誤";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_nice2'])){
                                echo "2號 舉球/修正:好球";
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECTplayer_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號舉球/修正好球";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_mistake2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號舉球/修正失誤";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_nice3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號舉球/修正好球";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_mistake3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號舉球/修正失誤";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_nice4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號舉球/修正好球";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_mistake4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號舉球/修正失誤";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_nice5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號舉球/修正好球";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_mistake5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號舉球/修正失誤";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_nice6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號舉球/修正好球";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_mistake6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號舉球/修正失誤";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_niceli'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['libero'];
                                echo $player . "號舉球/修正好球";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['se_mistakeli'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['libero'];
                                echo $player . "號舉球/修正失誤";
                                $insertSql = "INSERT INTO set_correction (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_touch1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號攔網擊球";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '擊球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_mistake1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號攔網失誤";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_score1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號攔網得分";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_seam1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號攔網中洞";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '中洞', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_touch3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號攔網擊球";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '擊球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_mistake3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號攔網失誤";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_score3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號攔網得分";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_seam3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號攔網中洞";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '中洞', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_touch2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號攔網擊球";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '擊球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_mistake2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號攔網失誤";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_score2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號攔網得分";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_seam2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號攔網中洞";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '中洞', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_touch4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號攔網擊球";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '擊球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_mistake4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號攔網失誤";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_score4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號攔網得分";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_seam4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號攔網中洞";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '中洞', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_touch5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號攔網擊球";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '擊球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_mistake5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號攔網失誤";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_score5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號攔網得分";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_seam5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號攔網中洞";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '中洞', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_touch6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號攔網擊球";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '擊球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_mistake6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號攔網失誤";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_score6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號攔網得分";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['b_seam6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號攔網中洞";
                                $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '中洞', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            // elseif(isset($_POST['b_touchli'])){
                            //     echo "自由球員 攔網:擊球";
                            //     $match_id = $_POST["match_id"];
                            //     $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                            //     $row = $result->fetch_assoc();
                            //     $current_set = $row['current_set'];
                            //     $hscore = $row['current_home_score'];
                            //     $ascore = $row['current_away_score'];
                            //     $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                            //     $row = $result->fetch_assoc();
                            //     $player = $row['libero'];
                            //     $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                            //                   VALUES ('$current_set', '$match_id', '$player', '擊球', '$hscore', '$ascore')";
                            //     if ($conn->query($insertSql) === TRUE) echo "";
                            //     else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            // }
                            // elseif(isset($_POST['b_mistakeli'])){
                            //     echo "自由球員 攔網:失誤";
                            //     $match_id = $_POST["match_id"];
                            //     $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                            //     $row = $result->fetch_assoc();
                            //     $current_set = $row['current_set'];
                            //     $hscore = $row['current_home_score'];
                            //     $ascore = $row['current_away_score'];
                            //     $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                            //     $row = $result->fetch_assoc();
                            //     $player = $row['libero'];
                            //     $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                            //                   VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                            //     if ($conn->query($insertSql) === TRUE) echo "";
                            //     else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            // }
                            // elseif(isset($_POST['b_scoreli'])){
                            //     echo "自由球員 攔網:得分";
                            //     $match_id = $_POST["match_id"];
                            //     $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                            //     $row = $result->fetch_assoc();
                            //     $current_set = $row['current_set'];
                            //     $hscore = $row['current_home_score'];
                            //     $ascore = $row['current_away_score'];
                            //     $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                            //     $row = $result->fetch_assoc();
                            //     $player = $row['libero'];
                            //     $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                            //                   VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                            //     if ($conn->query($insertSql) === TRUE) echo "";
                            //     else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            // }
                            // elseif(isset($_POST['b_seamli'])){
                            //     echo "自由球員 攔網:中洞";
                            //     $match_id = $_POST["match_id"];
                            //     $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                            //     $row = $result->fetch_assoc();
                            //     $current_set = $row['current_set'];
                            //     $hscore = $row['current_home_score'];
                            //     $ascore = $row['current_away_score'];
                            //     $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                            //     $row = $result->fetch_assoc();
                            //     $player = $row['libero'];
                            //     $insertSql = "INSERT INTO block (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                            //                   VALUES ('$current_set', '$match_id', '$player', '中洞', '$hscore', '$ascore')";
                            //     if ($conn->query($insertSql) === TRUE) echo "";
                            //     else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            // }
                            elseif(isset($_POST['a_success1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號攻擊成功";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_score1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號攻擊得分";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_mistake1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號攻擊失誤";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失敗', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_success2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號攻擊成功";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_score2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號攻擊得分";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_mistake2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號攻擊失誤";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失敗', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_success3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號攻擊成功";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_score3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號攻擊得分";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_mistake3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號攻擊失誤";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失敗', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_success4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號攻擊成功";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_score4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號攻擊得分";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_mistake4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號攻擊失誤";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失敗', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_success5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號攻擊成功";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_score5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號攻擊得分";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_mistake5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號攻擊失誤";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失敗', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_success6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號攻擊成功";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_score6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號攻擊得分";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['a_mistake6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號攻擊失誤";
                                $insertSql = "INSERT INTO attack (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失敗', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_nice1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號防守好球";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_mistake1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號防守失誤";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_nice2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號防守好球";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_mistake2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號防守失誤";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_nice3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號防守好球";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_mistake3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號防守失誤";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_nice4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號防守好球";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_mistake4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號防守失誤";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_nice5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號防守好球";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_mistake5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號防守失誤";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_nice6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號防守好球";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_mistake6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號防守失誤";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_niceli'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['libero'];
                                echo $player . "號防守好球";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['d_mistakeli'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['libero'];
                                echo $player . "號防守失誤";
                                $insertSql = "INSERT INTO defense (set_number, match_id, player_number, success_error, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_success1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號發球成功";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_score1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號發球得分";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_mistake1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號發球失誤";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_success2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號發球成功";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_score2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號發球得分";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_mistake2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號發球失誤";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_success3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號發球成功";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_score3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號發球得分";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_mistake3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號發球失誤";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_success4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號發球成功";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_score4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號發球得分";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_mistake4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號發球失誤";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_success5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號發球成功";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_score5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號發球得分";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_mistake5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號發球失誤";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_success6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號發球成功";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '成功', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_score6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號發球得分";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '得分', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['s_mistake6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號發球失誤";
                                $insertSql = "INSERT INTO serve (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_nice1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號接發好球";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_mistake1'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_one FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_one'];
                                echo $player . "號接發失誤";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_nice2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號接發好球";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_mistake2'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_two FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_two'];
                                echo $player . "號接發失誤";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_nice3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號接發好球";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_mistake3'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_three FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_three'];
                                echo $player . "號接發失誤";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_nice4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號接發好球";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_mistake4'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_four FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_four'];
                                echo $player . "號接發失誤";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_nice5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號接發好球";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_mistake5'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_five FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_five'];
                                echo $player . "號接發失誤";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_nice6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號接發好球";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_mistake6'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT player_six FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['player_six'];
                                echo $player . "號接發失誤";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_niceli'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['libero'];
                                echo $player . "號接發好球";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '好球', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            elseif(isset($_POST['r_mistakeli'])){
                                $match_id = $_POST["match_id"];
                                $result = $conn->query("SELECT current_set, current_home_score, current_away_score FROM match_info WHERE match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $current_set = $row['current_set'];
                                $hscore = $row['current_home_score'];
                                $ascore = $row['current_away_score'];
                                $result = $conn->query("SELECT libero FROM lineup_sheet WHERE set_number = '$current_set' AND match_id = '$match_id'");
                                $row = $result->fetch_assoc();
                                $player = $row['libero'];
                                echo $player . "號接發失誤";
                                $insertSql = "INSERT INTO receive (set_number, match_id, player_number, success_error_score, current_home_score, current_away_score)
                                              VALUES ('$current_set', '$match_id', '$player', '失誤', '$hscore', '$ascore')";
                                if ($conn->query($insertSql) === TRUE) echo "";
                                else echo "Error: " . $insertSql . "<br>" . $conn->error;
                            }
                            else {
                                echo "";
                            }
                            $conn->close();
                        ?>
                    </div>
                </div>
                <div class="header-box header-box-item-3">
                    <div>
                        <p>Login/Logout</p>
                    </div>
                </div>
            </div>
            <div class="body-flex-box-container">
                <div class="body-box body-box-item-1">
                    <h4>輪轉表</h4>
                    <button type="button" class="number" id="btn4">
                        <?php echo $_POST["4"]; ?>
                    </button>
                    <button type="button" class="number" id="btn3">
                        <?php echo $_POST["3"]; ?>
                    </button>
                    <button type="button" class="number" id="btn2">
                        <?php echo $_POST["2"]; ?>
                    </button>
                    <br><br><br>
                    <button type="button" class="number" id="btn5">
                        <?php echo $_POST["5"]; ?>
                    </button>
                    <button type="button" class="number" id="btn6">
                        <?php echo $_POST["6"]; ?>
                    </button>
                    <button type="button" class="number" id="btn1">
                        <?php echo $_POST["1"]; ?>
                    </button>
                    <br><br><br>
                    <button type="button" class="number" id="btn7">
                        <?php echo $_POST["libero"]; ?>
                    </button>
                </div>
                <div class="body-box body-box-item-2">
                    <h4>發球</h4>
                    <h5> <?php echo $_POST["1"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_success1" id="s_success1" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_score1" id="s_score1" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_mistake1" id="s_mistake1" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["2"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_success2" id="s_success2" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_score2" id="s_score2" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_mistake2" id="s_mistake2" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["3"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_success3" id="s_success3" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_score3" id="s_score3" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_mistake3" id="s_mistake3" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["4"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_success4" id="s_success4" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_score4" id="s_score4" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_mistake4" id="s_mistake4" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["5"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_success5" id="s_success5" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_score5" id="s_score5" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_mistake5" id="s_mistake5" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["6"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_success6" id="s_success6" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_score6" id="s_score6" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="s_mistake6" id="s_mistake6" value = "失誤">
                        </form>
                    </h5>
                    <h4>接發</h4>
                    <h5><?php echo $_POST["1"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_nice1" id="r_nice1" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_mistake1" id="r_mistake1" value = " 失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["2"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_nice2" id="r_nice2" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_mistake2" id="r_mistake2" value = " 失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["3"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_nice3" id="r_nice3" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_mistake3" id="r_mistake3" value = " 失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["4"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_nice4" id="r_nice4" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_mistake4" id="r_mistake4" value = " 失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["5"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_nice5" id="r_nice5" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_mistake5" id="r_mistake5" value = " 失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["6"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_nice6" id="r_nice6" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_mistake6" id="r_mistake6" value = " 失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["libero"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_niceli" id="r_niceli" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="r_mistakeli" id="r_mistakeli" value = " 失誤">
                        </form>
                    </h5>
                </div>
                <div class="body-box body-box-item-3">
                    <h4>攻擊</h4>
                    <h5><?php echo $_POST["1"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_success1" id="a_success1" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_score1" id="a_score1" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_mistake1" id="a_mistake1" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["2"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_success2" id="a_success2" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_score2" id="a_score2" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_mistake2" id="a_mistake2" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["3"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_success3" id="a_success3" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_score3" id="a_score3" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_mistake3" id="a_mistake3" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["4"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_success4" id="a_success4" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_score4" id="a_score4" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_mistake4" id="a_mistake4" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["5"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_success5" id="a_success5" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_score5" id="a_score5" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_mistake5" id="a_mistake5" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["6"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_success6" id="a_success6" value = "成功">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_score6" id="a_score6" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="a_mistake6" id="a_mistake6" value = "失誤">
                        </form>
                    </h5>
                    <h4>防守</h4>
                    <h5><?php echo $_POST["1"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_nice1" id="d_nice1" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_mistake1" id="d_mistake1" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["2"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_nice2" id="d_nice2" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_mistake2" id="d_mistake2" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["3"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_nice3" id="d_nice3" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_mistake3" id="d_mistake3" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["4"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_nice4" id="d_nice4" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_mistake4" id="d_mistake4" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["5"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_nice5" id="d_nice5" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_mistake5" id="d_mistake5" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["6"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_nice6" id="d_nice6" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_mistake6" id="d_mistake6" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["libero"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_niceli" id="d_niceli" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="d_mistakeli" id="d_mistakeli" value = "失誤">
                        </form>
                    </h5>
                </div>
                <div class="body-box body-box-item-4">
                    <h4>攔網</h4>
                    <h5><?php echo $_POST["1"] . "號"; ?><br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_touch1" id="b_touch1" value = "擊球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_mistake1" id="b_mistake1" value = "失誤">
                        </form>
                        <br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_score1" id="b_score1" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_seam1" id="b_seam1" value = "中洞">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["2"] . "號"; ?><br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_touch2" id="b_touch2" value = "擊球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_mistake2" id="b_mistake2" value = "失誤">
                        </form>
                        <br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_score2" id="b_score2" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_seam2" id="b_seam2" value = "中洞">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["3"] . "號"; ?><br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_touch3" id="b_touch3" value = "擊球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_mistake3" id="b_mistake3" value = "失誤">
                        </form>
                        <br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_score3" id="b_score3" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_seam3" id="b_seam3" value = "中洞">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["4"] . "號"; ?><br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_touch4" id="b_touch4" value = "擊球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_mistake4" id="b_mistake4" value = "失誤">
                        </form>
                        <br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_score4" id="b_score4" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_seam4" id="b_seam4" value = "中洞">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["5"] . "號"; ?><br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_touch5" id="b_touch5" value = "擊球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_mistake5" id="b_mistake5" value = "失誤">
                        </form>
                        <br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_score5" id="b_score5" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_seam5" id="b_seam5" value = "中洞">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["6"] . "號"; ?><br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_touch6" id="b_touch6" value = "擊球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_mistake6" id="b_mistake6" value = "失誤">
                        </form>
                        <br>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_score6" id="b_score6" value = "得分">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="b_seam6" id="b_seam6" value = "中洞">
                        </form>
                    </h5>
                    <h4>舉球/修正</h4>
                    <h5><?php echo $_POST["1"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_nice1" id="se_nice1" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_mistake1" id="se_mistake1" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["2"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_nice2" id="se_nice2" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_mistake2" id="se_mistake2" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["3"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_nice3" id="se_nice3" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_mistake3" id="se_mistake3" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["4"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_nice4" id="se_nice4" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_mistake4" id="se_mistake4" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["5"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_nice5" id="se_nice5" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_mistake5" id="se_mistake5" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["6"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_nice6" id="se_nice6" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_mistake6" id="se_mistake6" value = "失誤">
                        </form>
                    </h5>
                    <h5><?php echo $_POST["libero"] . "號"; ?>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_niceli" id="se_niceli" value = "好球">
                        </form>
                        <form method="POST" action="index.php" style="display: inline;">
                            <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                            <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                            <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                            <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                            <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                            <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                            <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                            <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                            <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                            <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                            <input type="submit" class="input-button" name="se_mistakeli" id="se_mistakeli" value = "失誤">
                        </form>
                    </h5>
                </div>
                <div class="body-box body-box-item-5">
                    <h4>
                        <div style="display: inline-block;">
                        對方失誤
                            <!-- form格式參考這邊，hidden的是你post之後仍會需要用到的值，用隱藏的方式傳過去 -->
                            <form method="POST" action="index.php" style="display: inline;">
                                <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                                <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                                <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                                <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                                <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                                <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                                <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                                <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                                <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                                <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                                <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                                <input type="submit" class="input-button" name="other_mistake" id="other_mistake" value="+">
                            </form>
                        </div>
                    </h4>
                    <h4>
                        <div style="display: inline-block;">
                        換人
                            <form method="POST" action="index.php" style="display: inline;">
                                <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                                <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                                <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                                <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                                <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                                <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                                <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                                <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                                <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                                <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                                <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                                <input type="submit" class="input-button" name="substi" id="substi" value="+">
                            </form>
                        </div>
                    </h4>
                    <h4>
                        暫停
                            <form method="POST" action="index.php" style="display: inline;">
                                <input type="hidden" name="home_team" value="<?php echo $_POST["home_team"]; ?>">
                                <input type="hidden" name="away_team" value="<?php echo $_POST["away_team"]; ?>">
                                <input type="hidden" name="set_num" value="<?php echo $_POST["set_num"]; ?>">
                                <input type="hidden" name="1" value="<?php echo $_POST["1"]; ?>">
                                <input type="hidden" name="2" value="<?php echo $_POST["2"]; ?>">
                                <input type="hidden" name="3" value="<?php echo $_POST["3"]; ?>">
                                <input type="hidden" name="4" value="<?php echo $_POST["4"]; ?>">
                                <input type="hidden" name="5" value="<?php echo $_POST["5"]; ?>">
                                <input type="hidden" name="6" value="<?php echo $_POST["6"]; ?>">
                                <input type="hidden" name="libero" value="<?php echo $_POST["libero"]; ?>">
                                <input type="hidden" name="match_id" value="<?php echo $match_id; ?>">
                                <input type="submit" class="input-button" name="timeout" id="timeout" value="+">
                            </form>
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <script src="js/main.js"></script>
        <!-- <footer>
            <p>Contact me</p>
            <p>黄相華 / Hsiang-Hua, HUANG</p>
            <p>NTNU TAHRD & CSIE</p>
            <p>-------------------------------------------------<p>
            <a href="https://www.instagram.com/hsiang_hua_/?igshid=OGQ5ZDc2ODk2ZA%3D%3D" target="_blank"><img src="image/instagram.png" width="30" id="instagram_image"></a>
            <a href="https://www.facebook.com/profile.php?id=100012710167273" target="_blank"><img src="image/facebook.png" width="30" id="facebook_image"></a>
            <a href="https://github.com/hsianghua" target="_blank"><img src="image/github.png" width="30" id="github_image"></a>
            <a href="https://www.linkedin.com/in/huang-hsiang-hua-46873a237" target="_blank"><img src="image/linkedin.png" width="30"></a>
        </footer> -->
    </body>

</html>


