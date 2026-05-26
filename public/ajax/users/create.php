<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/UserService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';

require_once __DIR__ . '/../../../app/session/guard.php';
requireAdmin();

header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['email', 'password']);

try {
    $service = new UserService();

    Request::requireValidEmail($body, 'email');
    Request::requireMaxLength($body, 'email', 100);

    $passwordRegex = '/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\w\d:])([^\s]){8,}$/';

    if (!preg_match($passwordRegex, $body['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' =>
            'Passwort muss mindestens 8 Zeichen, einen Großbuchstaben, einen Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten']);
        exit;
    }

    $pwHash = password_hash($body['password'], PASSWORD_BCRYPT);
    $userId = $service->createUser($body['email'], $pwHash);

    if ($userId === null) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'E-Mail existiert bereits']);
        exit;
    }

    http_response_code(201);
    echo json_encode(['success' => true, 'user_id' => $userId]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}