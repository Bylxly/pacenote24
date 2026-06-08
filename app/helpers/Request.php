<?php
class Request
{
    public static function getBody(): array {
        $body = json_decode(file_get_contents('php://input'), true);

        if ($body === null) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Ungültiger JSON-Body']);
            exit;
        }

        return $body;
    }

    public static function requireMethod(string $method): void {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt']);
            exit;
        }
    }

    public static function requireFields(array $body, array $fields): void {
        foreach ($fields as $field) {
            if (!isset($body[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "$field erforderlich"]);
                exit;
            }
        }
    }

    public static function requirePositiveInt(array $body, string $field): void {
        if (!is_numeric($body[$field]) || (int)$body[$field] <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "$field muss eine positive Zahl > 0 sein"]);
            exit;
        }
    }

    public static function requireMaxLength(array $body, string $field, int $max): void {
        if (isset($body[$field]) && strlen($body[$field]) > $max) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "$field darf max. $max Zeichen lang sein"]);
            exit;
        }
    }

    public static function requireValidEmail(array $body, string $field): void {
        if (!filter_var($body[$field], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Ungültige E-Mail-Adresse"]);
            exit;
        }
    }

    public static function requireAtLeastOneField(array $body, array $fields): void
    {
        foreach ($fields as $field) {
            if (isset($body[$field])) {
                return;
            }
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'error' => implode(' oder ', $fields) . ' erforderlich']);
        exit;
    }
}