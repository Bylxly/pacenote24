<?php
/**
 * Lightweight JSON-over-HTTP client for the Pacenote24 internal API.
 *
 * Wraps PHP stream contexts to avoid a hard dependency on cURL and
 * provides uniform error handling, automatic JSON (de)serialisation,
 * and structured exception propagation.
 */

declare(strict_types=1);

final class ApiClient
{
    private string $baseUrl;
    private int    $timeoutSeconds;

    public function __construct(string $baseUrl, int $timeoutSeconds = 10)
    {
        $this->baseUrl        = rtrim($baseUrl, '/');
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * Issue a GET request and decode the JSON response.
     *
     * @param array<string, scalar> $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $url = $this->baseUrl . $path;
        if ($query !== []) {
            $url .= (str_contains($path, '?') ? '&' : '?') . http_build_query($query);
        }
        return $this->dispatch('GET', $url, null);
    }

    /**
     * Issue a POST request with a JSON body and decode the response.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function post(string $path, array $payload): array
    {
        return $this->dispatch('POST', $this->baseUrl . $path, $payload);
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array<string, mixed>
     */
    private function dispatch(string $method, string $url, ?array $payload): array
    {
        $context = stream_context_create([
            'http' => [
                'method'        => $method,
                'header'        => "Content-Type: application/json\r\n"
                                 . "Accept: application/json\r\n",
                'content'       => $payload !== null ? json_encode($payload) : null,
                'timeout'       => $this->timeoutSeconds,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            throw new RuntimeException("Request to {$url} failed.");
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : ['success' => false, 'error' => 'Malformed response'];
    }
}