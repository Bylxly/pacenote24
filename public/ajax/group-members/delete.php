<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/GroupMemberService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';


header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['user_id', 'group_id']);

try {
    $service = new GroupMemberService();

    Request::requirePositiveInt($body, 'group_id');
    Request::requirePositiveInt($body, 'user_id');

    $deleted = $service->deleteGroupMember($body['user_id'], $body['group_id']);

    if (!$deleted) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Gruppenmitglied nicht gefunden']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}