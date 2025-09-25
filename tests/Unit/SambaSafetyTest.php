<?php

declare(strict_types=1);

namespace Binjuhor\SambasafetyApi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Binjuhor\SambasafetyApi\SambaSafety;
use Binjuhor\SambasafetyApi\Services\DriverService;

final class SambaSafetyTest extends TestCase
{
    private const TEST_API_KEY = 'test-api-key';

    public function testCanCreateInstance(): void
    {
        $sdk = new SambaSafety(self::TEST_API_KEY);

        self::assertInstanceOf(SambaSafety::class, $sdk);
    }

    public function testCanAccessDriversService(): void
    {
        $sdk = new SambaSafety(self::TEST_API_KEY);
        $driversService = $sdk->drivers();

        self::assertInstanceOf(DriverService::class, $driversService);
    }

    public function testStaticCreateMethod(): void
    {
        $sdk = SambaSafety::create(self::TEST_API_KEY);

        self::assertInstanceOf(SambaSafety::class, $sdk);
    }

    public function testDriversServiceReturnsSameInstance(): void
    {
        $sdk = new SambaSafety(self::TEST_API_KEY);

        $service1 = $sdk->drivers();
        $service2 = $sdk->drivers();

        self::assertSame($service1, $service2);
    }
}