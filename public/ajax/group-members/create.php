<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/GroupMemberService.php';
require_once __DIR__ . '/../../../app/helpers/Request.php';

require_once __DIR__ . '/../../../app/session/guard.php';
requireAdmin();

header('Content-Type: application/json');

Request::requireMethod('POST');
$body = Request::getBody();
Request::requireFields($body, ['user_id', 'group_id']);

try {
    $service = new GroupMemberService();

    Request::requirePositiveInt($body, 'user_id');
    Request::requirePositiveInt($body, 'group_id');

    $service->createGroupMember($body['user_id'], $body['group_id']);

    http_response_code(201);
    echo json_encode(['success' => true, 'data' => ['user_id' => $body['user_id'], 'group_id' => $body['group_id']]]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}