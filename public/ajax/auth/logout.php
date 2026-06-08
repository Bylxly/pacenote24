<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/helpers/Request.php';
require_once __DIR__ . '/../../../app/services/SessionService.php';
require_once __DIR__ . '/../../../app/session/guard.php';

header('Content-Type: application/json');

Request::requireMethod('POST');
requireAuth_API();

$sessionService = new SessionService();
$sessionService->deleteSession($_SESSION['token']);

$_SESSION = [];
session_destroy();

http_response_code(200);
echo json_encode(['success' => true]);