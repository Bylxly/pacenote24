<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/services/GroupService.php';
require_once __DIR__ . '/../../app/helpers/Request.php';


header('Content-Type: application/json');

try {
    $service = new GroupService();

    if (isset($_GET['id'])) {

        Request::requirePositiveInt($_GET, 'id');

        $group = $service->getGroupById((int)$_GET['id']);

        if ($group === null) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Gruppe nicht gefunden']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $group]);
    } else {
        $groups = $service->getAllGroups();
        echo json_encode(['success' => true, 'data' => $groups]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}