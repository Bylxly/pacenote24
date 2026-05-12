<?php

use Random\RandomException;

require_once __DIR__ . '/Database.php';

class SessionService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllSessions(): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM sessions'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getSessionById(string $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM sessions WHERE session_id = :id'
        );

        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getSessionsByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM sessions WHERE user_id = :user_id'
        );

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * @throws RandomException
     */
    public function createSession(int $userId, string $timeout): ?int
    {
        $sessionId = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare(
            'INSERT INTO sessions (session_id, user_id, created_at, timeout)
             VALUES (:session_id, :user_id, CURRENT_TIMESTAMP, :timeout)'
        );

        $stmt->execute([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'timeout' => $timeout
        ]);

        $lastInsertId = $this->db->lastInsertId();

        return $lastInsertId ? (int)$lastInsertId : null;
    }

    public function updateSession(string $id, string $timeout): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE sessions
             SET timeout = :timeout
             WHERE session_id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'timeout' => $timeout
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteSession(string $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM sessions WHERE session_id = :id'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
