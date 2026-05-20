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

try {
    $service = new UserService();

    if (!is_numeric($body['id']) || (int)$body['id'] <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id muss eine positive Zahl > 0 sein']);
        exit;
    }

    $deleted = $service->deleteUser((int)$body['id']);

    if (!$deleted) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Benutzer nicht gefunden']);
        exit;
    }

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}