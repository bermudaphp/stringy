<?php

declare(strict_types=1);

namespace Bermuda\Stdlib\Tests;

use Bermuda\Stdlib\StrMutable;
use Bermuda\Stdlib\StringIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bermuda\Stdlib\StrMutable
 */
class StrMutableTest extends TestCase
{
    public function testCreate(): void
    {
        $str = StrMutable::create('test');
        $this->assertSame('test', $str->toString());
    }

    public function testSubstring(): void
    {
        $str = StrMutable::create('test');
        $this->assertSame('es', $str->substring(1, 2)->toString());
        // In StrMutable, substring modifies the current instance
        $this->assertSame('es', $str->toString());
    }

    public function testToLowerCase(): void
    {
        $str = StrMutable::create('TEST');
        $this->assertSame('test', $str->toLowerCase()->toString());
        // Method modifies the current instance
        $this->assertSame('test', $str->toString());
    }

    public function testToUpperCase(): void
    {
        $str = StrMutable::create('test');
        $this->assertSame('TEST', $str->toUpperCase()->toString());
        // Method modifies the current instance
        $this->assertSame('TEST', $str->toString());
    }

    public function testDelimit(): void
    {
        $str = StrMutable::create('TestString');
        $this->assertSame('test-string', $str->delimit('-')->toString());
        
        $str = StrMutable::create('test_string');
        $this->assertSame('test.string', $str->delimit('.')->toString());
    }

    public function testTrim(): void
    {
        $str = StrMutable::create('  test  ');
        $this->assertSame('test', $str->trim()->toString());
        
        // Test with multibyte string
        $str = StrMutable::create('  тест  ');
        $this->assertSame('тест', $str->trim()->toString());
    }

    public function testTrimStart(): void
    {
        $str = StrMutable::create('  test  ');
        $this->assertSame('test  ', $str->trimStart()->toString());
        
        // Test with multibyte string
        $str = StrMutable::create('  тест  ');
        $this->assertSame('тест  ', $str->trimStart()->toString());
    }

    public function testTrimEnd(): void
    {
        $str = StrMutable::create('  test  ');
        $this->assertSame('  test', $str->trimEnd()->toString());
        
        // Test with multibyte string
        $str = StrMutable::create('  тест  ');
        $this->assertSame('  тест', $str->trimEnd()->toString());
    }

    public function testOffsetExists(): void
    {
        $str = StrMutable::create('test');
        $this->assertTrue(isset($str[0]));
        $this->assertTrue(isset($str[3]));
        $this->assertFalse(isset($str[4]));
        $this->assertFalse(isset($str[-5]));
    }

    public function testOffsetGet(): void
    {
        $str = StrMutable::create('test');
        $this->assertSame('t', $str[0]);
        $this->assertSame('e', $str[1]);
        $this->assertSame('t', $str[-1]);
    }

    public function testOffsetSet(): void
    {
        $str = StrMutable::create('test');
        $str[0] = 'b';
        $this->assertSame('best', $str->toString());
        
        $str[3] = 'p';
        $this->assertSame('besp', $str->toString());
        
        $str[-2] = 'a';
        $this->assertSame('beap', $str->toString());
    }

    public function testOffsetUnset(): void
    {
        $str = StrMutable::create('test');
        unset($str[0]);
        $this->assertSame('est', $str->toString());
        
        $str = StrMutable::create('test');
        unset($str[2]);
        $this->assertSame('tet', $str->toString());
        
        $str = StrMutable::create('test');
        unset($str[-1]);
        $this->assertSame('tes', $str->toString());
    }

    public function testGetIterator(): void
    {
        $str = StrMutable::create('test');
        $iterator = $str->getIterator();
        $this->assertInstanceOf(StringIterator::class, $iterator);
        $this->assertSame('test', (string) $iterator);
    }

    public function testMultibyteString(): void
    {
        $str = StrMutable::create('Привет, мир!');
        $this->assertSame(12, $str->length());
        $this->assertTrue($str->isMultibyte);
        $this->assertSame('П', $str->charAt(0));
        
        $str->substring(0, 6);
        $this->assertSame('Привет', $str->toString());
    }

    public function testMethodChaining(): void
    {
        $str = StrMutable::create('  Hello world  ');
        $result = $str->trim()
                      ->toUpperCase()
                      ->replace(' ', '-');
                      
        $this->assertSame('HELLO-WORLD', $str->toString());
        $this->assertSame($str, $result, 'Method should return $this for chaining');
    }

    public function testSetString(): void
    {
        $str = StrMutable::create('Hello');
        $str->setString('World');
        $this->assertSame('World', $str->toString());
    }
}
