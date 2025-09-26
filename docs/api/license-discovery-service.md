# License Discovery Service API Reference

The License Discovery Service provides comprehensive license finding, validation, and management capabilities across all US states and Canadian provinces.

## Class: LicenseDiscoveryService

**Namespace:** `Binjuhor\SambasafetyApi\Services\LicenseDiscoveryService`

### Methods Overview

| Method | Description | Return Type |
|--------|-------------|-------------|
| `discoverByPersonalInfo($data)` | Discover licenses by personal info | `LicenseInfo[]` |
| `discoverByDriverInfo($firstName, $lastName, $dob, $state)` | Discover by driver details | `LicenseInfo[]` |
| `discoverBySsn($ssn, $state)` | Discover using SSN | `LicenseInfo[]` |
| `discoverMultipleStates($data, $states)` | Search specific states | `LicenseInfo[]` |
| `searchAllStates($data)` | Search all available states | `LicenseInfo[]` |
| `validateLicense($number, $state)` | Validate specific license | `LicenseInfo|null` |
| `getLicenseHistory($number, $state)` | Get license history | `LicenseInfo[]` |
| `findExpiredLicenses($filters)` | Find expired licenses | `LicenseInfo[]` |
| `findExpiringSoon($days)` | Find licenses expiring soon | `LicenseInfo[]` |
| `findSuspendedLicenses($filters)` | Find suspended licenses | `LicenseInfo[]` |
| `bulkValidation($licenses)` | Validate multiple licenses | `array` |
| `discoverForExistingDriver($driverId)` | Discover for existing driver | `LicenseInfo[]` |
| `linkLicenseToDriver($driverId, $number, $state)` | Link license to driver | `Driver` |
| `unlinkLicenseFromDriver($driverId, $number, $state)` | Unlink license from driver | `bool` |

---

## License Discovery Methods

### discoverByPersonalInfo()

Discover licenses using personal information.

```php
public function discoverByPersonalInfo(array $personalInfo): array
```

**Parameters:**
- `$personalInfo` (array): Personal information for search

**Returns:** `LicenseInfo[]` - Array of discovered licenses

**Personal Info Fields:**
- `first_name` (required): First name
- `last_name` (required): Last name
- `date_of_birth` (required): DOB in YYYY-MM-DD format
- `ssn` (optional): Social Security Number
- `middle_name` (optional): Middle name or initial
- `suffix` (optional): Name suffix (Jr, Sr, III, etc.)
- `state` (optional): Specific state to search

**Example:**
```php
$licenses = $sambaSafety->licenseDiscovery()->discoverByPersonalInfo([
    'first_name' => 'John',
    'last_name' => 'Smith',
    'date_of_birth' => '1985-03-15',
    'ssn' => '123-45-6789',
    'middle_name' => 'Michael',
    'state' => 'CA'
]);

foreach ($licenses as $license) {
    echo "Found: {$license->number} in {$license->state}\n";
    echo "Status: {$license->status}\n";
    echo "Expires: " . $license->expirationDate?->format('Y-m-d') . "\n\n";
}
```

### discoverByDriverInfo()

Simplified method to discover licenses by basic driver information.

```php
public function discoverByDriverInfo(
    string $firstName,
    string $lastName,
    string $dateOfBirth,
    ?string $state = null
): array
```

**Parameters:**
- `$firstName` (string): Driver's first name
- `$lastName` (string): Driver's last name
- `$dateOfBirth` (string): Date of birth (YYYY-MM-DD)
- `$state` (string|null): Optional state filter

**Returns:** `LicenseInfo[]` - Array of discovered licenses

**Example:**
```php
// Search specific state
$caLicenses = $sambaSafety->licenseDiscovery()->discoverByDriverInfo(
    'Jane',
    'Doe',
    '1990-07-22',
    'CA'
);

// Search all states (state parameter omitted)
$allLicenses = $sambaSafety->licenseDiscovery()->discoverByDriverInfo(
    'Jane',
    'Doe',
    '1990-07-22'
);

echo "Found {count($allLicenses)} licenses across all states";
```

