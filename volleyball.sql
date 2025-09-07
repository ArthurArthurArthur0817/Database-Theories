CREATE TABLE user (
    username VARCHAR(20) PRIMARY KEY,
    user_password VARCHAR(20)
) ENGINE=INNODB;

CREATE TABLE player (
    player_name VARCHAR(30) PRIMARY KEY,
    school VARCHAR(30),
    player_number VARCHAR(20)
) ENGINE=INNODB;

CREATE TABLE coach (
    coach_name VARCHAR(30) PRIMARY KEY,
    school VARCHAR(30),
    win_match INT,
    total_match INT
) ENGINE=INNODB;

CREATE TABLE match_info (
    match_id INT AUTO_INCREMENT PRIMARY KEY,
    match_name VARCHAR(30),
    match_date VARCHAR(20),
    match_result VARCHAR(30),
    match_format VARCHAR(30),
    home_team VARCHAR(20),
    away_team VARCHAR(20),
    current_home_score INT DEFAULT 0,
    current_away_score INT DEFAULT 0,
    current_set INT DEFAULT 1,
    current_home_set INT DEFAULT 0,
    current_away_set INT DEFAULT 0
) ENGINE=INNODB;

CREATE TABLE lineup_sheet (
    set_number INT,
    match_id INT,
    player_one VARCHAR(20),
    player_two VARCHAR(20),
    player_three VARCHAR(20),
    player_four VARCHAR(20),
    player_five VARCHAR(20),
    player_six VARCHAR(20),
    libero VARCHAR(20),
    PRIMARY KEY (set_number, match_id),
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE participate (
    player_name VARCHAR(30),
    match_id INT,
    PRIMARY KEY (player_name, match_id),
    FOREIGN KEY (player_name) REFERENCES player(player_name) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE user_record (
    user_username VARCHAR(20),
    match_id INT,
    PRIMARY KEY (user_username, match_id),
    FOREIGN KEY (user_username) REFERENCES user(username) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE coaching (
    coach_name VARCHAR(30),
    match_id INT,
    PRIMARY KEY (coach_name, match_id),
    FOREIGN KEY (coach_name) REFERENCES coach(coach_name) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE error_substitution_timeout (
    set_number INT,
    match_id INT,
    away_error INT DEFAULT 0,
    substitution INT DEFAULT 0,
    time_out INT DEFAULT 0,
    PRIMARY KEY (set_number, match_id),
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE set_correction (
    set_number INT,
    match_id INT,
    player_number VARCHAR(20),
    success_error VARCHAR(30),
    current_home_score INT,
    current_away_score INT,
    PRIMARY KEY (set_number, match_id, player_number, current_home_score, current_away_score),
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE block (
    set_number INT,
    match_id INT,
    player_number VARCHAR(20),
    success_error VARCHAR(30),
    current_home_score INT,
    current_away_score INT,
    PRIMARY KEY (set_number, match_id, player_number, current_home_score, current_away_score),
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE defense (
    set_number INT,
    match_id INT,
    player_number VARCHAR(20),
    success_error VARCHAR(30),
    current_home_score INT,
    current_away_score INT,
    PRIMARY KEY (set_number, match_id, player_number, current_home_score, current_away_score),
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE attack (
    set_number INT,
    match_id INT,
    player_number VARCHAR(20),
    success_error_score VARCHAR(30),
    current_home_score INT,
    current_away_score INT,
    PRIMARY KEY (set_number, match_id, player_number, current_home_score, current_away_score),
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE receive (
    set_number INT,
    match_id INT,
    player_number VARCHAR(20),
    success_error_score VARCHAR(30),
    current_home_score INT,
    current_away_score INT,
    PRIMARY KEY (set_number, match_id, player_number, current_home_score, current_away_score),
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;

CREATE TABLE serve (
    set_number INT,
    match_id INT,
    player_number VARCHAR(20),
    success_error_score VARCHAR(30),
    current_home_score INT,
    current_away_score INT,
    PRIMARY KEY (set_number, match_id, player_number, current_home_score, current_away_score),
    FOREIGN KEY (match_id) REFERENCES match_info(match_id) ON DELETE CASCADE
) ENGINE=INNODB;
