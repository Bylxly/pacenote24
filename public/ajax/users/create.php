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

if (!isset($body['email'], $body['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'email und password erforderlich']);
    exit;
}

try {
    $service = new UserService();

    if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'email ungültig']);
        exit;
    }

    if (strlen($body['email']) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'email zu lang']);
        exit;
    }

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