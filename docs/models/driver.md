# Driver Model

The Driver model represents a driver in the SambaSafety system with all their personal information, license details, and metadata.

## Class: Driver

**Namespace:** `Binjuhor\SambasafetyApi\Models\Driver`

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | `string` | Unique driver identifier |
| `firstName` | `string` | Driver's first name |
| `lastName` | `string` | Driver's last name |
| `licenseNumber` | `?string` | Driver's license number (optional) |
| `email` | `?string` | Driver's email address (optional) |
| `metadata` | `array` | Additional custom data |

## Constructor

```php
public function __construct(array $data = [])
```

The constructor accepts an array of driver data and maps it to the object properties. It handles both snake_case and camelCase field names for flexibility.

**Example:**
```php
$driver = new Driver([
    'id' => 'driver-123',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'license_number' => 'D1234567',
    'email' => 'john.doe@example.com',
    'metadata' => [
        'employee_id' => 'EMP001',
        'department' => 'Logistics',
        'hire_date' => '2024-01-15'
    ]
]);

// Or using camelCase
$driver = new Driver([
    'id' => 'driver-123',
    'firstName' => 'John',
    'lastName' => 'Doe',
    'licenseNumber' => 'D1234567',
    'email' => 'john.doe@example.com',
    'metadata' => [...]
]);
```

## Methods

### getFullName()

Returns the driver's full name by combining first and last names.

```php
public function getFullName(): string
```

**Returns:** `string` - Full name with trimmed whitespace

**Example:**
```php
$driver = new Driver([
    'first_name' => 'John',
    'last_name' => 'Doe'
]);

echo $driver->getFullName(); // "John Doe"
```

### toArray()

Converts the driver object to an associative array.

```php
public function toArray(): array
```

**Returns:** `array` - Driver data as array with snake_case keys

**Example:**
```php
$driver = new Driver([
    'id' => 'driver-123',
    'firstName' => 'John',
    'lastName' => 'Doe',
    'email' => 'john.doe@example.com'
]);

$array = $driver->toArray();
/*
[
    'id' => 'driver-123',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'license_number' => null,
    'email' => 'john.doe@example.com',
    'metadata' => []
]
*/
```

## Usage Examples

### Basic Driver Creation

```php
use Binjuhor\SambasafetyApi\Models\Driver;

// Create a minimal driver
$driver = new Driver([
    'id' => 'driver-456',
    'first_name' => 'Jane',
    'last_name' => 'Smith'
]);

echo "Driver: " . $driver->getFullName(); // "Driver: Jane Smith"
echo "ID: " . $driver->id; // "ID: driver-456"
echo "Email: " . ($driver->email ?? 'Not provided'); // "Email: Not provided"
```

### Driver with Full Information

```php
$fullDriver = new Driver([
    'id' => 'driver-789',
    'first_name' => 'Robert',
    'last_name' => 'Johnson',
    'license_number' => 'R9876543',
    'email' => 'robert.johnson@company.com',
    'metadata' => [
        'employee_id' => 'EMP789',
        'department' => 'Operations',
        'hire_date' => '2023-06-10',
        'status' => 'active',
        'phone' => '+1-555-987-6543',
        'address' => [
            'street' => '456 Oak Avenue',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zip' => '94102'
        ],
        'emergency_contact' => [
            'name' => 'Mary Johnson',
            'relationship' => 'spouse',
            'phone' => '+1-555-123-7890'
        ]
    ]
]);

echo "Full Name: " . $fullDriver->getFullName() . "\n";
echo "License: " . $fullDriver->licenseNumber . "\n";
echo "Employee ID: " . $fullDriver->metadata['employee_id'] . "\n";
echo "Department: " . $fullDriver->metadata['department'] . "\n";
```

### Working with Metadata

The `metadata` property is a flexible array that can store any additional driver information.

```php
$driver = new Driver([
    'id' => 'driver-999',
    'first_name' => 'Sarah',
    'last_name' => 'Wilson',
    'metadata' => [
        'status' => 'active',
        'hire_date' => '2024-01-15',
        'certifications' => [
            'defensive_driving' => '2024-02-01',
            'hazmat' => '2023-11-15'
        ],
        'vehicle_assignments' => ['TRUCK001', 'TRUCK042'],
        'safety_score' => 92.5,
        'last_mvr_date' => '2024-03-01'
    ]
]);

// Access metadata
echo "Status: " . $driver->metadata['status'] . "\n";
echo "Safety Score: " . $driver->metadata['safety_score'] . "\n";

// Check certifications
if (isset($driver->metadata['certifications']['hazmat'])) {
    echo "HAZMAT certified since: " . $driver->metadata['certifications']['hazmat'] . "\n";
}

// List assigned vehicles
echo "Assigned vehicles: " . implode(', ', $driver->metadata['vehicle_assignments']) . "\n";
```

### Driver Status Checking

```php
function getDriverStatus(Driver $driver): string
{
    $status = $driver->metadata['status'] ?? 'unknown';

    return match($status) {
        'active' => '✅ Active',
        'inactive' => '⏸️ Inactive',
        'suspended' => '🚫 Suspended',
        'terminated' => '❌ Terminated',
        default => '❓ Unknown Status'
    };
}

$driver = new Driver([
    'id' => 'driver-101',
    'first_name' => 'Mike',
    'last_name' => 'Davis',
    'metadata' => ['status' => 'active']
]);

echo getDriverStatus($driver); // "✅ Active"
```

