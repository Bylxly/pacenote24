<?php
declare(strict_types=1);

require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../services/GroupMemberService.php';
require_once __DIR__ . '/../services/SessionService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict'
        ]);
    session_start();
}

# Gibt zurück ob das session token noch aktuell ist
function isAuthenticated(): bool
{
    if (!isset($_SESSION['account_id']) || !isset($_SESSION['user_name']) || !isset($_SESSION['token'])) return FALSE;
    $sessionService = new SessionService();
    if (!$sessionService->isSessionValid($_SESSION['token'])) return FALSE;
    $sessionService->extendSession($_SESSION['token']);
    return TRUE;
}

# Gibt den aktuell angemeldeten Nutzer zurück bzw null wenn keiner angemeldet
function currentUser(): ?array
{
    if (!isAuthenticated()) {
        return null;
    }

    $userService = new UserService();
    $user = $userService->getUserById($_SESSION['account_id']);

    if(!$user) {
        return null;
    }

    return $user;
}

# Hilfsfunktion welche Prüft ob eine Rolle an angemeldeten Nutzer vergeben ist
function hasRole(int $roleId): bool
{
    $groupMemberService = new GroupMemberService();
    return $groupMemberService->getGroupMember($_SESSION['account_id'], $roleId) !== null;
}