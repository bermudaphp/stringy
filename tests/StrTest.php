<?php

declare(strict_types=1);

namespace Bermuda\Stdlib\Tests;

use Bermuda\Stdlib\Str;
use Bermuda\Stdlib\StrMutable;
use Bermuda\Stdlib\StringIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bermuda\Stdlib\Str
 */
class StrTest extends TestCase
{
    public function testFrom(): void
    {
        $str = Str::from('test');
        $this->assertSame('test', $str->toString());
    }

    public function testAt(): void
    {
        $str = Str::from('test');
        $this->assertSame('e', $str->at(1)->toString());
    }

    public function testSubstring(): void
    {
        $str = Str::from('test');
        $this->assertSame('es', $str->substring(1, 2)->toString());
    }

    public function testToLowerCase(): void
    {
        $str = Str::from('TEST');
        $this->assertSame('test', $str->toLowerCase()->toString());
    }

    public function testToUpperCase(): void
    {
        $str = Str::from('test');
        $this->assertSame('TEST', $str->toUpperCase()->toString());
    }

    public function testDelimit(): void
    {
        $str = Str::from('TestString');
        $this->assertSame('test-string', $str->delimit('-')->toString());
        
        $str = Str::from('test_string');
        $this->assertSame('test.string', $str->delimit('.')->toString());
    }

    public function testTrim(): void
    {
        $str = Str::from('  test  ');
        $this->assertSame('test', $str->trim()->toString());
        
        // Test with multibyte string
        $str = Str::from('  тест  ');
        $this->assertSame('тест', $str->trim()->toString());
    }

    public function testTrimStart(): void
    {
        $str = Str::from('  test  ');
        $this->assertSame('test  ', $str->trimStart()->toString());
        
        // Test with multibyte string
        $str = Str::from('  тест  ');
        $this->assertSame('тест  ', $str->trimStart()->toString());
    }

    public function testTrimEnd(): void
    {
        $str = Str::from('  test  ');
        $this->assertSame('  test', $str->trimEnd()->toString());
        
        // Test with multibyte string
        $str = Str::from('  тест  ');
        $this->assertSame('  тест', $str->trimEnd()->toString());
    }

    public function testGetIterator(): void
    {
        $str = Str::from('test');
        $iterator = $str->getIterator();
        $this->assertInstanceOf(StringIterator::class, $iterator);
        $this->assertSame('test', (string) $iterator);
    }

    public function testMultibyteString(): void
    {
        $str = Str::from('Привет, мир!');
        $this->assertSame(12, $str->length());
        $this->assertTrue($str->isMultibyte);
        $this->assertSame('П', $str->charAt(0));
        $this->assertSame('Привет', $str->substring(0, 6)->toString());
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue(Str::from('')->isEmpty());
        $this->assertFalse(Str::from('test')->isEmpty());
    }

    public function testIsBlank(): void
    {
        $this->assertTrue(Str::from('')->isBlank());
        $this->assertTrue(Str::from('   ')->isBlank());
        $this->assertFalse(Str::from('test')->isBlank());
    }

    public function testContains(): void
    {
        $str = Str::from('Hello World');
        $this->assertTrue($str->contains('Hello'));
        $this->assertTrue($str->contains(['Hello', 'Test']));
        $this->assertTrue($str->contains('hello', false)); // Case insensitive
        $this->assertFalse($str->contains('hello'));       // Case sensitive
    }

    public function testReplace(): void
    {
        $str = Str::from('Hello World');
        $this->assertSame('Hello Earth', $str->replace('World', 'Earth')->toString());
        $this->assertSame('Hi Earth', $str->replace(['Hello', 'World'], ['Hi', 'Earth'])->toString());
    }

    public function testReverse(): void
    {
        $str = Str::from('Hello');
        $this->assertSame('olleH', $str->reverse()->toString());

        // Test with multibyte
        $str = Str::from('Привет');
        $this->assertSame('тевирП', $str->reverse()->toString());
    }
}