### Driver Validation

```php
function validateDriver(Driver $driver): array
{
    $errors = [];

    // Check required fields
    if (empty($driver->firstName)) {
        $errors[] = 'First name is required';
    }

    if (empty($driver->lastName)) {
        $errors[] = 'Last name is required';
    }

    // Validate email if provided
    if ($driver->email && !filter_var($driver->email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    // Check license number format if provided
    if ($driver->licenseNumber && strlen($driver->licenseNumber) < 5) {
        $errors[] = 'License number too short';
    }

    return $errors;
}

$driver = new Driver([
    'first_name' => 'John',
    'last_name' => '',
    'email' => 'invalid-email'
]);

$errors = validateDriver($driver);
if (!empty($errors)) {
    echo "Validation errors:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
}
```

### Driver Comparison

```php
function driversEqual(Driver $driver1, Driver $driver2): bool
{
    return $driver1->id === $driver2->id;
}

function compareDrivers(Driver $driver1, Driver $driver2): array
{
    $differences = [];

    if ($driver1->firstName !== $driver2->firstName) {
        $differences['firstName'] = [$driver1->firstName, $driver2->firstName];
    }

    if ($driver1->lastName !== $driver2->lastName) {
        $differences['lastName'] = [$driver1->lastName, $driver2->lastName];
    }

    if ($driver1->email !== $driver2->email) {
        $differences['email'] = [$driver1->email, $driver2->email];
    }

    if ($driver1->licenseNumber !== $driver2->licenseNumber) {
        $differences['licenseNumber'] = [$driver1->licenseNumber, $driver2->licenseNumber];
    }

    return $differences;
}
```

### Data Export/Import

```php
// Export driver data
function exportDriverData(Driver $driver): string
{
    $data = $driver->toArray();
    return json_encode($data, JSON_PRETTY_PRINT);
}

// Import driver data
function importDriverData(string $json): Driver
{
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid JSON data');
    }

    return new Driver($data);
}

// Usage
$original = new Driver([
    'id' => 'driver-export',
    'first_name' => 'Test',
    'last_name' => 'Driver',
    'email' => 'test@example.com'
]);

// Export
$json = exportDriverData($original);
echo $json . "\n";

// Import
$imported = importDriverData($json);
echo "Imported: " . $imported->getFullName() . "\n";
```

### Driver Builder Pattern

```php
class DriverBuilder
{
    private array $data = [];

    public function id(string $id): self
    {
        $this->data['id'] = $id;
        return $this;
    }

    public function name(string $firstName, string $lastName): self
    {
        $this->data['first_name'] = $firstName;
        $this->data['last_name'] = $lastName;
        return $this;
    }

    public function email(string $email): self
    {
        $this->data['email'] = $email;
        return $this;
    }

    public function license(string $licenseNumber): self
    {
        $this->data['license_number'] = $licenseNumber;
        return $this;
    }

    public function metadata(array $metadata): self
    {
        $this->data['metadata'] = $metadata;
        return $this;
    }

    public function build(): Driver
    {
        return new Driver($this->data);
    }
}

// Usage
$driver = (new DriverBuilder())
    ->id('driver-builder-001')
    ->name('Alice', 'Cooper')
    ->email('alice.cooper@company.com')
    ->license('AC123456')
    ->metadata([
        'department' => 'Delivery',
        'hire_date' => '2024-01-01'
    ])
    ->build();

echo "Built driver: " . $driver->getFullName() . "\n";
```

## Integration with Services

The Driver model is typically created automatically by service methods:

```php
// From DriverService
$drivers = $sambaSafety->drivers()->list();
foreach ($drivers as $driver) {
    // $driver is a Driver instance
    echo $driver->getFullName() . "\n";
}

// Single driver retrieval
$driver = $sambaSafety->drivers()->get('driver-123');
echo "Email: " . ($driver->email ?? 'No email') . "\n";

// Driver creation
$newDriver = $sambaSafety->drivers()->create([
    'first_name' => 'New',
    'last_name' => 'Driver',
    'email' => 'new.driver@example.com'
]);
// Returns Driver instance
```

## Best Practices

### 1. Always Check for Null Values

```php
// ❌ Don't assume fields exist
echo $driver->email; // Could be null

// ✅ Check for null values
echo $driver->email ?? 'No email provided';
```

### 2. Use Metadata for Custom Fields

```php
// ✅ Store custom data in metadata
$driver = new Driver([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'metadata' => [
        'employee_id' => 'EMP001',
        'custom_field' => 'custom_value'
    ]
]);
```

### 3. Validate Before Use

```php
function requireValidDriver(Driver $driver): void
{
    if (empty($driver->firstName) || empty($driver->lastName)) {
        throw new InvalidArgumentException('Driver must have first and last name');
    }
}
```

---

**Related:** [Driver Service API](../api/driver-service.md) | [Driver Collection](../guides/collections.md)