### discoverBySsn()

Discover licenses using Social Security Number.

```php
public function discoverBySsn(string $ssn, ?string $state = null): array
```

**Parameters:**
- `$ssn` (string): Social Security Number (with or without dashes)
- `$state` (string|null): Optional state filter

**Returns:** `LicenseInfo[]` - Array of discovered licenses

**Example:**
```php
// SSN with dashes
$licenses = $sambaSafety->licenseDiscovery()->discoverBySsn('123-45-6789', 'NY');

// SSN without dashes
$licenses = $sambaSafety->licenseDiscovery()->discoverBySsn('123456789');

foreach ($licenses as $license) {
    if ($license->isActive()) {
        echo "Active license: {$license->number} ({$license->state})\n";
    }
}
```

---

## Multi-State Discovery

### discoverMultipleStates()

Search for licenses across multiple specific states.

```php
public function discoverMultipleStates(array $personalInfo, array $states): array
```

**Parameters:**
- `$personalInfo` (array): Personal information for search
- `$states` (array): Array of state codes to search

**Returns:** `LicenseInfo[]` - Array of discovered licenses

**Example:**
```php
$licenses = $sambaSafety->licenseDiscovery()->discoverMultipleStates(
    [
        'first_name' => 'Robert',
        'last_name' => 'Johnson',
        'date_of_birth' => '1982-11-30'
    ],
    ['CA', 'NY', 'TX', 'FL']  // Search these 4 states
);

// Group results by state
$byState = [];
foreach ($licenses as $license) {
    $byState[$license->state][] = $license;
}

foreach ($byState as $state => $stateLicenses) {
    echo "{$state}: " . count($stateLicenses) . " licenses found\n";
}
```

### searchAllStates()

Search for licenses across all available states and provinces.

```php
public function searchAllStates(array $personalInfo): array
```

**Parameters:**
- `$personalInfo` (array): Personal information for search

**Returns:** `LicenseInfo[]` - Array of discovered licenses

**Example:**
```php
$allLicenses = $sambaSafety->licenseDiscovery()->searchAllStates([
    'first_name' => 'Michael',
    'last_name' => 'Brown',
    'date_of_birth' => '1978-05-14',
    'ssn' => '987-65-4321'
]);

echo "Comprehensive search found " . count($allLicenses) . " licenses\n";

// Analyze results
$activeCount = 0;
$expiredCount = 0;
$suspendedCount = 0;

foreach ($allLicenses as $license) {
    if ($license->isActive()) {
        $activeCount++;
    } elseif ($license->isExpired()) {
        $expiredCount++;
    } elseif ($license->isSuspended()) {
        $suspendedCount++;
    }
}

echo "Active: {$activeCount}, Expired: {$expiredCount}, Suspended: {$suspendedCount}\n";
```

---

## License Validation

### validateLicense()

Validate a specific license number and state.

```php
public function validateLicense(string $licenseNumber, string $state): ?LicenseInfo
```

**Parameters:**
- `$licenseNumber` (string): License number to validate
- `$state` (string): State code

**Returns:** `LicenseInfo|null` - License info if valid, null if invalid

**Example:**
```php
$license = $sambaSafety->licenseDiscovery()->validateLicense('D1234567', 'CA');

if ($license !== null) {
    echo "✅ License is VALID\n";
    echo "Status: {$license->status}\n";
    echo "Class: {$license->class}\n";
    echo "Expires: " . $license->expirationDate?->format('M d, Y') . "\n";

    // Check for issues
    if ($license->isExpired()) {
        echo "⚠️ License is EXPIRED!\n";
    }

    if ($license->isSuspended()) {
        echo "🚫 License is SUSPENDED!\n";
    }

    if ($license->isExpiringSoon(30)) {
        echo "⏰ License expires within 30 days!\n";
    }

    // Check endorsements
    if ($license->hasEndorsement('M')) {
        echo "🏍️ Has motorcycle endorsement\n";
    }

    if ($license->isCommercial()) {
        echo "🚛 Commercial license\n";
    }
} else {
    echo "❌ License NOT FOUND or INVALID\n";
}
```

