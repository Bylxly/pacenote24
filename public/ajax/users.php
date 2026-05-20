<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/services/UserService.php';

header('Content-Type: application/json');

try {
    $service = new UserService();

    if (isset($_GET['id'])) {

        if (!is_numeric($_GET['id']) || (int)$_GET['id'] <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Id muss eine positive Zahl > 0 sein']);
            exit;
        }

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