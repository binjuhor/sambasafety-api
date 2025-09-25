<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Validation;

use Binjuhor\SambasafetyApi\Exceptions\ValidationException;

class DriverValidator
{
    public static function validateCreateData(array $data): void
    {
        $required = ['first_name', 'last_name'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new ValidationException("Field '{$field}' is required");
            }
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }

        if (isset($data['license_number'])) {
            self::validateLicenseNumber($data['license_number']);
        }

        if (isset($data['date_of_birth']) && !self::isValidDate($data['date_of_birth'])) {
            throw new ValidationException('Invalid date_of_birth format. Use ISO 8601 format (YYYY-MM-DD)');
        }
    }

    public static function validateUpdateData(array $data): void
    {
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }

        if (isset($data['license_number'])) {
            self::validateLicenseNumber($data['license_number']);
        }

        if (isset($data['date_of_birth']) && !self::isValidDate($data['date_of_birth'])) {
            throw new ValidationException('Invalid date_of_birth format. Use ISO 8601 format (YYYY-MM-DD)');
        }

        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive', 'suspended'])) {
            throw new ValidationException("Invalid status. Must be one of: active, inactive, suspended");
        }
    }

    private static function validateLicenseNumber(string $licenseNumber): void
    {
        if (empty(trim($licenseNumber))) {
            throw new ValidationException('License number cannot be empty');
        }

        if (strlen($licenseNumber) < 3) {
            throw new ValidationException('License number must be at least 3 characters long');
        }

        if (strlen($licenseNumber) > 50) {
            throw new ValidationException('License number cannot exceed 50 characters');
        }
    }

    private static function isValidDate(string $date): bool
    {
        $formats = ['Y-m-d', 'Y-m-d H:i:s', 'c'];

        foreach ($formats as $format) {
            $dateTime = \DateTime::createFromFormat($format, $date);
            if ($dateTime !== false) {
                return true;
            }
        }

        return false;
    }
}