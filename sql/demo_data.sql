-- Demo-Daten für Pacenote24
-- Login-Daten:
--   admin@test.de  / Admin123!
--   user1@test.de  / User123!
--   user2@test.de  / User123!

-- users
INSERT INTO users (email, pw_hash) VALUES
('admin@test.de', '$2y$10$eIAftJe69oENhRakztdDmu0I/2ZDj.NPHLUpUIup.6ysI.7Y6p4VO'),
('user1@test.de', '$2y$10$zUNioexc.ASL8e/3KctAX.J.AQh6ihcURdSV6NJ7qW67StoLuIUgO'),
('user2@test.de', '$2y$10$PCQCFewf5wJeKjIvlqN25OYwdiju0UmeWLN2sSEdz9ooXZW1tYGye');

-- groups (Admins group_id = 1)
INSERT INTO `groups` (name) VALUES
('Admins'),
('Testgruppe'),
('Freunde');

-- group members
INSERT INTO group_member (user_id, group_id) VALUES
(1, 1),
(2, 2),
(3, 2),
(2, 3);

-- tracks
INSERT INTO tracks (owner_user_id, json_data, title) VALUES
(1, '{}', 'Teststrecke 1'),
(2, '{}', 'Training Route'),
(3, '{}', 'Rallye Demo');

-- track_visible_user
INSERT INTO track_visible_user (user_id, route_id) VALUES
(2, 1),
(3, 1),
(1, 2);

-- track_visible_group
INSERT INTO track_visible_group (group_id, route_id) VALUES
(2, 1),
(3, 2);