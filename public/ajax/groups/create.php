<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/services/GroupService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

if (!isset($body['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name erforderlich']);
    exit;
}

try {
    $service = new GroupService();

    if (empty($body['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Name darf nicht leer sein']);
        exit;
    }

    if (strlen($body['name']) > 50) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Name darf max. 50 Zeichen lang sein']);
        exit;
    }

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