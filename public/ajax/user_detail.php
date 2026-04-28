<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/ApiClient.php';

$apiClient = new ApiClient('http://localhost');

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($userId === false || $userId === null) {
    http_response_code(400);
    exit('Ungültige User-ID.');
}

$successMsg = '';
$errorMsg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    try {
        $sessions = $apiClient->get('/ajax/sessions.php', ['user_id' => $userId]);
        foreach ($sessions['data'] ?? [] as $session) {
            $apiClient->post('/ajax/sessions/delete.php', ['id' => $session['session_id']]);
        }

        $memberships = $apiClient->get('/ajax/group-members.php', ['user_id' => $userId]);
        foreach ($memberships['data'] ?? [] as $membership) {
            $apiClient->post('/ajax/group-members/delete.php', [
                'user_id'  => $membership['user_id'],
                'group_id' => $membership['group_id'],
            ]);
        }

        $grantedVisibilities = $apiClient->get('/ajax/track-visible-users.php', [
            'user_id' => $userId,
        ]);
        foreach ($grantedVisibilities['data'] ?? [] as $visibility) {
            $apiClient->post('/ajax/track-visible-users/delete.php', $visibility);
        }

        $ownedTracks = $apiClient->get('/ajax/routes.php', ['owner_user_id' => $userId]);
        foreach ($ownedTracks['data'] ?? [] as $track) {
            $trackId = $track['track_id'];

            $userVis = $apiClient->get('/ajax/track-visible-users.php', ['track_id' => $trackId]);
            foreach ($userVis['data'] ?? [] as $visibility) {
                $apiClient->post('/ajax/track-visible-users/delete.php', $visibility);
            }

            $groupVis = $apiClient->get('/ajax/track-visible-groups.php', ['track_id' => $trackId]);
            foreach ($groupVis['data'] ?? [] as $visibility) {
                $apiClient->post('/ajax/track-visible-groups/delete.php', $visibility);
            }

            $apiClient->post('/ajax/routes/delete.php', ['id' => $trackId]);
        }

        $deletion = $apiClient->post('/ajax/users/delete.php', ['id' => $userId]);

        if (!empty($deletion['success'])) {
            header('Location: adminpanel.php?msg=deleted');
            exit;
        }

        $errorMsg = 'Löschung fehlgeschlagen: '
                  . htmlspecialchars($deletion['error'] ?? 'Unbekannter Fehler.');
    } catch (RuntimeException $e) {
        $errorMsg = 'API-Kommunikationsfehler: ' . htmlspecialchars($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    try {
        $update = $apiClient->post('/ajax/users/update.php', [
            'id'       => $userId,
            'email'    => trim((string) $_POST['email']),
            'password' => '__unchanged__',
        ]);

        if (!empty($update['success'])) {
            $successMsg = 'Profil erfolgreich aktualisiert.';
        } else {
            $errorMsg = htmlspecialchars($update['error'] ?? 'Aktualisierung fehlgeschlagen.');
        }
    } catch (RuntimeException $e) {
        $errorMsg = 'API-Kommunikationsfehler: ' . htmlspecialchars($e->getMessage());
    }
}

try {
    $userResponse = $apiClient->get('/ajax/users.php', ['id' => $userId]);
} catch (RuntimeException $e) {
    http_response_code(502);
    exit('API nicht erreichbar.');
}

if (empty($userResponse['success'])) {
    http_response_code(404);
    exit('Nutzer nicht gefunden.');
}

$user = $userResponse['data'];
?>
<a href="adminpanel.php">← Zurück zur Übersicht</a>

<?php if ($successMsg !== ''): ?>
    <div style="color: green;"><?= $successMsg ?></div>
<?php endif; ?>
<?php if ($errorMsg !== ''): ?>
    <div style="color: red;"><?= $errorMsg ?></div>
<?php endif; ?>

<h2>Profil verwalten</h2>
<form method="POST">
    <label>User ID:</label><br>
    <input type="text" value="<?= htmlspecialchars((string) $user['user_id']) ?>" disabled><br>

    <label>E-Mail Adresse:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

    <button type="submit" name="update">Speichern</button>
</form>

<h3>Gefahrenbereich</h3>
<p>Das Löschen eines Nutzers kann nicht rückgängig gemacht werden.</p>
<form method="POST" onsubmit="return confirm('Bist du dir wirklich sicher, dass du diesen User unwiderruflich löschen möchtest?');">
    <button type="submit" name="delete_user">Nutzer unwiderruflich löschen</button>
</form>