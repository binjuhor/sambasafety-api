<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Binjuhor\SambasafetyApi\Exceptions\SambaSafetyException;
use Binjuhor\SambasafetyApi\Exceptions\AuthenticationException;
use Binjuhor\SambasafetyApi\Exceptions\ValidationException;

class SambaSafetyClient
{
    private Client $httpClient;
    private string $baseUrl;
    private string $apiKey;
    private array $defaultHeaders;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.sambasafety.com/v1',
        array  $options = []
    )
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');

        $this->defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'SambaSafety-PHP-SDK/1.0.0',
        ];

        $clientOptions = array_merge([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => $this->defaultHeaders,
        ], $options);

        $this->httpClient = new Client($clientOptions);
    }

    public function get(string $endpoint, array $query = []): array
    {
        $options = [];
        if (!empty($query)) {
            $options[RequestOptions::QUERY] = $query;
        }
        return $this->makeRequest('GET', $endpoint, $options);
    }

    public function post(string $endpoint, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options[RequestOptions::JSON] = $data;
        }
        return $this->makeRequest('POST', $endpoint, $options);
    }

    public function put(string $endpoint, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options[RequestOptions::JSON] = $data;
        }
        return $this->makeRequest('PUT', $endpoint, $options);
    }

    public function patch(string $endpoint, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options[RequestOptions::JSON] = $data;
        }
        return $this->makeRequest('PATCH', $endpoint, $options);
    }

    public function delete(string $endpoint): array
    {
        return $this->makeRequest('DELETE', $endpoint);
    }

    private function makeRequest(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            return $this->parseResponse($response);
        } catch (GuzzleException $e) {
            $this->handleHttpException($e);
        }
    }

    private function parseResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();

        if ($statusCode >= 200 && $statusCode < 300) {
            $decoded = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new SambaSafetyException('Invalid JSON response: ' . json_last_error_msg());
            }

            return $decoded ?? [];
        }

        $this->handleErrorResponse($statusCode, $body);
    }

    private function handleHttpException(GuzzleException $e): void
    {
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = (string)$response->getBody();

            $this->handleErrorResponse($statusCode, $body);
        }

        throw new SambaSafetyException('HTTP request failed: ' . $e->getMessage(), 0, $e);
    }

    private function handleErrorResponse(int $statusCode, string $body): void
    {
        $error = json_decode($body, true);
        $message = $error['message'] ?? $error['error'] ?? 'Unknown error';

        switch ($statusCode) {
            case 401:
            case 403:
                throw new AuthenticationException($message, $statusCode);
            case 400:
            case 422:
                throw new ValidationException($message, $statusCode);
            default:
                throw new SambaSafetyException($message, $statusCode);
        }
    }
}
