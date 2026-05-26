<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/UserService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';


header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['id']);
Request::requireAtLeastOneField($body, ['email', 'password']);

require_once __DIR__ . '/../../../app/session/guard.php';
requireSelforAdmin($body['id']);

try {
    $service = new UserService();

    Request::requirePositiveInt($body, 'id');

    $email  = $body['email'] ?? null;

    $passwordRegex = '/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\w\d:])([^\s]){8,}$/';

    if (isset($body['password']) && !preg_match($passwordRegex, $body['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' =>
            'Passwort muss mindestens 8 Zeichen, einen Großbuchstaben, einen Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten']);
        exit;
    }

    $pwHash = isset($body['password']) ? password_hash($body['password'], PASSWORD_BCRYPT) : null;

    if ($email !== null) {
        Request::requireValidEmail($body, 'email');
        Request::requireMaxLength($body, 'email', 100);
    }

    $updated = $service->updateUser((int)$body['id'], $email, $pwHash);

    if (!$updated) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Benutzer nicht gefunden']);
        exit;
    }

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}