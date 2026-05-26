<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/services/GroupMemberService.php';
require_once __DIR__ . '/../../app/helpers/Request.php';

require_once __DIR__ . '/../../app/session/guard.php';
requireAuth_API();

header('Content-Type: application/json');

try {
    $service = new GroupMemberService();

    if (isset($_GET['user_id'])) {
        Request::requirePositiveInt($_GET, 'user_id');
        $groups = $service->getGroupMembersByUserId((int)$_GET['user_id']);

        if (empty($groups)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Keine Gruppenmitgliedschaft gefunden']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $groups]);
    } elseif (isset($_GET['group_id'])) {
        Request::requirePositiveInt($_GET, 'group_id');
        $members = $service->getGroupMembersByGroupId((int)$_GET['group_id']);

        if (empty($members)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Keine Gruppenmitglieder gefunden']);
            exit;
        }
        echo json_encode(['success' => true, 'data' => $members]);
    } else {
        $group_members = $service->getAllGroupMembers();
        echo json_encode(['success' => true, 'data' => $group_members]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}