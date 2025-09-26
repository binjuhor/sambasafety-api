# Query Builder Guide

The SambaSafety PHP SDK includes a powerful query builder that allows you to construct complex queries with a fluent, readable syntax. This guide covers all query builder features and patterns.

## Overview

The query builder provides a fluent interface for building database queries without writing raw SQL or dealing with complex array structures. It supports filtering, sorting, pagination, and more.

## Basic Usage

### Getting Started

```php
use Binjuhor\SambasafetyApi\SambaSafety;

$sambaSafety = new SambaSafety('your-api-key');

// Get query builder instance
$query = $sambaSafety->drivers()->query();

// Build and execute query
$drivers = $query
    ->whereActive()
    ->sortByName()
    ->perPage(25)
    ->get();
```

## Available Query Builders

### Driver Query Builder

The `DriverQuery` class provides methods for filtering and retrieving drivers:

```php
$driverQuery = $sambaSafety->drivers()->query();
```

**Available Methods:**
- `where($field, $value)`
- `whereIn($field, $values)`
- `whereLike($field, $value)`
- `whereStatus($status)`
- `whereActive()`
- `whereInactive()`
- `whereState($state)`
- `whereEmail($email)`
- `whereLicenseNumber($licenseNumber)`
- `whereCreatedAfter($date)`
- `whereCreatedBefore($date)`
- `sortBy($field, $direction)`
- `sortByName($direction)`
- `sortByCreatedAt($direction)`
- `page($page)`
- `perPage($perPage)`
- `include(...$relations)`
- `includeMvr()`
- `includeFleet()`
- `get()`
- `first()`
- `count()`

---

## Filtering Methods

### Basic Where Clauses

```php
// Basic where clause
$drivers = $sambaSafety->drivers()
    ->query()
    ->where('department', 'Logistics')
    ->get();

// Multiple where clauses (AND logic)
$drivers = $sambaSafety->drivers()
    ->query()
    ->where('status', 'active')
    ->where('department', 'Operations')
    ->get();
```

### Where In Clauses

Filter by multiple values:

```php
// Multiple states
$drivers = $sambaSafety->drivers()
    ->query()
    ->whereIn('state', ['CA', 'NY', 'TX'])
    ->get();

// Multiple departments
$drivers = $sambaSafety->drivers()
    ->query()
    ->whereIn('department', ['Logistics', 'Delivery', 'Operations'])
    ->get();
```

### Like Queries

Pattern matching with wildcards:

```php
// Names starting with 'John'
$drivers = $sambaSafety->drivers()
    ->query()
    ->whereLike('first_name', 'John%')
    ->get();

// Email addresses containing 'gmail'
$drivers = $sambaSafety->drivers()
    ->query()
    ->whereLike('email', '%gmail%')
    ->get();

// License numbers ending with specific pattern
$drivers = $sambaSafety->drivers()
    ->query()
    ->whereLike('license_number', '%123')
    ->get();
```

---

## Predefined Filters

### Status Filters

```php
// Active drivers only
$activeDrivers = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->get();

// Inactive drivers only
$inactiveDrivers = $sambaSafety->drivers()
    ->query()
    ->whereInactive()
    ->get();

// Specific status
$suspendedDrivers = $sambaSafety->drivers()
    ->query()
    ->whereStatus('suspended')
    ->get();
```

### Location Filters

```php
// Drivers in specific state
$californiaDrivers = $sambaSafety->drivers()
    ->query()
    ->whereState('CA')
    ->get();
```

### Contact Filters

```php
// Find driver by email
$driver = $sambaSafety->drivers()
    ->query()
    ->whereEmail('john.doe@company.com')
    ->first();

// Find driver by license number
$driver = $sambaSafety->drivers()
    ->query()
    ->whereLicenseNumber('D1234567')
    ->first();
```

### Date Filters

```php
// Drivers created after specific date
$recentDrivers = $sambaSafety->drivers()
    ->query()
    ->whereCreatedAfter('2024-01-01')
    ->get();

// Drivers created before specific date
$olderDrivers = $sambaSafety->drivers()
    ->query()
    ->whereCreatedBefore('2023-12-31')
    ->get();

// Date range
$rangeDrivers = $sambaSafety->drivers()
    ->query()
    ->whereCreatedAfter('2024-01-01')
    ->whereCreatedBefore('2024-06-30')
    ->get();
```

---

## Sorting

### Basic Sorting

```php
// Sort by name (ascending)
$drivers = $sambaSafety->drivers()
    ->query()
    ->sortByName()
    ->get();

// Sort by name (descending)
$drivers = $sambaSafety->drivers()
    ->query()
    ->sortByName('desc')
    ->get();

// Sort by creation date (newest first)
$drivers = $sambaSafety->drivers()
    ->query()
    ->sortByCreatedAt('desc')
    ->get();
```

### Custom Field Sorting

