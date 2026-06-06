<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/helpers/Request.php';
require_once __DIR__ . '/../../../app/services/UserService.php';
require_once __DIR__ . '/../../../app/services/SessionService.php';
require_once __DIR__ . '/../../../app/session/auth.php';

header('Content-Type: application/json');

Request::requireMethod('POST');

$body = Request::getBody();
Request::requireFields($body, ['email', 'password']);
Request::requireValidEmail($body, 'email');
Request::requireMaxLength($body, 'email', 100);

try {

    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])[A-Za-z\d\S]{8,128}$/';

    if (!preg_match($passwordRegex, $body['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' =>
            'Das Passwort muss zwischen 8 und 128 Zeichen lang sein und jeweils mindestens einen Großbuchstaben, einen Kleinbuchstaben, eine Ziffer sowie ein Sonderzeichen enthalten.']);
        exit;
    }
    $pw_hash = password_hash($body['password'], PASSWORD_BCRYPT);

    $userService = new UserService();

    $user_id = $userService->createUser($body['email'], $pw_hash);
    if ($user_id === null) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'E-Mail ist bereits registriert']);
        exit;
    }

    http_response_code(201);
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}