<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi;

use Binjuhor\SambasafetyApi\Services\AuthService;
use Binjuhor\SambasafetyApi\Exceptions\AuthenticationException;

class SambaSafetyAuth
{
    private string $baseUrl;
    private array $options;
    private ?array $tokenData = null;

    public function __construct(
        string $baseUrl = 'https://api.sambasafety.com/v1',
        array $options = []
    ) {
        $this->baseUrl = $baseUrl;
        $this->options = $options;
    }

    public function login(string $username, string $password): SambaSafety
    {
        // Create temporary client for authentication
        $tempClient = new SambaSafetyClient('temp', $this->baseUrl, $this->options);
        $authService = new AuthService($tempClient);

        $this->tokenData = $authService->login($username, $password);

        return new SambaSafety(
            $this->tokenData['access_token'],
            $this->baseUrl,
            $this->options
        );
    }

    public function loginWithApiKey(string $apiKey): SambaSafety
    {
        return new SambaSafety($apiKey, $this->baseUrl, $this->options);
    }

    public function refreshToken(string $refreshToken): SambaSafety
    {
        if ($this->tokenData === null) {
            throw new AuthenticationException('No token data available for refresh');
        }

        // Create temporary client for token refresh
        $tempClient = new SambaSafetyClient($this->tokenData['access_token'], $this->baseUrl, $this->options);
        $authService = new AuthService($tempClient);

        $this->tokenData = $authService->refreshToken($refreshToken);

        return new SambaSafety(
            $this->tokenData['access_token'],
            $this->baseUrl,
            $this->options
        );
    }

    public function getTokenData(): ?array
    {
        return $this->tokenData;
    }

    public function isTokenExpired(): bool
    {
        if ($this->tokenData === null) {
            return true;
        }

        if (!isset($this->tokenData['expires_in'])) {
            return false; // No expiry info, assume valid
        }

        // Assuming token includes issued_at timestamp or we track when it was received
        $expiresAt = time() + $this->tokenData['expires_in'];
        return time() >= ($expiresAt - 300); // Refresh 5 minutes before expiry
    }

    public static function create(string $baseUrl = 'https://api.sambasafety.com/v1'): self
    {
        return new self($baseUrl);
    }
}