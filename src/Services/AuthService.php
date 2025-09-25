<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Services;

use Binjuhor\SambasafetyApi\SambaSafetyClient;
use Binjuhor\SambasafetyApi\Exceptions\AuthenticationException;

class AuthService
{
    private SambaSafetyClient $client;

    public function __construct(SambaSafetyClient $client)
    {
        $this->client = $client;
    }

    public function login(string $username, string $password): array
    {
        $response = $this->client->post('/auth/login', [
            'username' => $username,
            'password' => $password,
        ]);

        if (!isset($response['access_token'])) {
            throw new AuthenticationException('Login failed: No access token received');
        }

        return [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'] ?? null,
            'expires_in' => $response['expires_in'] ?? 3600,
            'token_type' => $response['token_type'] ?? 'Bearer',
        ];
    }

    public function refreshToken(string $refreshToken): array
    {
        $response = $this->client->post('/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        if (!isset($response['access_token'])) {
            throw new AuthenticationException('Token refresh failed: No access token received');
        }

        return [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'] ?? $refreshToken,
            'expires_in' => $response['expires_in'] ?? 3600,
            'token_type' => $response['token_type'] ?? 'Bearer',
        ];
    }

    public function logout(): bool
    {
        try {
            $this->client->post('/auth/logout');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCurrentUser(): array
    {
        return $this->client->get('/auth/user');
    }

    public function validateToken(): bool
    {
        try {
            $this->client->get('/auth/validate');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}