<?php

declare(strict_types=1);

namespace Bermuda\Stdlib\Tests;

use Bermuda\Stdlib\ClsHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bermuda\Stdlib\ClsHelper
 */
class ClsHelperTest extends TestCase
{
    public function testBasename(): void
    {
        $this->assertSame('ClsHelper', ClsHelper::basename('Bermuda\\Stdlib\\ClsHelper'));
        $this->assertSame('ClassName', ClsHelper::basename('ClassName'));
    }

    public function testNamespace(): void
    {
        $this->assertSame('Bermuda\\Stdlib', ClsHelper::namespace('Bermuda\\Stdlib\\ClsHelper'));
        $this->assertNull(ClsHelper::namespace('ClassName'));
    }

    public function testIsValidName(): void
    {
        // Valid class names
        $this->assertTrue(ClsHelper::isValidName('ClassName'));
        $this->assertTrue(ClsHelper::isValidName('Bermuda\\Stdlib\\ClsHelper'));
        $this->assertTrue(ClsHelper::isValidName('_ClassName'));

        // Invalid class names
        $this->assertFalse(ClsHelper::isValidName('0InvalidClass'));
        $this->assertFalse(ClsHelper::isValidName('Class-Name'));
        
        // Test without namespace
        $this->assertTrue(ClsHelper::isValidName('ClassName', false));
        $this->assertFalse(ClsHelper::isValidName('Bermuda\\Stdlib\\ClsHelper', false));
    }

    public function testSplit(): void
    {
        $expected = [
            0 => 'Bermuda\\Stdlib',
            1 => 'ClsHelper'
        ];
        $this->assertSame($expected, ClsHelper::split('Bermuda\\Stdlib\\ClsHelper'));
        $this->assertSame('ClassName', ClsHelper::split('ClassName'));
    }
}
