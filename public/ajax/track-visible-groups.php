<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/services/TrackVisibleGroupService.php';
require_once __DIR__ . '/../../app/helpers/Request.php';

require_once __DIR__ . '/../../app/session/guard.php';
requireAuth_API();

header('Content-Type: application/json');

try {
    $service = new TrackVisibleGroupService();

    if (isset($_GET['route_id'])) {
        Request::requirePositiveInt($_GET, 'route_id');
        $groups = $service->getTrackVisibleGroupsByTrackId((int)$_GET['route_id']);

        if (empty($groups)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Keine Gruppen-Route-Verbindungen gefunden']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $groups]);
    } elseif (isset($_GET['group_id'])) {
        Request::requirePositiveInt($_GET, 'group_id');
        $members = $service->getTrackVisibleGroupsByGroupId((int)$_GET['group_id']);

        if (empty($members)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Keine Gruppen-Route-Verbindungen gefunden']);
            exit;
        }
        echo json_encode(['success' => true, 'data' => $members]);
    } else {
        $group_members = $service->getAllTrackVisibleGroups();
        echo json_encode(['success' => true, 'data' => $group_members]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}