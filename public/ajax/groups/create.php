<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/GroupService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';

require_once __DIR__ . '/../../../app/session/guard.php';
requireAdmin_API();

header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['name']);

try {
    $service = new GroupService();

    if (empty($body['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Name darf nicht leer sein']);
        exit;
    }

    Request::requireMaxLength($body, 'name', 50);

    $groupId = $service->createGroup($body['name']);

    if ($groupId === null) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Gruppenname existiert bereits']);
        exit;
    }

    http_response_code(201);
    echo json_encode(['success' => true, 'group_id' => $groupId]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}