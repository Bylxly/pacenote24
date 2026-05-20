<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/RouteService.php';

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

if (!isset($body['title']) && !isset($body['json_data'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'title oder json_data erforderlich']);
    exit;
}

try {
    $service = new RouteService();

    if (!is_numeric($body['id']) || (int)$body['id'] <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id muss eine positive Zahl > 0 sein']);
        exit;
    }

    $title = $body['title'] ?? null;

    if (isset($body['json_data']) && json_validate($body['json_data']) === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Das JSON Format ist nicht valide']);
        exit;
    }

    $jsonData = isset($body['json_data']) ? json_encode($body['json_data']) : null;

    if ($title !== null && strlen($title) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Titel darf max. 100 Zeichen lang sein']);
        exit;
    }

    $updated = $service->updateRoute((int)$body['id'], $title, $jsonData);

    if (!$updated) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Track nicht gefunden']);
        exit;
    }

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}