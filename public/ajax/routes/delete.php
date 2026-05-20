<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/RouteService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';


header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['id']);

try {
    $service = new RouteService();

    Request::requirePositiveInt($body, 'id');

    $deleted = $service->deleteRoute($body['id']);

    if (!$deleted) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Route nicht gefunden']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}