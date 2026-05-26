<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/services/TrackVisibleUserService.php';
require_once __DIR__ . '/../../app/helpers/Request.php';

header('Content-Type: application/json');

try {
    $service = new TrackVisibleUserService();

    if (isset($_GET['route_id'])) {
        Request::requirePositiveInt($_GET, 'route_id');
        $groups = $service->getTrackVisibleUsersByTrackId((int)$_GET['route_id']);

        if (empty($groups)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Keine User-Route-Verbindungen gefunden']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $groups]);
    } elseif (isset($_GET['user_id'])) {
        Request::requirePositiveInt($_GET, 'user_id');
        $members = $service->getTrackVisibleUsersByUserId((int)$_GET['user_id']);

        if (empty($members)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Keine User-Route-Verbindungen gefunden']);
            exit;
        }
    } else {
        $group_members = $service->getAllTrackVisibleUsers();
        echo json_encode(['success' => true, 'data' => $group_members]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}