### bulkValidation()

Validate multiple licenses in a single request.

```php
public function bulkValidation(array $licenses): array
```

**Parameters:**
- `$licenses` (array): Array of license info arrays

**Returns:** `array` - Validation results

**License Info Format:**
```php
[
    'license_number' => 'string',
    'state' => 'string'
]
```

**Result Format:**
```php
[
    'license_number' => 'string',
    'state' => 'string',
    'valid' => bool,
    'license_info' => LicenseInfo|null,
    'errors' => string[]
]
```

**Example:**
```php
$results = $sambaSafety->licenseDiscovery()->bulkValidation([
    ['license_number' => 'D1234567', 'state' => 'CA'],
    ['license_number' => 'DL987654', 'state' => 'NY'],
    ['license_number' => 'T555666', 'state' => 'TX'],
    ['license_number' => 'INVALID', 'state' => 'FL']
]);

foreach ($results as $result) {
    echo "License {$result['license_number']} ({$result['state']}): ";

    if ($result['valid']) {
        echo "✅ VALID";
        if ($result['license_info']) {
            echo " - Status: {$result['license_info']->status}";
            echo " - Expires: " . $result['license_info']->expirationDate?->format('Y-m-d');
        }
    } else {
        echo "❌ INVALID";
        if (!empty($result['errors'])) {
            echo " - Errors: " . implode(', ', $result['errors']);
        }
    }
    echo "\n";
}
```

---

## License Monitoring

### findExpiredLicenses()

Find licenses that have expired.

```php
public function findExpiredLicenses(array $filters = []): array
```

**Parameters:**
- `$filters` (array): Optional filters

**Returns:** `LicenseInfo[]` - Array of expired licenses

**Available Filters:**
- `fleet_id`: Filter by fleet
- `state`: Filter by state
- `expired_after`: Expired after date
- `expired_before`: Expired before date

**Example:**
```php
$expiredLicenses = $sambaSafety->licenseDiscovery()->findExpiredLicenses([
    'fleet_id' => 'fleet-123',
    'expired_after' => '2024-01-01'
]);

echo "Found " . count($expiredLicenses) . " expired licenses\n";

foreach ($expiredLicenses as $license) {
    $daysSinceExpired = $license->expirationDate ?
        (new DateTime())->diff($license->expirationDate)->days : 'Unknown';

    echo "- {$license->number} ({$license->state}): ";
    echo "Expired {$daysSinceExpired} days ago\n";
}
```

### findExpiringSoon()

Find licenses that will expire within a specified number of days.

```php
public function findExpiringSoon(int $days = 30): array
```

**Parameters:**
- `$days` (int): Number of days to look ahead (default: 30)

**Returns:** `LicenseInfo[]` - Array of licenses expiring soon

**Example:**
```php
// Find licenses expiring in next 30 days
$expiringSoon = $sambaSafety->licenseDiscovery()->findExpiringSoon(30);

// Find licenses expiring in next 7 days (urgent)
$expiringUrgent = $sambaSafety->licenseDiscovery()->findExpiringSoon(7);

echo "Expiring in 30 days: " . count($expiringSoon) . "\n";
echo "Expiring in 7 days: " . count($expiringUrgent) . "\n";

foreach ($expiringUrgent as $license) {
    $daysUntilExpiry = $license->expirationDate ?
        (new DateTime())->diff($license->expirationDate)->days : 'Unknown';

    echo "🚨 URGENT: {$license->number} ({$license->state}) ";
    echo "expires in {$daysUntilExpiry} days!\n";
}
```

### findSuspendedLicenses()

