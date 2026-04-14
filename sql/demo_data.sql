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