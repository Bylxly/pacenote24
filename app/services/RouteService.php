<?php
require_once __DIR__ . '/Database.php';

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
            'SELECT * FROM tracks ORDER BY compiled_time DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAccessibleRoutes(int $userId): ?array
    {
        $stmt = $this->db->prepare(
    'SELECT * FROM tracks
            WHERE owner_user_id = :uid1
            
            UNION
            
            SELECT t.* FROM tracks t
            JOIN track_visible_user tvu ON t.route_id = tvu.route_id
            WHERE tvu.user_id = :uid2
            
            UNION
            
            SELECT t.* FROM tracks t
            JOIN track_visible_group tvg ON t.route_id = tvg.route_id
            JOIN group_member gm        ON gm.group_id = tvg.group_id
            WHERE gm.user_id = :uid3'
        );

        $stmt->execute(['uid1' => $userId, 'uid2' => $userId, 'uid3' => $userId]);

        return $stmt->fetchAll() ?: null;
    }

    public function getRouteById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM tracks WHERE route_id = :id'
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

    public function createRoute(?string $title, int $ownerUserId, string $jsonData, ?string $waypoints = null, ?int $distanceM = null): ?int
    {
        if (json_decode($jsonData) === null) {
            return null;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO tracks (title, owner_user_id, compiled_time, json_data, waypoints, distance_m)
             VALUES (:title, :owner_user_id, CURRENT_TIMESTAMP, :json_data, :waypoints, :distance_m)'
        );

        $stmt->execute([
            'title'        => $title,
            'owner_user_id'=> $ownerUserId,
            'json_data'    => $jsonData,
            'waypoints'    => $waypoints,
            'distance_m'   => $distanceM,
        ]);

        $lastInsertId = $this->db->lastInsertId();
        return $lastInsertId ? (int)$lastInsertId : null;
    }

    public function updateRoute(int $id, ?string $title = null, ?string $jsonData = null): bool
    {
        if ($jsonData !== null && json_decode($jsonData) === null) {
            return false;
        }

        $fields = ['compiled_time = CURRENT_TIMESTAMP'];
        $params = ['id' => $id];

        if ($title !== null) {
            $fields[] = 'title = :title';
            $params['title'] = $title;
        }

        if ($jsonData !== null) {
            $fields[] = 'json_data = :json_data';
            $params['json_data'] = $jsonData;
        }

        $stmt = $this->db->prepare(
            'UPDATE tracks SET ' . implode(', ', $fields) . ' WHERE route_id = :id'
        );
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function deleteRoute(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM tracks WHERE route_id = :id'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function updatePacenotes(int $routeId, string $jsonData): bool
    {
        if (json_decode($jsonData) === null) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE tracks SET pacenotes_data = :pacenotes_data WHERE route_id = :id'
        );
        $stmt->execute(['pacenotes_data' => $jsonData, 'id' => $routeId]);
        return $stmt->rowCount() > 0;
    }

    public function getRoutePacenotes(int $routeId): ?string {
        $stmt = $this->db->prepare(
            'SELECT pacenotes_data FROM tracks WHERE route_id = :id'
        );
        $stmt->execute(['id' => $routeId]);
        $result = $stmt->fetch();
        return $result['pacenotes_data'] ?? null;
    }

    // visible_for_user_id -> JOIN auf track_visible_user
    public function getRoutesByVisibleUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.* FROM tracks t
         JOIN track_visible_user tvu ON t.route_id = tvu.route_id
         WHERE tvu.user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

// visible_for_group_id -> JOIN auf track_visible_group
    public function getRoutesByVisibleGroupId(int $groupId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.* FROM tracks t
         JOIN track_visible_group tvg ON t.route_id = tvg.route_id
         WHERE tvg.group_id = :group_id'
        );
        $stmt->execute(['group_id' => $groupId]);
        return $stmt->fetchAll();
    }
}