<?php
require_once __DIR__ . '/../../app/services/Database.php';

class GroupService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllGroups(): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM groups'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getGroupById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT * FROM groups WHERE group_id = :id'
        );

        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function createGroup(String $name): ?int {
        $stmt = $this->db->prepare(
            'INSERT INTO groups(name) VALUES (:name)'
        );
        $stmt->execute(['name' => $name]);
        $result = $this->db->lastInsertId();
        return $result ?: null;
    }

    public function updateGroup(int $id, String $name): ?bool {
        $stmt = $this->db->prepare(
            'UPDATE groups SET name = :name WHERE group_id = :id'
        );
        $stmt->execute(['id' => $id, 'name' => $name]);
        return $stmt->rowCount() > 0;
    }

    public function deleteGroup(int $id): ?bool {
        $stmt = $this->db->prepare(
            'DELETE FROM groups WHERE group_id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

}