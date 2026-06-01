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
    session_id VARCHAR(64) PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    timeout TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- tracks table
CREATE TABLE tracks (
    route_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    owner_user_id INT NOT NULL,
    compiled_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    json_data JSON NOT NULL,
    waypoints JSON NULL,
    distance_m INT NULL,
    pacenote_data JSON NULL,
    FOREIGN KEY (owner_user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- group_Member table
CREATE TABLE group_member (
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, group_id)
);

-- track_visible_user table
CREATE TABLE track_visible_user (
    user_id INT NOT NULL,
    route_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES tracks(route_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, route_id)
);

-- track_visible_group table
CREATE TABLE track_visible_group (
    group_id INT NOT NULL,
    route_id INT NOT NULL,
    FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES tracks(route_id) ON DELETE CASCADE,
    PRIMARY KEY (group_id, route_id)
);
