<?php

declare(strict_types=1);

namespace Bermuda\Stdlib\Tests;

use Bermuda\Stdlib\Stringy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bermuda\Stdlib\Stringy
 */
class StringyTest extends TestCase
{
    public function testDelimit(): void
    {
        $this->assertSame('test-string', Stringy::delimit('TestString', '-'));
        $this->assertSame('test+string', Stringy::delimit('test_string', '+'));
        $this->assertSame('camel-case-string', Stringy::delimit('camelCaseString', '-'));
    }

    public function testTrim(): void
    {
        $this->assertSame('test', Stringy::trim('  test  '));
        
        // Test with multibyte string
        $this->assertSame('тест', Stringy::trim('  тест  '));
        
        // Test with custom characters
        $this->assertSame('test', Stringy::trim('__test__', '_'));
    }

    public function testTrimStart(): void
    {
        $this->assertSame('test  ', Stringy::trimStart('  test  '));
        
        // Test with multibyte string
        $this->assertSame('тест  ', Stringy::trimStart('  тест  '));
        
        // Test with custom characters
        $this->assertSame('test__', Stringy::trimStart('__test__', '_'));
    }

    public function testTrimEnd(): void
    {
        $this->assertSame('  test', Stringy::trimEnd('  test  '));
        
        // Test with multibyte string
        $this->assertSame('  тест', Stringy::trimEnd('  тест  '));
        
        // Test with custom characters
        $this->assertSame('__test', Stringy::trimEnd('__test__', '_'));
    }

    public function testIsMultibyte(): void
    {
        $this->assertTrue(Stringy::isMultibyte('тест'));
        $this->assertTrue(Stringy::isMultibyte('你好'));
        $this->assertFalse(Stringy::isMultibyte('test'));
    }

    public function testReplace(): void
    {
        $this->assertSame('Hello Earth', Stringy::replace('Hello World', 'World', 'Earth'));
        $this->assertSame('HELLO EARTH', Stringy::replace('hello world', 'hello world', 'HELLO EARTH', false));
    }

    public function testReplaceCallback(): void
    {
        $result = Stringy::replaceCallback('Hello World', '/\w+/u', function ($matches) {
            return strtoupper($matches[0]);
        });
        
        $this->assertSame('HELLO WORLD', $result);
    }
}
