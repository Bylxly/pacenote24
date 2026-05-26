<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/UserService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';



header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['id']);

require_once __DIR__ . '/../../../app/session/guard.php';
requireSelforAdmin($body['id']);

try {
    $service = new UserService();

    Request::requirePositiveInt($body, 'id');

    $deleted = $service->deleteUser((int)$body['id']);

    if (!$deleted) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Benutzer nicht gefunden']);
        exit;
    }

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}