```php
// Sort by custom field
$drivers = $sambaSafety->drivers()
    ->query()
    ->sortBy('department', 'asc')
    ->get();

// Multiple sorting criteria
$drivers = $sambaSafety->drivers()
    ->query()
    ->sortBy('department', 'asc')
    ->sortBy('last_name', 'asc')
    ->get();
```

---

## Pagination

### Basic Pagination

```php
// Get page 1 with 25 items per page
$firstPage = $sambaSafety->drivers()
    ->query()
    ->perPage(25)
    ->page(1)
    ->get();

// Get page 2
$secondPage = $sambaSafety->drivers()
    ->query()
    ->perPage(25)
    ->page(2)
    ->get();

// Check pagination metadata
$meta = $firstPage->getMeta();
echo "Total drivers: " . $meta['total'] . "\n";
echo "Current page: " . $meta['current_page'] . "\n";
echo "Per page: " . $meta['per_page'] . "\n";
```

### Pagination Helpers

```php
$drivers = $sambaSafety->drivers()
    ->query()
    ->perPage(20)
    ->page(1)
    ->get();

// Check for next/previous pages
if ($drivers->hasNextPage()) {
    echo "More results available\n";
}

if ($drivers->hasPreviousPage()) {
    echo "Previous page exists\n";
}

// Get total count
$totalDrivers = $drivers->getTotal();
echo "Total drivers matching query: {$totalDrivers}\n";
```

### Pagination Loop

```php
$page = 1;
$allDrivers = [];

do {
    $pageResults = $sambaSafety->drivers()
        ->query()
        ->whereActive()
        ->perPage(50)
        ->page($page)
        ->get();

    $allDrivers = array_merge($allDrivers, $pageResults->toArray());
    $page++;

} while ($pageResults->hasNextPage());

echo "Retrieved " . count($allDrivers) . " total drivers\n";
```

---

## Including Related Data

### Basic Includes

```php
// Include MVR data with drivers
$driversWithMvr = $sambaSafety->drivers()
    ->query()
    ->includeMvr()
    ->get();

// Include fleet information
$driversWithFleet = $sambaSafety->drivers()
    ->query()
    ->includeFleet()
    ->get();

// Multiple includes
$driversWithAll = $sambaSafety->drivers()
    ->query()
    ->includeMvr()
    ->includeFleet()
    ->get();
```

### Custom Includes

```php
// Include custom relations
$drivers = $sambaSafety->drivers()
    ->query()
    ->include('mvr', 'fleet', 'violations')
    ->get();
```

---

## Result Methods

### Get Results

```php
// Get all matching results
$allDrivers = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->get();

// Get first matching result
$firstDriver = $sambaSafety->drivers()
    ->query()
    ->whereEmail('john.doe@company.com')
    ->first();

// Get count without retrieving data
$count = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->whereState('CA')
    ->count();

echo "Active drivers in CA: {$count}\n";
```

---

## Complex Query Examples

### Multi-Criteria Search

```php
// Complex driver search
$drivers = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->whereIn('state', ['CA', 'NY', 'TX'])
    ->whereCreatedAfter('2024-01-01')
    ->whereLike('email', '%@company.com')
    ->sortByName()
    ->perPage(50)
    ->get();

echo "Found " . $drivers->count() . " drivers matching complex criteria\n";
```

### Department-Based Filtering

```php
// Get drivers by department with sorting
$logisticsDrivers = $sambaSafety->drivers()
    ->query()
    ->where('department', 'Logistics')
    ->whereActive()
    ->sortBy('hire_date', 'desc')
    ->includeMvr()
    ->get();

foreach ($logisticsDrivers as $driver) {
    echo "Driver: {$driver->getFullName()}\n";
    echo "Hire date: " . ($driver->metadata['hire_date'] ?? 'Unknown') . "\n";
    echo "MVR status: " . ($driver->mvr->status ?? 'No MVR') . "\n\n";
}
```

### Geographic Analysis

```php
// Get drivers by geographic regions
$westCoastDrivers = $sambaSafety->drivers()
    ->query()
    ->whereIn('state', ['CA', 'OR', 'WA'])
    ->whereActive()
    ->sortByState()
    ->get();

$eastCoastDrivers = $sambaSafety->drivers()
    ->query()
    ->whereIn('state', ['NY', 'NJ', 'CT', 'MA'])
    ->whereActive()
    ->sortByState()
    ->get();

echo "West Coast: " . $westCoastDrivers->count() . " drivers\n";
echo "East Coast: " . $eastCoastDrivers->count() . " drivers\n";
```

### License Expiration Monitoring