Find licenses that are currently suspended.

```php
public function findSuspendedLicenses(array $filters = []): array
```

**Parameters:**
- `$filters` (array): Optional filters

**Returns:** `LicenseInfo[]` - Array of suspended licenses

**Example:**
```php
$suspendedLicenses = $sambaSafety->licenseDiscovery()->findSuspendedLicenses([
    'state' => 'CA'
]);

echo "Found " . count($suspendedLicenses) . " suspended licenses in CA\n";

foreach ($suspendedLicenses as $license) {
    echo "🚫 {$license->number}: {$license->status}\n";

    // Check for restrictions
    if (!empty($license->restrictions)) {
        echo "   Restrictions: " . implode(', ', $license->restrictions) . "\n";
    }
}
```

---

## Driver Integration

### discoverForExistingDriver()

Discover licenses for an existing driver in your system.

```php
public function discoverForExistingDriver(string $driverId): array
```

**Parameters:**
- `$driverId` (string): Existing driver ID

**Returns:** `LicenseInfo[]` - Array of discovered licenses

**Example:**
```php
$licenses = $sambaSafety->licenseDiscovery()->discoverForExistingDriver('driver-123');

echo "Found " . count($licenses) . " licenses for existing driver\n";

foreach ($licenses as $license) {
    echo "- {$license->number} ({$license->state}): {$license->status}\n";

    // Check if this is a new license not in our records
    $existingDriver = $sambaSafety->drivers()->get('driver-123');
    if ($existingDriver->licenseNumber !== $license->number) {
        echo "  🆕 NEW license discovered!\n";
    }
}
```

### linkLicenseToDriver()

Link a discovered license to a driver record.

```php
public function linkLicenseToDriver(
    string $driverId,
    string $licenseNumber,
    string $state
): Driver
```

**Parameters:**
- `$driverId` (string): Driver ID
- `$licenseNumber` (string): License number to link
- `$state` (string): License state

**Returns:** `Driver` - Updated driver object

**Example:**
```php
// Discover licenses for a driver
$licenses = $sambaSafety->licenseDiscovery()->discoverForExistingDriver('driver-123');

// Find the best license (active, not expired)
$bestLicense = null;
foreach ($licenses as $license) {
    if ($license->isActive() && !$license->isExpired() && !$license->isSuspended()) {
        $bestLicense = $license;
        break;
    }
}

if ($bestLicense) {
    // Link the best license to the driver
    $updatedDriver = $sambaSafety->licenseDiscovery()->linkLicenseToDriver(
        'driver-123',
        $bestLicense->number,
        $bestLicense->state
    );

    echo "✅ Linked license {$bestLicense->number} to {$updatedDriver->getFullName()}\n";
} else {
    echo "⚠️ No valid license found to link\n";
}
```

### unlinkLicenseFromDriver()

Remove a license link from a driver record.

```php
public function unlinkLicenseFromDriver(
    string $driverId,
    string $licenseNumber,
    string $state
): bool
```

**Parameters:**
- `$driverId` (string): Driver ID
- `$licenseNumber` (string): License number to unlink
- `$state` (string): License state

**Returns:** `bool` - True if successful

**Example:**
```php
$success = $sambaSafety->licenseDiscovery()->unlinkLicenseFromDriver(
    'driver-123',
    'D1234567',
    'CA'
);

if ($success) {
    echo "✅ License unlinked successfully\n";
} else {
    echo "❌ Failed to unlink license\n";
}
```

---

## License History

### getLicenseHistory()

Get historical information for a specific license.

```php
public function getLicenseHistory(string $licenseNumber, string $state): array
```

**Parameters:**
- `$licenseNumber` (string): License number
- `$state` (string): State code

**Returns:** `LicenseInfo[]` - Array of historical license records

