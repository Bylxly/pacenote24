<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/helpers/Request.php';
require_once __DIR__ . '/../../../app/services/UserService.php';
require_once __DIR__ . '/../../../app/helpers/PasswordValidator.php';
require_once __DIR__ . '/../../../app/session/guard.php';
requireAuth_API();

header('Content-Type: application/json');

Request::requireMethod('POST');

$body = Request::getBody();
Request::requireFields($body, ['old_password', 'new_password']);

$userId = (int) $_SESSION['account_id'];

$userService = new UserService();
$user = $userService->getUserByEmail($_SESSION['user_name']);

if (!$user || !password_verify($body['old_password'], $user['pw_hash'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Altes Passwort ist falsch']);
    exit;
}


if (PasswordValidator::validate($body['new_password']) === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Neues Passwort muss zwischen 8 und 128 Zeichen lang sein und jeweils mindestens einen Großbuchstaben, einen Kleinbuchstaben, eine Ziffer sowie ein Sonderzeichen enthalten.']);
    exit;
}

try {
    $userService->updateUser($userId, pwHash: password_hash($body['new_password'], PASSWORD_BCRYPT));
    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}