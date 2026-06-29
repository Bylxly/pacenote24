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

# Präfix relativ zur aufrufenden Seite: Admin-Seiten liegen eine Ebene tiefer (/public/admin/),
# damit die Redirects unabhängig vom Installationsordner funktionieren.
function pagePrefix(): string
{
    return str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') ? '../' : '';
}

# Funktion um nicht eingeloggte Nutzer von index auf Login zu leiten
function requireAuth(): void
{
    if (!isAuthenticated()) {
        redirect(pagePrefix() . 'login.php?status=not_logged_in');
    }
}

# Funktion um Eingeloggte Nutzer von der Login Seite zu Index weiterzuleiten
function requireGuest(): void
{
    if (isAuthenticated()) {
        redirect(pagePrefix() . 'home.php');
    }
}

# Für Seiten welche zugriffsbeschränkt auf eine Bestimmte Rolle sind
function requireRole(int $role): void
{
    requireAuth();

    if (!hasRole($role)) {
        redirect(pagePrefix() . 'home.php');
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
        echo json_encode(['success' => false, 'error' => 'Benutzer nicht authentisiert']);
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
        echo json_encode(['success' => false, 'error' => 'Fehlende Berechtigung']);
        exit;
    }
}

# Ticket #94: Funktion um API Endpunkte auf Admins zu beschränken
function requireAdmin_API(): void
{
    requireAuth_API();
    if (!hasRole(ADMIN_ROLE_ID)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Fehlende Berechtigung']);
        exit;
    }
}