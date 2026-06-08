<?php
require_once __DIR__ . '/Database.php';

class TrackVisibleUserService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllTrackVisibleUsers(): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM track_visible_user'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTrackVisibleUser(int $userId, int $trackId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM track_visible_user
             WHERE user_id = :user_id
             AND route_id = :route_id'
        );

        $stmt->execute([
            'user_id' => $userId,
            'route_id' => $trackId
        ]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getTrackVisibleUsersByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM track_visible_user WHERE user_id = :user_id'
        );

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getTrackVisibleUsersByTrackId(int $trackId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM track_visible_user WHERE route_id = :route_id'
        );

        $stmt->execute(['route_id' => $trackId]);

        return $stmt->fetchAll();
    }

    public function createTrackVisibleUser(int $userId, int $trackId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO track_visible_user (user_id, route_id)
             VALUES (:user_id, :route_id)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'route_id' => $trackId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteTrackVisibleUser(int $userId, int $trackId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM track_visible_user
             WHERE user_id = :user_id
             AND route_id = :route_id'
        );

        $stmt->execute([
            'user_id' => $userId,
            'route_id' => $trackId
        ]);

        return $stmt->rowCount() > 0;
    }
}