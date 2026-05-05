-- group table
CREATE TABLE groups (
    group_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

-- user table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    pw_hash VARCHAR(255) NOT NULL
);

-- session table
CREATE TABLE sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    timeout TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- tracks table
CREATE TABLE tracks (
    track_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    owner_user_id INT NOT NULL,
    compiled_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    json_data JSON NOT NULL,
    FOREIGN KEY (owner_user_id) REFERENCES users(user_id)
);

-- group_Member table
CREATE TABLE group_member (
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (group_id) REFERENCES groups(group_id),
    PRIMARY KEY (user_id, group_id)
);

-- track_visible_user table
CREATE TABLE track_visible_user (
    user_id INT NOT NULL,
    track_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (track_id) REFERENCES tracks(track_id),
    PRIMARY KEY (user_id, track_id)
);

-- track_visible_group table
CREATE TABLE track_visible_group (
    group_id INT NOT NULL,
    track_id INT NOT NULL,
    FOREIGN KEY (group_id) REFERENCES groups(group_id),
    FOREIGN KEY (track_id) REFERENCES tracks(track_id),
    PRIMARY KEY (group_id, track_id)
);
