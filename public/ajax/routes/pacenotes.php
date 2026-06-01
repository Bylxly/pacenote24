<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/RouteService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';
require_once __DIR__ . '/../../../app/session/guard.php';

header('Content-Type: application/json');

$service = new RouteService();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    requireAuth_API();

    if (!isset($_GET['id']) || (int)$_GET['id'] <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id erforderlich']);
        exit;
    }

    $route = $service->getRouteById((int)$_GET['id']);

    if ($route === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Route nicht gefunden']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $route['pacenotes_data'] ?? null]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAuth_API();

    $body = Request::getBody();
    Request::requireFields($body, ['id', 'pacenotes_data']);
    Request::requirePositiveInt($body, 'id');

    $route = $service->getRouteById((int)$body['id']);

    if ($route === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Route nicht gefunden']);
        exit;
    }

    if ($route['owner_user_id'] !== $_SESSION['account_id'] && !hasRole(ADMIN_ROLE_ID)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        exit;
    }

    $jsonData = json_encode($body['pacenotes_data']);

    $updated = $service->updatePacenotes((int)$body['id'], $jsonData);

    if (!$updated) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Pacenotes konnten nicht gespeichert werden']);
        exit;
    }

    echo json_encode(['success' => true]);

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt']);
}