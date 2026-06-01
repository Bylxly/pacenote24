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

$userService = new UserService();
$user = $userService->getUserByEmail($body['email']);

if (!$user || !password_verify($body['password'], $user['pw_hash'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'E-Mail oder Passwort falsch']);
    exit;
}
try {
    $sessionService = new SessionService();
    $token = $sessionService->createSession($user['user_id']);

    if (!$token) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Session konnte nicht erstellt werden']);
        exit;
    }

    $_SESSION['account_id'] = $user['user_id'];
    $_SESSION['user_name']  = $user['email'];
    $_SESSION['token']      = $token;

    session_regenerate_id(true);

    http_response_code(200);
    echo json_encode(['success' => true, 'data' => ['token' => $token]]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}