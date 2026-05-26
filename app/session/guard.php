<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
const ADMIN_ROLE_ID = 1;
# Hilfsfunktion für nachfolgende funktionen
function redirect(string $path): never
{
        header('Location: ' . $path);
        exit;
}

# Funktion um nicht eingeloggte Nutzer von index auf Login zu leiten
function requireAuth(): void
{
    if (!isAuthenticated()) {
        redirect('/pacenote24/public/login.php?status=not_logged_in');
    }
}

# Funktion um Eingeloggte Nutzer von der Login Seite zu Index weiterzuleiten
function requireGuest(): void
{
    if (isAuthenticated()) {
        redirect('/pacenote24/public/index.php');
    }
}

# Für Seiten welche zugriffsbeschränkt auf eine Bestimmte Rolle sind
function requireRole(int $role): void
{
    requireAuth();

    if (!hasRole($role)) {
        redirect('/pacenote24/public/index.php');
    }
}

# Hilfsfunktion aus requireRole aber für Admin
function requireAdmin(): void
{
    requireRole(ADMIN_ROLE_ID); # Rolle wird wahrscheinlich umbenannt
}

# Prüft bei anfragen auf Athentifizierungsstatus
function requireAuth_API(): void
{
    if (!isAuthenticated()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Nicht authentisiert', 'code' => 401]);
        exit;
    }
}

# Prüft bei anfragen auf berechtigung mittels adminprüfung und ob es der eigene nutzer ist
function requireSelforAdmin(int $targetUserId): void
{
    requireAuth_API();
    if ($_SESSION['account_id'] !== $targetUserId && !hasRole(ADMIN_ROLE_ID)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Fehlende Berechtigung', 'code' => 403]);
        exit;
    }
}