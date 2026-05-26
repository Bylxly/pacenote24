<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/RouteService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';

require_once __DIR__ . '/../../../app/session/guard.php';
requireAuth_API();

header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['id']);
Request::requireAtLeastOneField($body, ['title', 'json_data']);

try {
    $service = new RouteService();

    Request::requirePositiveInt($body, 'id');

    // Route laden
    $route = $service->getRouteById((int)$body['id']);

    if ($route === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Route nicht gefunden']);
        exit;
    }

    // Prüfen ob eingeloggter User der Besitzer ist, oder Admin
    if ($route['owner_user_id'] !== $_SESSION['account_id'] && !hasRole('Admins')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        exit;
    }

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