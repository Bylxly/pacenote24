<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/ApiClient.php';

$apiClient = new ApiClient('http://localhost');

$feedback = ['type' => null, 'message' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email']    ?? ''));
    $password = (string)        ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $feedback = ['type' => 'error', 'message' => 'E-Mail und Passwort sind erforderlich.'];
    } else {
        try {
            $createResponse = $apiClient->post('/ajax/users/create.php', [
                'email'    => $email,
                'password' => $password,
            ]);

            if (!empty($createResponse['success']) && isset($createResponse['user_id'])) {
 
                $verification = $apiClient->get('/ajax/users.php', [
                    'id' => $createResponse['user_id'],
                ]);

                if (!empty($verification['success'])) {
                    $feedback = [
                        'type'    => 'success',
                        'message' => 'Nutzer erfolgreich angelegt (ID: '
                                   . (int) $createResponse['user_id'] . ').',
                    ];
                } else {
                    $feedback = [
                        'type'    => 'error',
                        'message' => 'Datensatz angelegt, aber Verifikation fehlgeschlagen.',
                    ];
                }
            } else {
                $feedback = [
                    'type'    => 'error',
                    'message' => $createResponse['error'] ?? 'Unbekannter Fehler.',
                ];
            }
        } catch (RuntimeException $e) {
            $feedback = ['type' => 'error', 'message' => 'API nicht erreichbar.'];
        }
    }
}
?>
<form method="POST">
    <label for="email">E-Mail:</label><br>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Passwort:</label><br>
    <input type="password" id="password" name="password" required><br><br>

    <input type="submit" value="Nutzer anlegen">
</form>

<?php if ($feedback['message'] !== null): ?>
    <div style="color: <?= $feedback['type'] === 'success' ? 'green' : 'red' ?>;">
        <?= htmlspecialchars($feedback['message']) ?>
    </div>
<?php endif; ?>