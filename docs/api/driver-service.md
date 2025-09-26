# Driver Service API Reference

The Driver Service provides comprehensive driver management capabilities including CRUD operations, MVR handling, and status management.

## Class: DriverService

**Namespace:** `Binjuhor\SambasafetyApi\Services\DriverService`

### Constructor

```php
public function __construct(SambaSafetyClient $client)
```

### Methods Overview

| Method | Description | Return Type |
|--------|-------------|-------------|
| `query()` | Get query builder instance | `DriverQuery` |
| `list($filters)` | List drivers with optional filters | `DriverCollection` |
| `get($driverId)` | Get a specific driver | `Driver` |
| `create($driverData)` | Create a new driver | `Driver` |
| `update($driverId, $driverData)` | Update driver information | `Driver` |
| `delete($driverId)` | Delete a driver | `bool` |
| `getMvr($driverId)` | Get driver's MVR | `MvrRecord` |
| `requestMvr($driverId, $options)` | Request new MVR | `MvrRecord` |
| `getMvrHistory($driverId, $filters)` | Get MVR history | `MvrCollection` |
| `activate($driverId)` | Activate driver | `Driver` |
| `deactivate($driverId)` | Deactivate driver | `Driver` |
| `suspend($driverId, $reason)` | Suspend driver | `Driver` |

---

## Query Builder

### query()

Returns a query builder instance for advanced filtering and searching.

```php
public function query(): DriverQuery
```

**Example:**
```php
$drivers = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->whereState('CA')
    ->sortByName()
    ->perPage(50)
    ->get();
```

**See:** [Query Builder Documentation](../guides/query-builder.md)

---

## Driver Retrieval

### list()

Retrieve a list of drivers with optional filtering.

```php
public function list(array $filters = []): DriverCollection
```

**Parameters:**
- `$filters` (array, optional): Query filters

**Returns:** `DriverCollection` - Collection of Driver objects

**Example:**
```php
// Get all drivers
$allDrivers = $sambaSafety->drivers()->list();

// Get drivers with filters
$filteredDrivers = $sambaSafety->drivers()->list([
    'status' => 'active',
    'state' => 'CA',
    'per_page' => 25,
    'page' => 1
]);

echo "Found {$filteredDrivers->count()} drivers";
```

**Available Filters:**
- `status`: Driver status (active, inactive, suspended)
- `state`: Driver's state/province
- `email`: Exact email match
- `license_number`: License number search
- `created_after`: Drivers created after date (ISO 8601)
- `created_before`: Drivers created before date (ISO 8601)
- `per_page`: Results per page (pagination)
- `page`: Page number (pagination)
- `sort`: Sort field and direction (`name`, `-name`, `created_at`, `-created_at`)

### get()

Retrieve a specific driver by ID.

```php
public function get(string $driverId): Driver
```

**Parameters:**
- `$driverId` (string): Unique driver identifier

**Returns:** `Driver` - Driver object

**Throws:** `SambaSafetyException` if driver not found

**Example:**
```php
try {
    $driver = $sambaSafety->drivers()->get('driver-123');
    echo "Driver: {$driver->getFullName()}";
    echo "Email: {$driver->email}";
    echo "License: {$driver->licenseNumber}";
} catch (SambaSafetyException $e) {
    echo "Driver not found: {$e->getMessage()}";
}
```

---

## Driver Management

### create()

Create a new driver record.

```php
public function create(array $driverData): Driver
```

**Parameters:**
- `$driverData` (array): Driver information

**Returns:** `Driver` - Newly created driver object

**Throws:** `ValidationException` if data is invalid

**Required Fields:**
- `first_name`: Driver's first name
- `last_name`: Driver's last name

**Optional Fields:**
- `email`: Email address (validated)
- `license_number`: Driver's license number
- `date_of_birth`: Date of birth (ISO 8601 format)
- `phone`: Phone number
- `address`: Physical address object
- `metadata`: Additional custom data

**Example:**
```php
try {
    $driver = $sambaSafety->drivers()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'license_number' => 'D1234567',
        'date_of_birth' => '1985-06-15',
        'phone' => '+1-555-123-4567',
        'address' => [
            'street' => '123 Main St',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip' => '90210'
        ],
        'metadata' => [
            'employee_id' => 'EMP001',
            'department' => 'Logistics',
            'hire_date' => '2024-01-15'
        ]
    ]);

    echo "Created driver: {$driver->id}";
} catch (ValidationException $e) {
    echo "Validation error: {$e->getMessage()}";
}
```

### update()

Update existing driver information.

```php
public function update(string $driverId, array $driverData): Driver
```

**Parameters:**
- `$driverId` (string): Driver ID to update
- `$driverData` (array): Updated driver information

**Returns:** `Driver` - Updated driver object

**Throws:** `ValidationException` if data is invalid

**Example:**
```php
$updatedDriver = $sambaSafety->drivers()->update('driver-123', [
    'email' => 'newemail@example.com',
    'phone' => '+1-555-987-6543',
    'metadata' => [
        'department' => 'Operations',
        'updated_at' => date('Y-m-d H:i:s')
    ]
]);

echo "Updated driver: {$updatedDriver->getFullName()}";
```

### delete()

Delete a driver record.

```php
public function delete(string $driverId): bool
```

**Parameters:**
- `$driverId` (string): Driver ID to delete

**Returns:** `bool` - True if successful

**Example:**
```php
try {
    $success = $sambaSafety->drivers()->delete('driver-123');

    if ($success) {
        echo "Driver deleted successfully";
    }
} catch (SambaSafetyException $e) {
    echo "Delete failed: {$e->getMessage()}";
}
```

---

## MVR Operations

### getMvr()