```php
// Find drivers with licenses expiring soon
$expiringSoon = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->where('license_expires_at', '<=', date('Y-m-d', strtotime('+30 days')))
    ->sortBy('license_expires_at', 'asc')
    ->get();

foreach ($expiringSoon as $driver) {
    $expiresAt = $driver->metadata['license_expires_at'] ?? null;
    if ($expiresAt) {
        $daysLeft = (new DateTime($expiresAt))->diff(new DateTime())->days;
        echo "⚠️ {$driver->getFullName()}: License expires in {$daysLeft} days\n";
    }
}
```

---

## Performance Optimization

### Query Optimization

```php
// Use count() instead of get() when you only need the number
$activeCount = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->count(); // More efficient than ->get()->count()

// Use first() instead of get() when you need only one result
$driver = $sambaSafety->drivers()
    ->query()
    ->whereEmail('specific@email.com')
    ->first(); // More efficient than ->get()[0]
```

### Batch Processing

```php
// Process large datasets in batches
function processAllDrivers($sambaSafety, $callback)
{
    $page = 1;
    $batchSize = 100;

    do {
        $drivers = $sambaSafety->drivers()
            ->query()
            ->perPage($batchSize)
            ->page($page)
            ->get();

        foreach ($drivers as $driver) {
            $callback($driver);
        }

        $page++;
        echo "Processed page {$page}, {$drivers->count()} drivers\n";

    } while ($drivers->hasNextPage());
}

// Usage
processAllDrivers($sambaSafety, function($driver) {
    // Process individual driver
    echo "Processing: {$driver->getFullName()}\n";
});
```

### Selective Field Loading

```php
// Only include necessary related data
$driversForReport = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->include('mvr') // Only include MVR data, not fleet info
    ->sortByName()
    ->get();
```

---

## Query Builder Patterns

### Builder Pattern Chain

```php
class DriverQueryBuilder
{
    private $query;

    public function __construct($sambaSafety)
    {
        $this->query = $sambaSafety->drivers()->query();
    }

    public function activeOnly(): self
    {
        $this->query->whereActive();
        return $this;
    }

    public function inStates(array $states): self
    {
        $this->query->whereIn('state', $states);
        return $this;
    }

    public function withMvr(): self
    {
        $this->query->includeMvr();
        return $this;
    }

    public function sortedByName(): self
    {
        $this->query->sortByName();
        return $this;
    }

    public function paginated(int $perPage = 25): self
    {
        $this->query->perPage($perPage);
        return $this;
    }

    public function execute()
    {
        return $this->query->get();
    }
}

// Usage
$builder = new DriverQueryBuilder($sambaSafety);
$drivers = $builder
    ->activeOnly()
    ->inStates(['CA', 'NY'])
    ->withMvr()
    ->sortedByName()
    ->paginated(50)
    ->execute();
```

### Reusable Query Scopes

```php
class CommonDriverQueries
{
    private $sambaSafety;

    public function __construct($sambaSafety)
    {
        $this->sambaSafety = $sambaSafety;
    }

    public function activeDrivers()
    {
        return $this->sambaSafety->drivers()
            ->query()
            ->whereActive();
    }

    public function highRiskDrivers()
    {
        return $this->sambaSafety->drivers()
            ->query()
            ->whereActive()
            ->where('safety_score', '<', 70)
            ->includeMvr();
    }

    public function newHires($days = 30)
    {
        $since = date('Y-m-d', strtotime("-{$days} days"));
        return $this->sambaSafety->drivers()
            ->query()
            ->whereActive()
            ->whereCreatedAfter($since);
    }

    public function expiringLicenses($days = 30)
    {
        $until = date('Y-m-d', strtotime("+{$days} days"));
        return $this->sambaSafety->drivers()
            ->query()
            ->whereActive()
            ->where('license_expires_at', '<=', $until)
            ->sortBy('license_expires_at', 'asc');
    }
}

// Usage
$queries = new CommonDriverQueries($sambaSafety);

$highRisk = $queries->highRiskDrivers()->get();
$newHires = $queries->newHires(7)->get(); // Last 7 days
$expiring = $queries->expiringLicenses(14)->get(); // Next 14 days
```

## Error Handling

```php
use Binjuhor\SambasafetyApi\Exceptions\SambaSafetyException;

try {
    $drivers = $sambaSafety->drivers()
        ->query()
        ->whereActive()
        ->whereState('INVALID') // Invalid state code
        ->get();

} catch (SambaSafetyException $e) {
    echo "Query error: " . $e->getMessage() . "\n";

    // Handle specific error scenarios
    switch ($e->getCode()) {
        case 400:
            echo "Invalid query parameters\n";
            break;
        case 429:
            echo "Rate limit exceeded, please retry later\n";
            break;
        default:
            echo "Unexpected error occurred\n";
    }
}
```

The query builder provides a powerful, fluent interface for constructing complex queries while maintaining readability and type safety. Use these patterns to build efficient, maintainable applications with the SambaSafety PHP SDK.

---

**Related:** [Driver Service API](../api/driver-service.md) | [Collections Guide](collections.md)