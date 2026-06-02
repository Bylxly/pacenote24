<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/RouteService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';

require_once __DIR__ . '/../../../app/session/guard.php';
requireAuth_API();

header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['owner_user_id', 'json_data']);

try {
    $service = new RouteService();

    if (isset($body['title'])) {
        Request::requireMaxLength($body, 'title', 100);
    }
    $title = $body['title'] ?? null;

    Request::requirePositiveInt($body, 'owner_user_id');

    $waypoints  = isset($body['waypoints'])  ? json_encode($body['waypoints'])  : null;
    $distanceM  = isset($body['distance_m']) ? (int)$body['distance_m']         : null;

    $routeId = $service->createRoute($title, $body['owner_user_id'], json_encode($body['json_data']), $waypoints, $distanceM);

    if ($routeId === null) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Fehler beim Erstellen']);
        exit;
    }

    http_response_code(201);
    echo json_encode(['success' => true, 'route_id' => $routeId]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}