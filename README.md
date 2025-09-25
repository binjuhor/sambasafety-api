# SambaSafety PHP SDK

A comprehensive PHP SDK for the SambaSafety Driver Risk Management API. This SDK provides an easy-to-use interface for managing drivers, accessing motor vehicle records (MVR), and integrating with SambaSafety's fleet safety platform.

## Features

- ✅ **Driver Management**: Create, retrieve, and list drivers
- ✅ **MVR Access**: Retrieve motor vehicle records for drivers
- ✅ **Type Safety**: Full PHP 8.0+ type declarations
- ✅ **Exception Handling**: Comprehensive error handling with custom exceptions
- ✅ **PSR-4 Autoloading**: Follows PHP standards for autoloading
- ✅ **Guzzle HTTP**: Built on the reliable Guzzle HTTP client
- ✅ **Extensible**: Clean architecture for adding new services

## Requirements

- PHP 8.0 or higher
- `ext-json` extension
- Composer for dependency management

## Installation

```bash
composer require binjuhor/sambasafety-api
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Binjuhor\SambasafetyApi\SambaSafety;

// Initialize the SDK
$sambaSafety = new SambaSafety('your-api-key');

// Or use the static factory method
$sambaSafety = SambaSafety::create('your-api-key');

// Get all drivers
$drivers = $sambaSafety->drivers()->list();

// Get a specific driver
$driver = $sambaSafety->drivers()->get('driver-id');

// Get driver's MVR
$mvr = $sambaSafety->drivers()->getMvr('driver-id');
```

## Configuration

### Basic Configuration

```php
use Binjuhor\SambasafetyApi\SambaSafety;

$sambaSafety = new SambaSafety(
    apiKey: 'your-api-key',
    baseUrl: 'https://api.sambasafety.com/v1', // Optional, this is the default
    options: [] // Optional Guzzle options
);
```

### Custom Configuration Options

You can pass additional Guzzle HTTP client options:

```php
$sambaSafety = new SambaSafety('your-api-key', 'https://api.sambasafety.com/v1', [
    'timeout' => 60,
    'connect_timeout' => 10,
    'verify' => true, // SSL verification
    'proxy' => 'http://proxy.example.com:8080',
]);
```

## API Reference

### Driver Service

The Driver Service provides methods to manage drivers and access their records.

#### List Drivers

```php
$drivers = $sambaSafety->drivers()->list($filters);
```

**Parameters:**
- `$filters` (array, optional): Query filters for the driver list

**Returns:** Array of `Driver` objects

**Example:**
```php
// Get all drivers
$allDrivers = $sambaSafety->drivers()->list();

// Get drivers with filters
$filteredDrivers = $sambaSafety->drivers()->list([
    'license_state' => 'CA',
    'status' => 'active'
]);
```

#### Get Driver

```php
$driver = $sambaSafety->drivers()->get($driverId);
```

**Parameters:**
- `$driverId` (string): The unique identifier for the driver

**Returns:** `Driver` object

**Example:**
```php
$driver = $sambaSafety->drivers()->get('driver-123');
echo $driver->getFullName(); // "John Doe"
```

#### Create Driver

```php
$driver = $sambaSafety->drivers()->create($driverData);
```

**Parameters:**
- `$driverData` (array): Driver information

**Returns:** `Driver` object

**Example:**
```php
$newDriver = $sambaSafety->drivers()->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'license_number' => 'D1234567',
    'email' => 'john.doe@example.com',
    'metadata' => [
        'employee_id' => 'EMP001',
        'department' => 'Logistics'
    ]
]);
```

#### Get Motor Vehicle Record (MVR)

```php
$mvr = $sambaSafety->drivers()->getMvr($driverId);
```

**Parameters:**
- `$driverId` (string): The unique identifier for the driver

**Returns:** Array containing MVR data

**Example:**
```php
$mvr = $sambaSafety->drivers()->getMvr('driver-123');
// MVR contains driving history, violations, etc.
```

## Models

### Driver Model

The `Driver` model represents a driver in the SambaSafety system.

**Properties:**
- `id` (string): Unique identifier
- `firstName` (string): Driver's first name
- `lastName` (string): Driver's last name
- `licenseNumber` (?string): Driver's license number (optional)
- `email` (?string): Driver's email address (optional)
- `metadata` (array): Additional metadata

**Methods:**
- `getFullName()`: Returns the driver's full name
- `toArray()`: Converts the driver object to an array

**Example:**
```php
$driver = $sambaSafety->drivers()->get('driver-123');

echo $driver->id;           // "driver-123"
echo $driver->firstName;    // "John"
echo $driver->lastName;     // "Doe"
echo $driver->getFullName(); // "John Doe"

// Convert to array
$driverArray = $driver->toArray();
```

## Error Handling

The SDK provides specific exception types for different error scenarios:

### Exception Types

- `SambaSafetyException`: Base exception class
- `AuthenticationException`: Authentication/authorization errors (401, 403)
- `ValidationException`: Validation errors (400, 422)

### Exception Handling Example

```php
use Binjuhor\SambasafetyApi\Exceptions\AuthenticationException;
use Binjuhor\SambasafetyApi\Exceptions\ValidationException;
use Binjuhor\SambasafetyApi\Exceptions\SambaSafetyException;

try {
    $driver = $sambaSafety->drivers()->get('invalid-driver-id');
} catch (AuthenticationException $e) {
    // Handle authentication errors
    echo "Authentication failed: " . $e->getMessage();
} catch (ValidationException $e) {
    // Handle validation errors
    echo "Validation error: " . $e->getMessage();
} catch (SambaSafetyException $e) {
    // Handle other API errors
    echo "API error: " . $e->getMessage();
}
```

## Development

### Running Tests

```bash
# Run PHPUnit tests
composer test

# Run with coverage
phpunit --coverage-text
```

### Code Quality

```bash
# Run PHPStan analysis
composer phpstan

# Check coding standards
composer cs-check

# Fix coding standards
composer cs-fix
```

### Available Scripts

The following Composer scripts are available:

- `composer test`: Run PHPUnit tests
- `composer phpstan`: Run PHPStan static analysis at level 8
- `composer cs-check`: Check code against PSR-12 standards
- `composer cs-fix`: Automatically fix code style issues

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests and ensure they pass (`composer test`)
5. Run code quality checks (`composer phpstan` and `composer cs-check`)
6. Commit your changes (`git commit -am 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Maintain 100% type coverage with PHPStan level 8
- Write comprehensive tests for new features
- Update documentation for any API changes

## Security

If you discover any security-related issues, please email kiemhd@outlook.com instead of using the issue tracker.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- **Documentation**: This README and inline code documentation
- **Issues**: [GitHub Issues](https://github.com/binjuhor/sambasafety-php-sdk/issues)
- **Email**: kiemhd@outlook.com

## Author

**Hoang Kiem**
- Email: kiemhd@outlook.com
- Website: [https://binjuhor.com](https://binjuhor.com)

---

*Built with ❤️ for the fleet safety community*