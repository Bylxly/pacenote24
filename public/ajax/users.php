<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/services/UserService.php';
require_once __DIR__ . '/../../app/helpers/Request.php';

require_once __DIR__ . '/../../app/session/guard.php';
requireAdmin();

header('Content-Type: application/json');

try {
    $service = new UserService();

    if (isset($_GET['id'])) {

        Request::requirePositiveInt($_GET, 'id');

        $user = $service->getUserById((int)$_GET['id']);

        if ($user === null) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Benutzer nicht gefunden']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        $users = $service->getAllUsers();
        echo json_encode(['success' => true, 'data' => $users]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}