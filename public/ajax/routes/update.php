<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/RouteService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';


header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['id']);
Request::requireAtLeastOneField($body, ['title', 'json_data']);

try {
    $service = new RouteService();

    Request::requirePositiveInt($body, 'id');

    $title = $body['title'] ?? null;

    $jsonData = isset($body['json_data']) ? json_encode($body['json_data']) : null;

    if ($title !== null) {
        Request::requireMaxLength($body, 'title', 100);
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