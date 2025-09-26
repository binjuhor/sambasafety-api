# License Discovery Examples

Complete examples showing how to use the License Discovery service for finding, validating, and managing driver licenses.

## Table of Contents

- [Basic License Discovery](#basic-license-discovery)
- [Multi-State Search](#multi-state-search)
- [License Validation](#license-validation)
- [License Monitoring](#license-monitoring)
- [Driver Integration](#driver-integration)
- [Bulk Operations](#bulk-operations)
- [Error Handling](#error-handling)
- [Advanced Workflows](#advanced-workflows)

---

## Basic License Discovery

### Simple Driver Information Search

```php
<?php

require_once 'vendor/autoload.php';

use Binjuhor\SambasafetyApi\SambaSafety;

$sambaSafety = new SambaSafety('your-api-key');

// Basic license discovery by personal information
$licenses = $sambaSafety->licenseDiscovery()->discoverByDriverInfo(
    firstName: 'John',
    lastName: 'Smith',
    dateOfBirth: '1985-03-15',
    state: 'CA'  // Search specific state
);

echo "Found " . count($licenses) . " licenses for John Smith in CA:\n\n";

foreach ($licenses as $license) {
    echo "📄 License: {$license->number}\n";
    echo "   State: {$license->state}\n";
    echo "   Status: {$license->status}\n";
    echo "   Class: {$license->class}\n";

    if ($license->expirationDate) {
        echo "   Expires: " . $license->expirationDate->format('M d, Y') . "\n";
    }

    // Check license status
    if ($license->isActive()) {
        echo "   ✅ Active\n";
    } elseif ($license->isExpired()) {
        echo "   ❌ Expired\n";
    } elseif ($license->isSuspended()) {
        echo "   🚫 Suspended\n";
    }

    echo "\n";
}
```

### SSN-Based Discovery

```php
// Discover licenses using Social Security Number
$licenses = $sambaSafety->licenseDiscovery()->discoverBySsn('123-45-6789');

echo "SSN-based discovery found " . count($licenses) . " licenses:\n";

foreach ($licenses as $license) {
    echo "- {$license->number} ({$license->state}): {$license->status}\n";

    // Check for commercial license
    if ($license->isCommercial()) {
        echo "  🚛 Commercial License (Class {$license->class})\n";
    }

    // Check endorsements
    if (!empty($license->endorsements)) {
        echo "  Endorsements: " . implode(', ', $license->endorsements) . "\n";
    }
}
```

---

## Multi-State Search

### Targeted Multi-State Discovery

```php
// Search specific high-population states
$targetStates = ['CA', 'TX', 'FL', 'NY', 'PA'];

$licenses = $sambaSafety->licenseDiscovery()->discoverMultipleStates([
    'first_name' => 'Maria',
    'last_name' => 'Garcia',
    'date_of_birth' => '1990-08-22',
    'ssn' => '987-65-4321'
], $targetStates);

echo "Multi-state search results:\n";

// Group results by state
$byState = [];
foreach ($licenses as $license) {
    $byState[$license->state][] = $license;
}

foreach ($byState as $state => $stateLicenses) {
    echo "\n{$state} ({count($stateLicenses)} licenses found):\n";

    foreach ($stateLicenses as $license) {
        echo "  - {$license->number}: {$license->status}";

        if ($license->isExpiringSoon(60)) {
            echo " ⚠️ EXPIRES SOON";
        }

        echo "\n";
    }
}
```

### Comprehensive All-States Search

```php
// Search all available states and provinces
$allLicenses = $sambaSafety->licenseDiscovery()->searchAllStates([
    'first_name' => 'Robert',
    'last_name' => 'Johnson',
    'date_of_birth' => '1982-11-30',
    'middle_name' => 'Michael',
    'ssn' => '555-44-3333'
]);

echo "Comprehensive search found " . count($allLicenses) . " licenses\n\n";

// Analyze results
$analysis = [
    'active' => 0,
    'expired' => 0,
    'suspended' => 0,
    'commercial' => 0,
    'motorcycle' => 0,
    'states' => []
];

foreach ($allLicenses as $license) {
    // Status analysis
    if ($license->isActive()) {
        $analysis['active']++;
    } elseif ($license->isExpired()) {
        $analysis['expired']++;
    } elseif ($license->isSuspended()) {
        $analysis['suspended']++;
    }

    // Type analysis
    if ($license->isCommercial()) {
        $analysis['commercial']++;
    }

    if ($license->hasEndorsement('M')) {
        $analysis['motorcycle']++;
    }

    // State tracking
    $analysis['states'][$license->state] = ($analysis['states'][$license->state] ?? 0) + 1;
}

// Print analysis
echo "📊 ANALYSIS:\n";
echo "Active: {$analysis['active']}\n";
echo "Expired: {$analysis['expired']}\n";
echo "Suspended: {$analysis['suspended']}\n";
echo "Commercial: {$analysis['commercial']}\n";
echo "Motorcycle endorsed: {$analysis['motorcycle']}\n";

echo "\nStates with licenses:\n";
arsort($analysis['states']);
foreach ($analysis['states'] as $state => $count) {
    echo "- {$state}: {$count}\n";
}
```

---

## License Validation

### Single License Validation

```php
// Validate a specific license
$licenseToValidate = 'D1234567';
$state = 'CA';

echo "🔍 Validating license {$licenseToValidate} in {$state}...\n";

$license = $sambaSafety->licenseDiscovery()->validateLicense($licenseToValidate, $state);

if ($license !== null) {
    echo "✅ LICENSE VALID\n\n";

    // Detailed information
    echo "Details:\n";
    echo "- Number: {$license->number}\n";
    echo "- State: {$license->state}\n";
    echo "- Status: {$license->status}\n";
    echo "- Class: {$license->class}\n";

    if ($license->issueDate) {
        echo "- Issued: " . $license->issueDate->format('M d, Y') . "\n";
    }

    if ($license->expirationDate) {
        echo "- Expires: " . $license->expirationDate->format('M d, Y') . "\n";
    }

    // Status checks
    echo "\nStatus Checks:\n";
    echo "- Active: " . ($license->isActive() ? 'Yes ✅' : 'No ❌') . "\n";
    echo "- Expired: " . ($license->isExpired() ? 'Yes ❌' : 'No ✅') . "\n";
    echo "- Suspended: " . ($license->isSuspended() ? 'Yes 🚫' : 'No ✅') . "\n";
    echo "- Commercial: " . ($license->isCommercial() ? 'Yes 🚛' : 'No 🚗') . "\n";

    // Expiration warning
    if ($license->isExpiringSoon(30)) {
        echo "\n⚠️ WARNING: License expires within 30 days!\n";
    } elseif ($license->isExpiringSoon(90)) {
        echo "\n🔔 NOTICE: License expires within 90 days\n";
    }

    // Show endorsements and restrictions
    if (!empty($license->endorsements)) {
        echo "\nEndorsements: " . implode(', ', $license->endorsements) . "\n";
    }

    if (!empty($license->restrictions)) {
        echo "Restrictions: " . implode(', ', $license->restrictions) . "\n";
    }

} else {
    echo "❌ LICENSE NOT FOUND OR INVALID\n";
    echo "Possible reasons:\n";
    echo "- License number doesn't exist\n";
    echo "- License is cancelled/revoked\n";
    echo "- Incorrect state specified\n";
    echo "- Data entry error\n";
}
```

### Bulk License Validation

```php
// Validate multiple licenses at once
$licensesToValidate = [
    ['license_number' => 'D1234567', 'state' => 'CA'],
    ['license_number' => 'DL987654', 'state' => 'NY'],
    ['license_number' => 'T555666', 'state' => 'TX'],
    ['license_number' => 'FL123789', 'state' => 'FL'],
    ['license_number' => 'INVALID', 'state' => 'XX']  // Invalid for testing
];

echo "🔍 Bulk validating " . count($licensesToValidate) . " licenses...\n\n";

$results = $sambaSafety->licenseDiscovery()->bulkValidation($licensesToValidate);

$validCount = 0;
$invalidCount = 0;

foreach ($results as $result) {
    echo "📄 {$result['license_number']} ({$result['state']}): ";

    if ($result['valid']) {
        echo "✅ VALID";
        $validCount++;

        if ($result['license_info']) {
            $license = $result['license_info'];
            echo " - Status: {$license->status}";
            echo " - Expires: " . $license->expirationDate?->format('Y-m-d');

            if ($license->isExpiringSoon(60)) {
                echo " ⚠️ EXPIRES SOON";
            }
        }
    } else {
        echo "❌ INVALID";
        $invalidCount++;

        if (!empty($result['errors'])) {
            echo " - Errors: " . implode(', ', $result['errors']);
        }
    }

    echo "\n";
}

echo "\n📊 SUMMARY:\n";
echo "Valid licenses: {$validCount}\n";
echo "Invalid licenses: {$invalidCount}\n";
echo "Success rate: " . round(($validCount / count($results)) * 100, 1) . "%\n";
```

---

## License Monitoring

### Find Expiring Licenses

```php
// Find licenses expiring in the next 30 days
echo "🔍 Finding licenses expiring in next 30 days...\n";

$expiringSoon = $sambaSafety->licenseDiscovery()->findExpiringSoon(30);

if (empty($expiringSoon)) {
    echo "✅ No licenses expiring in the next 30 days\n";
} else {
    echo "⚠️ Found " . count($expiringSoon) . " licenses expiring soon:\n\n";

    foreach ($expiringSoon as $license) {
        $daysUntilExpiry = $license->expirationDate ?
            (new DateTime())->diff($license->expirationDate)->days : 'Unknown';

        echo "📄 {$license->number} ({$license->state})\n";
        echo "   Expires: " . $license->expirationDate?->format('M d, Y') . "\n";
        echo "   Days remaining: {$daysUntilExpiry}\n";

        // Urgency level
        if ($daysUntilExpiry <= 7) {
            echo "   🚨 CRITICAL: Expires in {$daysUntilExpiry} days!\n";
        } elseif ($daysUntilExpiry <= 14) {
            echo "   ⚠️ URGENT: Expires in {$daysUntilExpiry} days\n";
        } else {
            echo "   🔔 NOTICE: Expires in {$daysUntilExpiry} days\n";
        }

        echo "\n";
    }

    // Group by urgency
    $critical = array_filter($expiringSoon, fn($l) => $l->expirationDate &&
        (new DateTime())->diff($l->expirationDate)->days <= 7);
    $urgent = array_filter($expiringSoon, fn($l) => $l->expirationDate &&
        (new DateTime())->diff($l->expirationDate)->days <= 14 &&
        (new DateTime())->diff($l->expirationDate)->days > 7);

    echo "📊 URGENCY BREAKDOWN:\n";
    echo "Critical (≤7 days): " . count($critical) . "\n";
    echo "Urgent (8-14 days): " . count($urgent) . "\n";
    echo "Notice (15-30 days): " . (count($expiringSoon) - count($critical) - count($urgent)) . "\n";
}
```

### Find Suspended and Expired Licenses

```php
// Find suspended licenses
echo "🔍 Finding suspended licenses...\n";
$suspended = $sambaSafety->licenseDiscovery()->findSuspendedLicenses();

// Find expired licenses
echo "🔍 Finding expired licenses...\n";
$expired = $sambaSafety->licenseDiscovery()->findExpiredLicenses();

echo "\n📊 LICENSE STATUS REPORT:\n";
echo "Suspended licenses: " . count($suspended) . "\n";
echo "Expired licenses: " . count($expired) . "\n";

if (!empty($suspended)) {
    echo "\n🚫 SUSPENDED LICENSES:\n";
    foreach ($suspended as $license) {
        echo "- {$license->number} ({$license->state}): {$license->status}\n";

        if (!empty($license->restrictions)) {
            echo "  Restrictions: " . implode(', ', $license->restrictions) . "\n";
        }
    }
}

if (!empty($expired)) {
    echo "\n❌ EXPIRED LICENSES:\n";
    foreach ($expired as $license) {
        $daysSinceExpired = $license->expirationDate ?
            (new DateTime())->diff($license->expirationDate)->days : 'Unknown';

        echo "- {$license->number} ({$license->state}): ";
        echo "Expired {$daysSinceExpired} days ago\n";
    }
}
```

### License Monitoring Dashboard

```php
function createLicenseMonitoringDashboard($sambaSafety): array
{
    echo "🏗️ Building license monitoring dashboard...\n";

    // Get all monitoring data
    $expiring7 = $sambaSafety->licenseDiscovery()->findExpiringSoon(7);
    $expiring30 = $sambaSafety->licenseDiscovery()->findExpiringSoon(30);
    $suspended = $sambaSafety->licenseDiscovery()->findSuspendedLicenses();
    $expired = $sambaSafety->licenseDiscovery()->findExpiredLicenses();

    $dashboard = [
        'critical_expiring' => count($expiring7),
        'expiring_soon' => count($expiring30) - count($expiring7),
        'suspended' => count($suspended),
        'expired' => count($expired),
        'total_issues' => count($expiring30) + count($suspended) + count($expired)
    ];

    return $dashboard;
}

// Generate dashboard
$dashboard = createLicenseMonitoringDashboard($sambaSafety);

echo "\n📊 LICENSE MONITORING DASHBOARD\n";
echo "=" . str_repeat("=", 40) . "\n";
echo "🚨 Critical (expires ≤7 days):  {$dashboard['critical_expiring']}\n";
echo "⚠️ Warning (expires ≤30 days):  {$dashboard['expiring_soon']}\n";
echo "🚫 Suspended licenses:          {$dashboard['suspended']}\n";
echo "❌ Expired licenses:            {$dashboard['expired']}\n";
echo "─" . str_repeat("─", 40) . "\n";
echo "📈 Total issues requiring attention: {$dashboard['total_issues']}\n";

// Alert levels
if ($dashboard['total_issues'] == 0) {
    echo "\n✅ All licenses are in good standing!\n";
} elseif ($dashboard['critical_expiring'] > 0) {
    echo "\n🚨 IMMEDIATE ACTION REQUIRED: {$dashboard['critical_expiring']} licenses expire within 7 days!\n";
} elseif ($dashboard['total_issues'] > 10) {
    echo "\n⚠️ HIGH ALERT: {$dashboard['total_issues']} licenses need attention\n";
} else {
    echo "\n🔔 Routine monitoring: {$dashboard['total_issues']} licenses need attention\n";
}
```

---

## Driver Integration

### Discover and Link Licenses to Existing Driver

```php
// Discover licenses for an existing driver
$driverId = 'driver-123';

echo "🔍 Discovering licenses for existing driver {$driverId}...\n";

$licenses = $sambaSafety->licenseDiscovery()->discoverForExistingDriver($driverId);

if (empty($licenses)) {
    echo "❌ No licenses found for this driver\n";
} else {
    echo "✅ Found " . count($licenses) . " licenses:\n\n";

    $bestLicense = null;
    $bestScore = -1;

    foreach ($licenses as $license) {
        echo "📄 {$license->number} ({$license->state}):\n";
        echo "   Status: {$license->status}\n";
        echo "   Class: {$license->class}\n";

        // Calculate license score for selection
        $score = 0;
        if ($license->isActive()) $score += 10;
        if (!$license->isExpired()) $score += 5;
        if (!$license->isSuspended()) $score += 5;
        if (!$license->isExpiringSoon(90)) $score += 2;
        if ($license->isCommercial()) $score += 3;

        echo "   Score: {$score}/25\n";

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestLicense = $license;
        }

        // Status indicators
        if ($license->isActive() && !$license->isExpired() && !$license->isSuspended()) {
            echo "   ✅ Suitable for use\n";
        } else {
            $issues = [];
            if (!$license->isActive()) $issues[] = 'Not active';
            if ($license->isExpired()) $issues[] = 'Expired';
            if ($license->isSuspended()) $issues[] = 'Suspended';
            echo "   ❌ Issues: " . implode(', ', $issues) . "\n";
        }

        echo "\n";
    }

    // Link the best license
    if ($bestLicense && $bestScore >= 15) {
        echo "🔗 Linking best license {$bestLicense->number} ({$bestLicense->state}) to driver...\n";

        try {
            $updatedDriver = $sambaSafety->licenseDiscovery()->linkLicenseToDriver(
                $driverId,
                $bestLicense->number,
                $bestLicense->state
            );

            echo "✅ License linked successfully!\n";
            echo "Driver: {$updatedDriver->getFullName()}\n";
            echo "License: {$updatedDriver->licenseNumber}\n";

        } catch (Exception $e) {
            echo "❌ Failed to link license: {$e->getMessage()}\n";
        }

    } else {
        echo "⚠️ No suitable license found for linking (best score: {$bestScore}/25)\n";
        echo "Manual review recommended\n";
    }
}
```

### Complete Driver Onboarding with License Discovery

```php
function onboardNewDriver($sambaSafety, $personalInfo): array
{
    echo "🚀 Starting driver onboarding process...\n";

    $results = [
        'success' => false,
        'driver_id' => null,
        'licenses_found' => 0,
        'primary_license' => null,
        'issues' => []
    ];

    try {
        // Step 1: Discover all licenses
        echo "Step 1: Discovering licenses...\n";
        $licenses = $sambaSafety->licenseDiscovery()->searchAllStates($personalInfo);
        $results['licenses_found'] = count($licenses);

        if (empty($licenses)) {
            $results['issues'][] = 'No licenses found';
            echo "❌ No licenses found for this person\n";
            return $results;
        }

        echo "✅ Found {$results['licenses_found']} licenses\n";

        // Step 2: Validate licenses and find best one
        echo "Step 2: Validating licenses...\n";
        $validLicenses = [];

        foreach ($licenses as $license) {
            if ($license->isActive() && !$license->isExpired() && !$license->isSuspended()) {
                $validLicenses[] = $license;
            }
        }

        if (empty($validLicenses)) {
            $results['issues'][] = 'No valid licenses found';
            echo "❌ No valid licenses found\n";
            return $results;
        }

        // Select primary license (prefer commercial, then newest)
        $primaryLicense = $validLicenses[0];
        foreach ($validLicenses as $license) {
            if ($license->isCommercial() && !$primaryLicense->isCommercial()) {
                $primaryLicense = $license;
            } elseif ($license->issueDate && $primaryLicense->issueDate &&
                     $license->issueDate > $primaryLicense->issueDate) {
                $primaryLicense = $license;
            }
        }

        $results['primary_license'] = $primaryLicense;
        echo "✅ Selected primary license: {$primaryLicense->number} ({$primaryLicense->state})\n";

        // Step 3: Create driver record
        echo "Step 3: Creating driver record...\n";
        $driverData = [
            'first_name' => $personalInfo['first_name'],
            'last_name' => $personalInfo['last_name'],
            'license_number' => $primaryLicense->number,
            'email' => $personalInfo['email'] ?? null,
            'metadata' => [
                'onboarding_date' => date('Y-m-d'),
                'license_state' => $primaryLicense->state,
                'license_class' => $primaryLicense->class,
                'license_expires' => $primaryLicense->expirationDate?->format('Y-m-d'),
                'total_licenses_found' => count($licenses),
                'commercial_license' => $primaryLicense->isCommercial()
            ]
        ];

        $driver = $sambaSafety->drivers()->create($driverData);
        $results['driver_id'] = $driver->id;

        echo "✅ Driver created: {$driver->id}\n";

        // Step 4: Link additional licenses if found
        echo "Step 4: Linking additional licenses...\n";
        $linkedCount = 0;
        foreach ($licenses as $license) {
            if ($license->number !== $primaryLicense->number) {
                try {
                    $sambaSafety->licenseDiscovery()->linkLicenseToDriver(
                        $driver->id,
                        $license->number,
                        $license->state
                    );
                    $linkedCount++;
                } catch (Exception $e) {
                    // Continue with other licenses
                }
            }
        }

        echo "✅ Linked {$linkedCount} additional licenses\n";

        $results['success'] = true;
        echo "🎉 Driver onboarding completed successfully!\n";

    } catch (Exception $e) {
        $results['issues'][] = $e->getMessage();
        echo "❌ Onboarding failed: {$e->getMessage()}\n";
    }

    return $results;
}

// Example usage
$personalInfo = [
    'first_name' => 'Michael',
    'last_name' => 'Thompson',
    'date_of_birth' => '1987-04-12',
    'email' => 'michael.thompson@company.com',
    'ssn' => '444-33-2222'
];

$results = onboardNewDriver($sambaSafety, $personalInfo);

if ($results['success']) {
    echo "\n✅ ONBOARDING SUCCESSFUL\n";
    echo "Driver ID: {$results['driver_id']}\n";
    echo "Licenses found: {$results['licenses_found']}\n";
    echo "Primary license: {$results['primary_license']->number} ({$results['primary_license']->state})\n";
} else {
    echo "\n❌ ONBOARDING FAILED\n";
    echo "Issues: " . implode(', ', $results['issues']) . "\n";
}
```

---

## Advanced Workflows

### Comprehensive License Audit

```php
function performLicenseAudit($sambaSafety, $driverIds): array
{
    echo "🔍 Starting comprehensive license audit...\n";

    $auditResults = [
        'total_drivers' => count($driverIds),
        'drivers_processed' => 0,
        'licenses_discovered' => 0,
        'issues_found' => [],
        'recommendations' => []
    ];

    foreach ($driverIds as $driverId) {
        echo "Auditing driver {$driverId}...\n";

        try {
            // Get current driver info
            $driver = $sambaSafety->drivers()->get($driverId);

            // Discover all licenses for this driver
            $discoveredLicenses = $sambaSafety->licenseDiscovery()->discoverForExistingDriver($driverId);
            $auditResults['licenses_discovered'] += count($discoveredLicenses);

            // Check current license status
            if ($driver->licenseNumber) {
                // Extract state from metadata or assume from license format
                $currentState = $driver->metadata['license_state'] ?? 'CA'; // Default assumption

                $currentLicense = $sambaSafety->licenseDiscovery()->validateLicense(
                    $driver->licenseNumber,
                    $currentState
                );

                if (!$currentLicense) {
                    $auditResults['issues_found'][] = [
                        'driver_id' => $driverId,
                        'type' => 'invalid_current_license',
                        'message' => 'Current license number is invalid'
                    ];
                }
            }

            // Check for undisclosed licenses
            foreach ($discoveredLicenses as $license) {
                if ($license->number !== $driver->licenseNumber) {
                    $auditResults['issues_found'][] = [
                        'driver_id' => $driverId,
                        'type' => 'undisclosed_license',
                        'message' => "Undisclosed license found: {$license->number} ({$license->state})"
                    ];
                }

                // Check for suspended/expired licenses
                if ($license->isSuspended()) {
                    $auditResults['issues_found'][] = [
                        'driver_id' => $driverId,
                        'type' => 'suspended_license',
                        'message' => "Suspended license: {$license->number} ({$license->state})"
                    ];
                }

                if ($license->isExpired()) {
                    $auditResults['issues_found'][] = [
                        'driver_id' => $driverId,
                        'type' => 'expired_license',
                        'message' => "Expired license: {$license->number} ({$license->state})"
                    ];
                }
            }

            $auditResults['drivers_processed']++;

        } catch (Exception $e) {
            $auditResults['issues_found'][] = [
                'driver_id' => $driverId,
                'type' => 'audit_error',
                'message' => "Audit failed: {$e->getMessage()}"
            ];
        }

        // Progress indicator
        if ($auditResults['drivers_processed'] % 10 == 0) {
            echo "Processed {$auditResults['drivers_processed']}/{$auditResults['total_drivers']} drivers...\n";
        }
    }

    // Generate recommendations
    $issueTypes = array_count_values(array_column($auditResults['issues_found'], 'type'));

    foreach ($issueTypes as $type => $count) {
        switch ($type) {
            case 'suspended_license':
                $auditResults['recommendations'][] = "Review and potentially suspend {$count} drivers with suspended licenses";
                break;
            case 'expired_license':
                $auditResults['recommendations'][] = "Request license renewals for {$count} drivers with expired licenses";
                break;
            case 'undisclosed_license':
                $auditResults['recommendations'][] = "Update records for {$count} drivers with undisclosed licenses";
                break;
            case 'invalid_current_license':
                $auditResults['recommendations'][] = "Verify and correct {$count} invalid license numbers";
                break;
        }
    }

    return $auditResults;
}

// Run audit on sample drivers
$driverIds = ['driver-123', 'driver-456', 'driver-789']; // Example IDs
$auditResults = performLicenseAudit($sambaSafety, $driverIds);

// Display results
echo "\n📊 AUDIT RESULTS:\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "Total drivers audited: {$auditResults['drivers_processed']}/{$auditResults['total_drivers']}\n";
echo "Total licenses discovered: {$auditResults['licenses_discovered']}\n";
echo "Issues found: " . count($auditResults['issues_found']) . "\n\n";

if (!empty($auditResults['issues_found'])) {
    echo "🚨 ISSUES FOUND:\n";
    foreach ($auditResults['issues_found'] as $issue) {
        echo "- {$issue['driver_id']}: {$issue['message']}\n";
    }
    echo "\n";
}

if (!empty($auditResults['recommendations'])) {
    echo "💡 RECOMMENDATIONS:\n";
    foreach ($auditResults['recommendations'] as $recommendation) {
        echo "- {$recommendation}\n";
    }
}
```

This comprehensive license discovery documentation provides practical examples for all major use cases, from basic discovery to complex audit workflows. Each example includes proper error handling and real-world considerations.

---

**Related:** [License Discovery Service API](../api/license-discovery-service.md) | [Driver Management Examples](driver-management.md)