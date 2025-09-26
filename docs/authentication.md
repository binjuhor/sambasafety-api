# Authentication Guide

The SambaSafety PHP SDK supports multiple authentication methods to suit different integration scenarios.

## Overview

SambaSafety API authentication can work in several ways:

1. **API Key Authentication** - Most common, using a permanent API key
2. **Username/Password Login** - For applications requiring user authentication
3. **OAuth Token Flow** - For more complex authentication scenarios
4. **Session Management** - Handling token expiration and refresh

## Method 1: API Key Authentication

The simplest and most common authentication method:

### Basic Usage

```php
<?php

use Binjuhor\SambasafetyApi\SambaSafety;

// Direct initialization with API key
$sambaSafety = new SambaSafety('your-api-key-here');

// Or using static factory
$sambaSafety = SambaSafety::create('your-api-key-here');

// API key is automatically included in all requests
$drivers = $sambaSafety->drivers()->list();
```

### Environment Variables

Store your API key securely:

```bash
# .env file
SAMBASAFETY_API_KEY=your_actual_api_key_here
```

```php
$apiKey = $_ENV['SAMBASAFETY_API_KEY'] ?? getenv('SAMBASAFETY_API_KEY');
$sambaSafety = new SambaSafety($apiKey);
```

### Custom Base URL

```php
$sambaSafety = new SambaSafety(
    apiKey: 'your-api-key',
    baseUrl: 'https://sandbox-api.sambasafety.com/v1'  // Sandbox environment
);
```

## Method 2: Username/Password Authentication

For applications that need to authenticate users:

### Basic Login

```php
<?php

use Binjuhor\SambasafetyApi\SambaSafetyAuth;

// Create authentication instance
$auth = SambaSafetyAuth::create();

// Login with credentials
$sambaSafety = $auth->login('your-username', 'your-password');

// Use the authenticated SDK
$drivers = $sambaSafety->drivers()->list();

// Access token information
$tokenData = $auth->getTokenData();
echo "Access token: " . $tokenData['access_token'] . "\n";
echo "Expires in: " . $tokenData['expires_in'] . " seconds\n";
```

### Login with Custom Endpoint

```php
$auth = SambaSafetyAuth::create('https://sandbox-api.sambasafety.com/v1');
$sambaSafety = $auth->login('username', 'password');
```

## Method 3: Token Management

Handle token expiration and refresh:

### Token Refresh

```php
$auth = SambaSafetyAuth::create();
$sambaSafety = $auth->login('username', 'password');

// Check if token is expired
if ($auth->isTokenExpired()) {
    // Refresh the token
    $tokenData = $auth->getTokenData();
    $sambaSafety = $auth->refreshToken($tokenData['refresh_token']);

    echo "Token refreshed successfully!\n";
}

// Continue using the SDK
$drivers = $sambaSafety->drivers()->list();
```

### Automatic Token Refresh

```php
class SambaSafetyManager
{
    private $auth;
    private $sambaSafety;
    private $tokenData;

    public function __construct()
    {
        $this->auth = SambaSafetyAuth::create();
    }

    public function ensureAuthenticated(): SambaSafety
    {
        if ($this->sambaSafety === null || $this->auth->isTokenExpired()) {
            if ($this->tokenData && isset($this->tokenData['refresh_token'])) {
                // Refresh existing token
                $this->sambaSafety = $this->auth->refreshToken($this->tokenData['refresh_token']);
            } else {
                // Fresh login
                $this->sambaSafety = $this->auth->login(
                    $_ENV['SAMBASAFETY_USERNAME'],
                    $_ENV['SAMBASAFETY_PASSWORD']
                );
            }

            $this->tokenData = $this->auth->getTokenData();
        }

        return $this->sambaSafety;
    }

    public function getDrivers()
    {
        return $this->ensureAuthenticated()->drivers()->list();
    }
}

// Usage
$manager = new SambaSafetyManager();
$drivers = $manager->getDrivers();  // Automatically handles authentication
```

## Method 4: Session Validation

Validate and manage authentication sessions:

### Check Authentication Status

```php
$sambaSafety = SambaSafety::create('your-api-key');

// Validate current token/session
if ($sambaSafety->auth()->validateToken()) {
    echo "Authentication is valid\n";

    // Get current user information
    $userInfo = $sambaSafety->auth()->getCurrentUser();
    echo "Logged in as: " . $userInfo['name'] . "\n";
} else {
    echo "Authentication failed or expired\n";
}
```

### Logout

```php
// Properly logout and invalidate token
$success = $sambaSafety->auth()->logout();

if ($success) {
    echo "Logged out successfully\n";
} else {
    echo "Logout failed\n";
}
```

## Advanced Authentication Patterns

### Dependency Injection

