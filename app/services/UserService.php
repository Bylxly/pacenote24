<?php
require_once __DIR__ . '/Database.php';

class UserService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllUsers(): array {
        $stmt = $this->db->prepare(
            'SELECT user_id, email FROM users'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT user_id, email FROM users WHERE user_id = :id'
        );

        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function createUser(string $email, string $pwHash): ?int {
        $stmt = $this->db->prepare(
            'INSERT INTO users(email, pw_hash) VALUES (:email, :pw_hash)'
        );

        $stmt->execute(['email' => $email, 'pw_hash' => $pwHash]);

        $result = $this->db->lastInsertId();

        return $result === '' ? null : (int) $result;
    }

    public function updateUser(int $id, string $email, string $pwHash): bool {
        $stmt = $this->db->prepare(
            'UPDATE users SET email = :email, pw_hash = :pw_hash WHERE user_id = :id'
        );
        $stmt->execute(['id' => $id, 'email' => $email, 'pw_hash' => $pwHash]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return $this->getUserById($id) !== null;
    }

    public function deleteUser(int $id): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM users WHERE user_id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

}