**Example:**
```php
$history = $sambaSafety->licenseDiscovery()->getLicenseHistory('D1234567', 'CA');

echo "License history for D1234567 (CA):\n";

foreach ($history as $historical) {
    echo "- Issue: " . $historical->issueDate?->format('Y-m-d');
    echo ", Expires: " . $historical->expirationDate?->format('Y-m-d');
    echo ", Status: {$historical->status}\n";

    if (!empty($historical->endorsements)) {
        echo "  Endorsements: " . implode(', ', $historical->endorsements) . "\n";
    }

    if (!empty($historical->restrictions)) {
        echo "  Restrictions: " . implode(', ', $historical->restrictions) . "\n";
    }
}
```

---

## Complete Usage Example

```php
<?php

require_once 'vendor/autoload.php';

use Binjuhor\SambasafetyApi\SambaSafety;

$sambaSafety = new SambaSafety('your-api-key');

// Step 1: Comprehensive license discovery
$personalInfo = [
    'first_name' => 'Sarah',
    'last_name' => 'Wilson',
    'date_of_birth' => '1987-09-12',
    'ssn' => '555-44-3333'
];

echo "🔍 Starting comprehensive license discovery...\n";

// Search all states
$allLicenses = $sambaSafety->licenseDiscovery()->searchAllStates($personalInfo);
echo "Found " . count($allLicenses) . " licenses across all states\n\n";

// Step 2: Analyze discovered licenses
$activeLicenses = [];
$issues = [];

foreach ($allLicenses as $license) {
    echo "📄 License: {$license->number} ({$license->state})\n";
    echo "   Status: {$license->status}\n";
    echo "   Class: {$license->class}\n";

    if ($license->expirationDate) {
        echo "   Expires: " . $license->expirationDate->format('M d, Y') . "\n";
    }

    // Check for issues
    if ($license->isSuspended()) {
        echo "   🚫 SUSPENDED\n";
        $issues[] = "Suspended license in {$license->state}";
    } elseif ($license->isExpired()) {
        echo "   ❌ EXPIRED\n";
        $issues[] = "Expired license in {$license->state}";
    } elseif ($license->isExpiringSoon(60)) {
        echo "   ⚠️ EXPIRES SOON\n";
        $issues[] = "License in {$license->state} expires within 60 days";
    } else {
        echo "   ✅ ACTIVE\n";
        $activeLicenses[] = $license;
    }

    // Show endorsements/restrictions
    if (!empty($license->endorsements)) {
        echo "   Endorsements: " . implode(', ', $license->endorsements) . "\n";
    }
    if (!empty($license->restrictions)) {
        echo "   Restrictions: " . implode(', ', $license->restrictions) . "\n";
    }
    echo "\n";
}

// Step 3: Summary and recommendations
echo "📊 SUMMARY:\n";
echo "Total licenses: " . count($allLicenses) . "\n";
echo "Active licenses: " . count($activeLicenses) . "\n";
echo "Issues found: " . count($issues) . "\n\n";

if (!empty($issues)) {
    echo "⚠️ ISSUES REQUIRING ATTENTION:\n";
    foreach ($issues as $issue) {
        echo "- {$issue}\n";
    }
    echo "\n";
}

// Step 4: Validate primary license
if (!empty($activeLicenses)) {
    $primaryLicense = $activeLicenses[0];
    echo "🔍 Validating primary license: {$primaryLicense->number} ({$primaryLicense->state})\n";

    $validated = $sambaSafety->licenseDiscovery()->validateLicense(
        $primaryLicense->number,
        $primaryLicense->state
    );

    if ($validated) {
        echo "✅ Primary license validated successfully\n";

        // Get license history
        $history = $sambaSafety->licenseDiscovery()->getLicenseHistory(
            $primaryLicense->number,
            $primaryLicense->state
        );

        echo "📚 License has " . count($history) . " historical records\n";
    } else {
        echo "❌ Primary license validation failed\n";
    }
}
```

---

**Next:** [MVR Service API](mvr-service.md) | [Fleet Service API](fleet-service.md)