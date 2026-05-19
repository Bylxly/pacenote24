<?php
require_once __DIR__ . '/Database.php';

class TrackVisibleGroupService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllTrackVisibleGroups(): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM track_visible_group'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTrackVisibleGroup(int $groupId, int $trackId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM track_visible_group
             WHERE group_id = :group_id
             AND route_id = :route_id'
        );

        $stmt->execute([
            'group_id' => $groupId,
            'route_id' => $trackId
        ]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getTrackVisibleGroupsByGroupId(int $groupId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM track_visible_group WHERE group_id = :group_id'
        );

        $stmt->execute(['group_id' => $groupId]);

        return $stmt->fetchAll();
    }

    public function getTrackVisibleGroupsByTrackId(int $trackId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM track_visible_group WHERE route_id = :route_id'
        );

        $stmt->execute(['route_id' => $trackId]);

        return $stmt->fetchAll();
    }

    public function createTrackVisibleGroup(int $groupId, int $trackId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO track_visible_group (group_id, route_id)
             VALUES (:group_id, :route_id)'
        );

        $stmt->execute([
            'group_id' => $groupId,
            'route_id' => $trackId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteTrackVisibleGroup(int $groupId, int $trackId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM track_visible_group
             WHERE group_id = :group_id
             AND route_id = :route_id'
        );

        $stmt->execute([
            'group_id' => $groupId,
            'route_id' => $trackId
        ]);

        return $stmt->rowCount() > 0;
    }
}