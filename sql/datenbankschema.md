Groups(GROUP_ID, name)
Users(USER_ID, email, pw_hash, salt)
Sessions(SESSION_ID, created_at, timeout, user_id#)
Tracks(TRACK_ID, title, json_data, compiled_time, user_id#)
group_member(USER_ID#, GROUP_ID#)
track_visible_group(GROUP_ID#, TRACK_ID#)
track_visible_user(USER_ID#, TRACK_ID#)


Groups(
  group_id PK,
  name
)

Users(
  user_id PK,
  email UNIQUE,
  pw_hash
)

Sessions(
  session_id PK,
  created_at,
  timeout,
  user_id FK -> Users(user_id)
)

Tracks(
  track_id PK,
  title,
  json_data,
  compiled_time,
  owner_user_id FK -> Users(user_id)
)

group_Member(
  user_id FK -> Users(user_id),
  group_id FK -> Groups(group_id),
  PRIMARY KEY (user_id, group_id)
)

track_visible_user(
  track_id FK -> Tracks(track_id),
  user_id FK -> Users(user_id),
  PRIMARY KEY (track_id, user_id)
)

track_visible_group(
  track_id FK -> Tracks(track_id),
  group_id FK -> Groups(group_id),
  PRIMARY KEY (track_id, group_id)
)