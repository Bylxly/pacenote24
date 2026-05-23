<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/services/GroupMemberService.php';

header('Content-Type: application/json');

try {
    $service = new GroupMemberService();

    if (isset($_GET['id'])) {
        $groups = $service->getGroupMembersByUserId((int)$_GET['id']);

        if (empty($groups)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Keine Gruppenmitgliedschaft gefunden']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $groups]);
    } elseif (isset($_GET['group_id'])) {
        $members = $service->getGroupMembersByGroupId((int)$_GET['group_id']);

        if (empty($members)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Keine Gruppenmitglieder gefunden']);
            exit;
        }
    } else {
        $group_members = $service->getAllGroupMembers();
        echo json_encode(['success' => true, 'data' => $group_members]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}