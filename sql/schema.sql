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


-- Insert sample data

-- users
INSERT INTO users (email, pw_hash) VALUES
('admin@test.de', '$2y$10$abcdefghijklmnopqrstuv'), 
('user1@test.de', '$2y$10$abcdefghijklmnopqrstuv'),
('user2@test.de', '$2y$10$abcdefghijklmnopqrstuv');

-- groups
INSERT INTO groups (name) VALUES
('Admins'),
('Testgruppe'),
('Freunde');

-- group members
INSERT INTO group_member (user_id, group_id) VALUES
(1, 1), -- admin in Admins
(2, 2),
(3, 2),
(2, 3);

-- tracks
INSERT INTO tracks (owner_user_id, json_data, title) VALUES
(1, '{}', 'Teststrecke 1'),
(2, '{}', 'Training Route'),
(3, '{}', 'Rallye Demo');
-- tracks_visible_users
INSERT INTO track_visible_user (user_id, track_id) VALUES
(2, 1),
(3, 1),
(1, 2);

-- track_visible_group
INSERT INTO track_visible_group (group_id, track_id) VALUES
(2, 1),
(3, 2);

-- sessions
INSERT INTO sessions (user_id, timeout) VALUES
(1, DATE_ADD(NOW(), INTERVAL 30 MINUTE)),
(2, DATE_ADD(NOW(), INTERVAL 30 MINUTE));