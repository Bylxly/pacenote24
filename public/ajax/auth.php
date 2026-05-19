<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/services/Database.php';

function db(): PDO {
    return Database::getConnection();
}

# Gibt zurück ob das session token noch aktuell ist
function isAuthenticated(): bool
{
        return isset($_SESSION['account_id']);
}

# Gibt den aktuell angemeldeten Nutzer zurück bzw null wenn keiner angemeldet
function currentUser(): ?array
{
    if (!isAuthenticated()) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT user_id, email FROM users WHERE user_id = :user_id'
    );

    $stmt->execute(['user_id' => $_SESSION['account_id']]);
    
    $user = $stmt->fetch();

    if(!$user) {
        logoutUser();
        return null;
    }

    return $user;
}

# Hilfsfunktion welche Prüft ob eine Rolle an angemeldeten Nutzer vergeben ist
function hasRole(string $role): bool
{
    $stmt = db()->prepare(
        'SELECT 1
        FROM group_member
        INNER JOIN groups ON groups.group_id = group_member.group_id
        WHERE group_member.user_id = :user_id
        AND groups.name = :role
        LIMIT 1'
    );

    $stmt->execute([
        'user_id' => $_SESSION['account_id'] ?? 0,
        'role' => $role,
    ]);

    return $stmt->fetch() !== false;
}