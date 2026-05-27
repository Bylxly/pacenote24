<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/TrackVisibleUserService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';

require_once __DIR__ . '/../../../app/session/guard.php';
requireAdmin_API();

header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['user_id', 'route_id']);

try {
    $service = new TrackVisibleUserService();

    Request::requirePositiveInt($body, 'route_id');
    Request::requirePositiveInt($body, 'user_id');

    $service->createTrackVisibleUser($body['user_id'], $body['route_id']);

    http_response_code(201);
    echo json_encode(['success' => true, 'data' => ['user_id' => $body['user_id'], 'route_id' => $body['route_id']]]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}