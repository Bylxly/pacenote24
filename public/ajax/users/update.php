<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/UserService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

if (!isset($body['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'id erforderlich']);
    exit;
}

if (!isset($body['email']) && !isset($body['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'email oder password erforderlich']);
    exit;
}

try {
    $service = new UserService();

    if (!is_numeric($body['id']) || (int)$body['id'] <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id muss eine positive Zahl > 0 sein']);
        exit;
    }

    $email  = $body['email'] ?? null;

    $passwordRegex = '/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\w\d:])([^\s]){8,}$/';

    if (isset($body['password']) && !preg_match($passwordRegex, $body['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' =>
            'Passwort muss mindestens 8 Zeichen, einen Großbuchstaben, einen Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten']);
        exit;
    }

    $pwHash = isset($body['password']) ? password_hash($body['password'], PASSWORD_BCRYPT) : null;

    if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'email ungültig']);
        exit;
    }

    if ($email !== null && strlen($email) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'email zu lang']);
        exit;
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