```php
class FleetService
{
    public function __construct(private SambaSafety $sambaSafety) {}

    public function getActiveDrivers()
    {
        return $this->sambaSafety->drivers()
            ->query()
            ->whereActive()
            ->get();
    }
}

// Container configuration
$sambaSafety = new SambaSafety($_ENV['SAMBASAFETY_API_KEY']);
$fleetService = new FleetService($sambaSafety);
```

### Multi-Environment Setup

```php
class SambaSafetyFactory
{
    public static function create(string $environment = 'production'): SambaSafety
    {
        $config = match($environment) {
            'development' => [
                'api_key' => $_ENV['SAMBASAFETY_DEV_API_KEY'],
                'base_url' => 'https://dev-api.sambasafety.com/v1'
            ],
            'staging' => [
                'api_key' => $_ENV['SAMBASAFETY_STAGING_API_KEY'],
                'base_url' => 'https://staging-api.sambasafety.com/v1'
            ],
            'production' => [
                'api_key' => $_ENV['SAMBASAFETY_API_KEY'],
                'base_url' => 'https://api.sambasafety.com/v1'
            ]
        };

        return new SambaSafety($config['api_key'], $config['base_url']);
    }
}

// Usage
$sambaSafety = SambaSafetyFactory::create('development');
```

## Error Handling

Handle authentication errors properly:

```php
use Binjuhor\SambasafetyApi\Exceptions\AuthenticationException;

try {
    $auth = SambaSafetyAuth::create();
    $sambaSafety = $auth->login('username', 'wrong-password');

} catch (AuthenticationException $e) {
    switch ($e->getCode()) {
        case 401:
            echo "Invalid credentials\n";
            break;
        case 403:
            echo "Access forbidden - check permissions\n";
            break;
        default:
            echo "Authentication error: " . $e->getMessage() . "\n";
    }
}
```

## Security Best Practices

### 1. Secure Credential Storage

```php
// ❌ Don't hardcode credentials
$sambaSafety = new SambaSafety('sk_live_hardcoded_key');

// ✅ Use environment variables
$sambaSafety = new SambaSafety($_ENV['SAMBASAFETY_API_KEY']);
```

### 2. Token Storage

```php
// Store tokens securely (e.g., encrypted session, secure database)
class TokenManager
{
    public function storeToken(array $tokenData): void
    {
        // Encrypt before storing
        $encrypted = encrypt(json_encode($tokenData));
        $_SESSION['sambasafety_token'] = $encrypted;
    }

    public function retrieveToken(): ?array
    {
        if (!isset($_SESSION['sambasafety_token'])) {
            return null;
        }

        $decrypted = decrypt($_SESSION['sambasafety_token']);
        return json_decode($decrypted, true);
    }
}
```

### 3. API Key Rotation

```php
class ApiKeyManager
{
    public function rotateApiKey(): void
    {
        $oldSdk = new SambaSafety($_ENV['SAMBASAFETY_API_KEY']);

        // Generate new API key (assuming this endpoint exists)
        $newKeyData = $oldSdk->auth()->generateNewApiKey();

        // Update environment variable
        $this->updateEnvironmentVariable('SAMBASAFETY_API_KEY', $newKeyData['api_key']);

        // Test new key
        $newSdk = new SambaSafety($newKeyData['api_key']);
        $newSdk->drivers()->list(); // Test call

        echo "API key rotated successfully\n";
    }
}
```

## Configuration Examples

### Laravel Integration

```php
// config/services.php
'sambasafety' => [
    'api_key' => env('SAMBASAFETY_API_KEY'),
    'base_url' => env('SAMBASAFETY_BASE_URL', 'https://api.sambasafety.com/v1'),
],

// Service Provider
public function register()
{
    $this->app->singleton(SambaSafety::class, function ($app) {
        return new SambaSafety(
            config('services.sambasafety.api_key'),
            config('services.sambasafety.base_url')
        );
    });
}
```

### Symfony Integration

```yaml
# config/services.yaml
services:
    Binjuhor\SambasafetyApi\SambaSafety:
        arguments:
            $apiKey: '%env(SAMBASAFETY_API_KEY)%'
            $baseUrl: '%env(SAMBASAFETY_BASE_URL)%'
```

## Troubleshooting

### Common Issues

**Invalid API Key**
```php
// Verify your API key format
if (!preg_match('/^sk_[a-zA-Z0-9_]+$/', $apiKey)) {
    throw new InvalidArgumentException('Invalid API key format');
}
```

**Token Expiration**
```php
// Always check token expiration before making requests
if ($auth->isTokenExpired()) {
    echo "Token expired, need to refresh\n";
}
```

**Network Issues**
```php
$sambaSafety = new SambaSafety('api-key', 'base-url', [
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => true,  // Keep SSL verification enabled
]);
```

---

*Choose the authentication method that best fits your application's architecture and security requirements.*