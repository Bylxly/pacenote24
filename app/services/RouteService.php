<?php
require_once __DIR__ . '/../../app/services/Database.php';

class RouteService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllRoutes(): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM tracks'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getRouteById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM tracks WHERE track_id = :id'
        );

        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getRoutesByOwnerUserId(int $ownerUserId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM tracks WHERE owner_user_id = :owner_user_id'
        );

        $stmt->execute(['owner_user_id' => $ownerUserId]);

        return $stmt->fetchAll();
    }

    public function createRoute(string $title, int $ownerUserId, string $jsonData): ?int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tracks (title, owner_user_id, compiled_time, json_data)
             VALUES (:title, :owner_user_id, CURRENT_TIMESTAMP, :json_data)'
        );

        $stmt->execute([
            'title' => $title,
            'owner_user_id' => $ownerUserId,
            'json_data' => $jsonData
        ]);

        return $this->db->lastInsertId() ? (int)$this->db->lastInsertId() : null;
    }

    public function updateRoute(int $id, string $title, string $jsonData): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE tracks
             SET title = :title,
                 compiled_time = CURRENT_TIMESTAMP,
                 json_data = :json_data
             WHERE track_id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'json_data' => $jsonData
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteRoute(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM tracks WHERE track_id = :id'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}