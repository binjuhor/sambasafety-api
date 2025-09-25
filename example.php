<?php

require_once __DIR__ . '/vendor/autoload.php';

use Binjuhor\SambasafetyApi\SambaSafety;

// Initialize the SDK
$samba = new SambaSafety('your-api-key-here');

try {
    // Get all drivers
    $drivers = $samba->drivers()->list();

    echo "Found " . count($drivers) . " drivers\n";

    // Create a new driver (example)
    // $newDriver = $samba->drivers()->create([
    //     'first_name' => 'John',
    //     'last_name' => 'Doe',
    //     'license_number' => 'ABC123456',
    //     'email' => 'john.doe@example.com'
    // ]);

} catch (\Binjuhor\SambasafetyApi\Exceptions\AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage() . "\n";
} catch (\Binjuhor\SambasafetyApi\Exceptions\SambaSafetyException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
}