Retrieve the current MVR record for a driver.

```php
public function getMvr(string $driverId): MvrRecord
```

**Parameters:**
- `$driverId` (string): Driver ID

**Returns:** `MvrRecord` - Most recent MVR record

**Example:**
```php
$mvr = $sambaSafety->drivers()->getMvr('driver-123');

echo "MVR Status: {$mvr->status}";
echo "Violations: {$mvr->getViolationCount()}";
echo "Accidents: {$mvr->getAccidentCount()}";

if ($mvr->isCompleted()) {
    foreach ($mvr->violations as $violation) {
        echo "- {$violation->description} ({$violation->severity})";
    }
}
```

### requestMvr()

Request a new MVR report for a driver.

```php
public function requestMvr(string $driverId, array $options = []): MvrRecord
```

**Parameters:**
- `$driverId` (string): Driver ID
- `$options` (array, optional): Request options

**Returns:** `MvrRecord` - New MVR request record

**Options:**
- `state`: Specific state to search
- `priority`: Request priority (standard, expedited)
- `callback_url`: Webhook URL for completion notification

**Example:**
```php
$mvrRequest = $sambaSafety->drivers()->requestMvr('driver-123', [
    'state' => 'CA',
    'priority' => 'expedited',
    'callback_url' => 'https://myapp.com/webhooks/mvr-complete'
]);

echo "MVR requested: {$mvrRequest->id}";
echo "Status: {$mvrRequest->status}";
```

### getMvrHistory()

Retrieve historical MVR records for a driver.

```php
public function getMvrHistory(string $driverId, array $filters = []): MvrCollection
```

**Parameters:**
- `$driverId` (string): Driver ID
- `$filters` (array, optional): Filter options

**Returns:** `MvrCollection` - Collection of historical MVR records

**Filters:**
- `status`: Filter by MVR status
- `state`: Filter by state
- `from_date`: Records from date
- `to_date`: Records to date
- `limit`: Maximum records to return

**Example:**
```php
$mvrHistory = $sambaSafety->drivers()->getMvrHistory('driver-123', [
    'status' => 'completed',
    'from_date' => '2024-01-01',
    'limit' => 10
]);

echo "Found {$mvrHistory->count()} historical MVR records";

// Get records with violations
$withViolations = $mvrHistory->withViolations();
echo "Records with violations: {$withViolations->count()}";
```

---

## Status Management

### activate()

Activate a driver record.

```php
public function activate(string $driverId): Driver
```

**Parameters:**
- `$driverId` (string): Driver ID

**Returns:** `Driver` - Updated driver object

**Example:**
```php
$driver = $sambaSafety->drivers()->activate('driver-123');
echo "Driver {$driver->getFullName()} activated";
```

### deactivate()

Deactivate a driver record.

```php
public function deactivate(string $driverId): Driver
```

**Parameters:**
- `$driverId` (string): Driver ID

**Returns:** `Driver` - Updated driver object

**Example:**
```php
$driver = $sambaSafety->drivers()->deactivate('driver-123');
echo "Driver {$driver->getFullName()} deactivated";
```

### suspend()

Suspend a driver with an optional reason.

```php
public function suspend(string $driverId, string $reason = ''): Driver
```

**Parameters:**
- `$driverId` (string): Driver ID
- `$reason` (string, optional): Suspension reason

**Returns:** `Driver` - Updated driver object

**Example:**
```php
$driver = $sambaSafety->drivers()->suspend('driver-123', 'DUI violation');
echo "Driver {$driver->getFullName()} suspended";

// Check suspension status
if (($driver->metadata['status'] ?? '') === 'suspended') {
    echo "Reason: {$driver->metadata['reason']}";
}
```

---

## Usage Examples

### Complete Driver Lifecycle

```php
// Create driver
$driver = $sambaSafety->drivers()->create([
    'first_name' => 'Alice',
    'last_name' => 'Johnson',
    'email' => 'alice.johnson@company.com',
    'license_number' => 'A9876543'
]);

// Request initial MVR
$mvr = $sambaSafety->drivers()->requestMvr($driver->id);

// Wait for MVR completion (in real app, use webhooks)
sleep(30);

// Check MVR results
$completedMvr = $sambaSafety->drivers()->getMvr($driver->id);
if ($completedMvr->hasViolations()) {
    echo "⚠️ Driver has {$completedMvr->getViolationCount()} violations";

    // Suspend if serious violations
    foreach ($completedMvr->violations as $violation) {
        if ($violation->isMajor()) {
            $sambaSafety->drivers()->suspend($driver->id, "Major violation: {$violation->description}");
            break;
        }
    }
} else {
    // Activate driver
    $sambaSafety->drivers()->activate($driver->id);
    echo "✅ Driver activated successfully";
}
```

### Bulk Driver Processing

```php
// Get all drivers needing MVR updates
$drivers = $sambaSafety->drivers()
    ->query()
    ->whereActive()
    ->where('mvr_last_updated', '<', '2024-01-01')
    ->get();

foreach ($drivers as $driver) {
    try {
        // Request new MVR
        $mvr = $sambaSafety->drivers()->requestMvr($driver->id);
        echo "Requested MVR for {$driver->getFullName()}: {$mvr->id}\n";

        // Update driver metadata
        $sambaSafety->drivers()->update($driver->id, [
            'metadata' => array_merge($driver->metadata, [
                'mvr_last_requested' => date('Y-m-d H:i:s')
            ])
        ]);

    } catch (Exception $e) {
        echo "Failed to request MVR for {$driver->getFullName()}: {$e->getMessage()}\n";
    }
}
```

---

**Next:** [Fleet Service API](fleet-service.md) | [MVR Service API](mvr-service.md)