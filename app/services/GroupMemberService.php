<?php
require_once __DIR__ . '/../../app/services/Database.php';

class GroupMemberService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllGroupMembers(): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM group_member'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getGroupMember(int $userId, int $groupId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM group_member
             WHERE user_id = :user_id
             AND group_id = :group_id'
        );

        $stmt->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getGroupMembersByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM group_member WHERE user_id = :user_id'
        );

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getGroupMembersByGroupId(int $groupId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM group_member WHERE group_id = :group_id'
        );

        $stmt->execute(['group_id' => $groupId]);

        return $stmt->fetchAll();
    }

    public function createGroupMember(int $userId, int $groupId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO group_member (user_id, group_id)
             VALUES (:user_id, :group_id)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteGroupMember(int $userId, int $groupId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM group_member
             WHERE user_id = :user_id
             AND group_id = :group_id'
        );

        $stmt->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        return $stmt->rowCount() > 0;
    }
}
