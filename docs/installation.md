# Installation Guide

This guide will help you install and set up the SambaSafety PHP SDK in your project.

## Requirements

Before installing the SDK, ensure your environment meets these requirements:

- **PHP**: 8.0 or higher
- **Composer**: Latest stable version
- **Extensions**: `ext-json` (usually included with PHP)
- **Memory**: At least 128MB PHP memory limit (recommended: 256MB+)

## Installation

### Via Composer (Recommended)

The easiest way to install the SDK is using Composer:

```bash
composer require binjuhor/sambasafety-api
```

### Manual Installation

If you prefer to install manually:

1. Download the latest release from GitHub
2. Extract the files to your project directory
3. Include the autoloader:

```php
require_once 'path/to/sambasafety-sdk/vendor/autoload.php';
```

## Verification

After installation, verify the SDK is working correctly:

```php
<?php

require_once 'vendor/autoload.php';

use Binjuhor\SambasafetyApi\SambaSafety;

// Test basic instantiation
try {
    $sdk = new SambaSafety('test-api-key');
    echo "✅ SambaSafety SDK installed successfully!\n";
    echo "SDK Version: 1.0.0\n";
} catch (Exception $e) {
    echo "❌ Installation error: " . $e->getMessage() . "\n";
}
```

## Configuration

### Environment Variables

For security, store your API credentials in environment variables:

```bash
# .env file
SAMBASAFETY_API_KEY=your_api_key_here
SAMBASAFETY_BASE_URL=https://api.sambasafety.com/v1
```

### Configuration File

Create a configuration file for your application:

```php
<?php
// config/sambasafety.php

return [
    'api_key' => env('SAMBASAFETY_API_KEY'),
    'base_url' => env('SAMBASAFETY_BASE_URL', 'https://api.sambasafety.com/v1'),
    'timeout' => 30,
    'verify_ssl' => true,
    'options' => [
        'connect_timeout' => 10,
        'read_timeout' => 30,
    ]
];
```

## Framework Integration

### Laravel

Add to your `config/services.php`:

```php
'sambasafety' => [
    'api_key' => env('SAMBASAFETY_API_KEY'),
    'base_url' => env('SAMBASAFETY_BASE_URL', 'https://api.sambasafety.com/v1'),
],
```

Create a service provider:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Binjuhor\SambasafetyApi\SambaSafety;

class SambaSafetyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SambaSafety::class, function ($app) {
            $config = $app['config']['services.sambasafety'];

            return new SambaSafety(
                $config['api_key'],
                $config['base_url'] ?? 'https://api.sambasafety.com/v1'
            );
        });
    }
}
```

### Symfony

Register as a service in `services.yaml`:

```yaml
services:
    Binjuhor\SambasafetyApi\SambaSafety:
        arguments:
            $apiKey: '%env(SAMBASAFETY_API_KEY)%'
            $baseUrl: '%env(SAMBASAFETY_BASE_URL)%'
        public: true
```

## Development Setup

For development and testing:

1. Clone the repository:
```bash
git clone https://github.com/binjuhor/sambasafety-php-sdk.git
cd sambasafety-php-sdk
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment template:
```bash
cp .env.example .env
```

4. Run tests:
```bash
composer test
```

5. Check code quality:
```bash
composer phpstan
composer cs-check
```

## Troubleshooting

### Common Issues

**"Class not found" errors**
```bash
composer dump-autoload
```

**SSL/TLS issues**
```php
$sambaSafety = new SambaSafety('api-key', 'base-url', [
    'verify' => false  // Only for development
]);
```

**Timeout issues**
```php
$sambaSafety = new SambaSafety('api-key', 'base-url', [
    'timeout' => 60,
    'connect_timeout' => 15
]);
```

**Memory issues**
```php
ini_set('memory_limit', '256M');
```

### Getting Help

If you encounter issues:

1. Check the [troubleshooting guide](troubleshooting.md)
2. Review [GitHub Issues](https://github.com/binjuhor/sambasafety-php-sdk/issues)
3. Contact support: kiemhd@outlook.com

## Next Steps

After installation:

1. Read the [Quick Start Guide](quickstart.md)
2. Review [Authentication Methods](authentication.md)
3. Explore the [API Reference](api/driver-service.md)
4. Check out [Code Examples](examples/basic-operations.md)

---

*Installation complete! Ready to start using the SambaSafety PHP SDK.*