<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/TrackVisibleGroupService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';

require_once __DIR__ . '/../../../app/session/guard.php';
requireAdmin();

header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['group_id', 'route_id']);

try {
    $service = new TrackVisibleGroupService();

    Request::requirePositiveInt($body, 'group_id');
    Request::requirePositiveInt($body, 'route_id');

    $deleted = $service->deleteTrackVisibleGroup($body['group_id'], $body['route_id']);

    if (!$deleted) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Gruppen-Route-Verbindung nicht gefunden']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}