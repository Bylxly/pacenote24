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

if (!isset($body['owner_user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'owner_user_id erforderlich']);
    exit;
}

if (!isset($body['json_data'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'json_data erforderlich']);
    exit;
}

try {
    $service = new RouteService();

    $routeId = $service->createRoute($body['title'], $body['owner_user_id'], json_encode($body['json_data']));

    if ($routeId === null) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Tja']);
        exit;
    }

    http_response_code(201);
    echo json_encode(['success' => true, 'route_id' => $routeId]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}