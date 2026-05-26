<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/GroupService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';


header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['id', 'name']);

try {
    $service = new GroupService();

    Request::requirePositiveInt($body, 'id');

    if (empty($body['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Name darf nicht leer sein']);
        exit;
    }

    Request::requireMaxLength($body, 'name', 50);

    $updated = $service->updateGroup($body['id'], $body['name']);

    if (!$updated) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Gruppe nicht gefunden']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}