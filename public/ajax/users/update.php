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

if (!isset($body['id'], $body['email'], $body['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'id, email und password erforderlich']);
    exit;
}

try {
    $service = new UserService();

    $pwHash = password_hash($body['password'], PASSWORD_BCRYPT);
    $updated = $service->updateUser((int)$body['id'], $body['email'], $pwHash);

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