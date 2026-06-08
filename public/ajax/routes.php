<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/services/RouteService.php';
require_once __DIR__ . '/../../app/helpers/Request.php';

require_once __DIR__ . '/../../app/session/guard.php';
requireAuth_API();

header('Content-Type: application/json');

try {
    $service = new RouteService();

    if (isset($_GET['id'])) {

        Request::requirePositiveInt($_GET, 'id');

        $route = $service->getRouteById((int)$_GET['id']);

        if ($route === null) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Route nicht gefunden']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $route]);
    } elseif (isset($_GET['owner_user_id'])) {
        Request::requirePositiveInt($_GET, 'owner_user_id');
        $routes = $service->getRoutesByOwnerUserId((int)$_GET['owner_user_id']);
        echo json_encode(['success' => true, 'data' => $routes]);
    } elseif (isset($_GET['visible_for_user_id'])) {
        Request::requirePositiveInt($_GET, 'visible_for_user_id');
        $routes = $service->getRoutesByVisibleUserId((int)$_GET['visible_for_user_id']);
        echo json_encode(['success' => true, 'data' => $routes]);
    } elseif (isset($_GET['visible_for_group_id'])) {
        Request::requirePositiveInt($_GET, 'visible_for_group_id');
        $routes = $service->getRoutesByVisibleGroupId((int)$_GET['visible_for_group_id']);
        echo json_encode(['success' => true, 'data' => $routes]);
    } else {
        if (hasRole(ADMIN_ROLE_ID)) {
            $routes = $service->getAllRoutes();
        }
        else {
            $routes = $service->getAccessibleRoutes($_SESSION['account_id']);
        }
        echo json_encode(['success' => true, 'data' => $routes]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}