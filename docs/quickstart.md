# Quick Start Guide

Get up and running with the SambaSafety PHP SDK in just a few minutes!

## Basic Setup

```php
<?php

require_once 'vendor/autoload.php';

use Binjuhor\SambasafetyApi\SambaSafety;

// Initialize the SDK
$sambaSafety = new SambaSafety('your-api-key-here');

// That's it! You're ready to use the SDK
```

## Your First API Call

Let's retrieve a list of drivers:

```php
// Get all drivers
$drivers = $sambaSafety->drivers()->list();

echo "Found " . $drivers->count() . " drivers\n";

foreach ($drivers as $driver) {
    echo "Driver: " . $driver->getFullName() . "\n";
    echo "Email: " . ($driver->email ?? 'No email') . "\n";
    echo "License: " . ($driver->licenseNumber ?? 'No license') . "\n\n";
}
```

## Common Operations

### 1. Driver Management

```php
// Create a new driver
$newDriver = $sambaSafety->drivers()->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com',
    'license_number' => 'D1234567',
    'metadata' => [
        'employee_id' => 'EMP001',
        'department' => 'Logistics'
    ]
]);

echo "Created driver: " . $newDriver->id . "\n";

// Get a specific driver
$driver = $sambaSafety->drivers()->get($newDriver->id);

// Update driver information
$updatedDriver = $sambaSafety->drivers()->update($driver->id, [
    'email' => 'john.doe.new@example.com'
]);

// Activate/deactivate driver
$sambaSafety->drivers()->activate($driver->id);
$sambaSafety->drivers()->deactivate($driver->id);
```

### 2. MVR (Motor Vehicle Record) Operations

```php
// Request an MVR for a driver
$mvr = $sambaSafety->drivers()->requestMvr($driver->id);

echo "MVR Status: " . $mvr->status . "\n";

// Get completed MVR
if ($mvr->isCompleted()) {
    echo "Violations: " . $mvr->getViolationCount() . "\n";
    echo "Accidents: " . $mvr->getAccidentCount() . "\n";

    foreach ($mvr->violations as $violation) {
        echo "- " . $violation->description . " (" . $violation->date?->format('Y-m-d') . ")\n";
    }
}

// Get MVR history
$mvrHistory = $sambaSafety->drivers()->getMvrHistory($driver->id);
echo "Total MVR records: " . $mvrHistory->count() . "\n";
```

### 3. License Discovery

```php
// Discover licenses by personal information
$licenses = $sambaSafety->licenseDiscovery()->discoverByDriverInfo(
    'John',
    'Doe',
    '1985-06-15',
    'CA'
);

foreach ($licenses as $license) {
    echo "Found license: " . $license->number . " in " . $license->state . "\n";

    if ($license->isExpired()) {
        echo "⚠️ This license is expired!\n";
    }

    if ($license->isSuspended()) {
        echo "🚫 This license is suspended!\n";
    }
}

// Validate a specific license
$validLicense = $sambaSafety->licenseDiscovery()->validateLicense('D1234567', 'CA');
if ($validLicense) {
    echo "License is valid: " . $validLicense->status . "\n";
}
```

## Using the Query Builder

The SDK includes a powerful query builder for advanced filtering:

```php
// Advanced driver query
$activeDrivers = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->whereState('CA')
    ->sortByName()
    ->perPage(25)
    ->get();

echo "Active CA drivers: " . $activeDrivers->count() . "\n";

// Filter by multiple criteria
$recentDrivers = $sambaSafety->drivers()
    ->query()
    ->whereCreatedAfter('2024-01-01')
    ->whereLike('first_name', 'John%')
    ->sortByCreatedAt('desc')
    ->get();

// Get just the first match
$firstDriver = $sambaSafety->drivers()
    ->query()
    ->whereEmail('john.doe@example.com')
    ->first();
```

## Working with Collections

Collections provide powerful data manipulation:

```php
$drivers = $sambaSafety->drivers()->list();

// Filter active drivers
$activeDrivers = $drivers->getActiveDrivers();

// Find by email
$driver = $drivers->findByEmail('john.doe@example.com');

// Sort by name
$sortedDrivers = $drivers->sortByName();

// Chain operations
$result = $drivers
    ->getActiveDrivers()
    ->sortByName()
    ->filter(fn($driver) => $driver->licenseNumber !== null);

echo "Active drivers with licenses: " . $result->count() . "\n";
```

## Error Handling

Always wrap your API calls in try-catch blocks:

```php
use Binjuhor\SambasafetyApi\Exceptions\AuthenticationException;
use Binjuhor\SambasafetyApi\Exceptions\ValidationException;
use Binjuhor\SambasafetyApi\Exceptions\SambaSafetyException;

try {
    $driver = $sambaSafety->drivers()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'invalid-email'  // This will cause validation error
    ]);

} catch (ValidationException $e) {
    echo "Validation error: " . $e->getMessage() . "\n";
} catch (AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage() . "\n";
} catch (SambaSafetyException $e) {
    echo "API error: " . $e->getMessage() . "\n";
}
```

## Configuration Options

Customize the SDK behavior:

```php
$sambaSafety = new SambaSafety(
    apiKey: 'your-api-key',
    baseUrl: 'https://api.sambasafety.com/v1',
    options: [
        'timeout' => 60,        // Request timeout
        'connect_timeout' => 10, // Connection timeout
        'verify' => true,        // SSL verification
        'proxy' => 'http://proxy.example.com:8080'
    ]
);
```

## Next Steps

Now that you're familiar with the basics:

1. **Explore Services**: Check out the [API Reference](api/driver-service.md) for detailed documentation
2. **Learn Models**: Understand the [data models](models/driver.md) and their properties
3. **Advanced Features**: Dive into [query building](guides/query-builder.md) and [collections](guides/collections.md)
4. **See Examples**: Browse complete [examples and tutorials](examples/basic-operations.md)

## Quick Reference Card

### Essential Methods

```php
// Driver operations
$sambaSafety->drivers()->list()
$sambaSafety->drivers()->get($id)
$sambaSafety->drivers()->create($data)
$sambaSafety->drivers()->update($id, $data)

// MVR operations
$sambaSafety->drivers()->requestMvr($driverId)
$sambaSafety->mvr()->list()

// License discovery
$sambaSafety->licenseDiscovery()->discoverByDriverInfo(...)
$sambaSafety->licenseDiscovery()->validateLicense(...)

// Fleet operations
$sambaSafety->fleets()->list()
$sambaSafety->fleets()->getDrivers($fleetId)
```

### Query Builder Shortcuts

```php
->whereActive()          // Active drivers only
->whereState('CA')       // Specific state
->sortByName()          // Sort alphabetically
->perPage(50)           // Pagination
->first()               // Get first result
->count()               // Count results
```

---

*You're now ready to build powerful fleet management applications with the SambaSafety PHP SDK!*