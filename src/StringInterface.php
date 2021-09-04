<?php

namespace Bermuda\String;

use Bermuda\Arrayable;

/**
 * interface StringInterface
 * @package Bermuda\String
 */
interface StringInterface extends \IteratorAggregate, \ArrayAccess, \Countable, Arrayable, Jsonable, Stringable
{
    /**
     * dump string and die
     */
    public function dd(): void ;

    /**
     * @return string
     */
    public function encoding(): string ;
    
    public function slice(int $bytes): StringInterface ;
   
    /**
     * @param string $encoding
     * @return StringInterface
     */
    public function encode(string $encoding): StringInterface ;

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseInsensitive
     * @return int|null
     */
    public function indexOf(string $needle, int $offset = 0, bool $caseInsensitive = false):? int ;
    
    /**
     * @return StringInterface
     */
    public function copy(): StringInterface ;

    /**
     * @param string $delim
     * @return StringInterface[]|string[]
     */
    public function explode(string $delim = '/', int $limit = PHP_INT_MAX, bool $asString = false): array ;

    /**
     * @return StringInterface
     */
    public function ucFirst(): StringInterface ;

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseInsensitive
     * @return bool
     */
    public function contains(string $needle, int $offset = 0, bool $caseInsensitive = false): bool ;

    /**
     * @param int $length
     * @param string $substring
     * @return StringInterface
     */
    public function truncate(int $length = 200, string $substring = '...'): StringInterface ;

    /**
     * @return int
     */
    public function length(): int ;
    
    /**
     * @return int
     */
    public function getBytes(): int ;

    /**
     * @param string $needle
     * @param bool $requireNeedle
     * @param bool $caseInsensitive
     * @return StringInterface|null
     */
    public function before(string $needle, bool $requireNeedle = true, bool $caseInsensitive = false):? StringInterface ; 

    /**
     * @param string $needle
     * @param bool $requireNeedle
     * @param bool $caseInsensitive
     * @return StringInterface|null
     */
    public function after(string $needle, bool $requireNeedle = true, bool $caseInsensitive = false):? StringInterface ;

    /**
     * @param string $algorithm
     * @return string
     */
    public function hash(string $algorithm = 'sha512'): string ;

    /**
     * @param string $charlist
     * @return Stringy
     */
    public function trim(string $charlist = ' '): StringInterface ;

    /**
     * @param string $charlist
     * @return StringInterface
     */
    public function ltrim(string $charlist = ' '): StringInterface ;

    /**
     * @param string $charlist
     * @return StringInterface
     */
    public function rtrim(string $charlist = ' '): StringInterface ;

    /**
     * @param string|array $search
     * @param string|array $replace
     * @return StringInterface
     */
    public function replace($search, $replace): StringInterface ;

    /**
     * @param string $subject
     * @return StringInterface
     */
    public function prepend(string $subject): StringInterface ;

    /**
     * @param string $subject
     * @return StringInterface
     */
    public function append(string $subject): StringInterface ;

    /**
     * @param string $subject
     * @param bool $caseInsensitive
     * @return bool
     */
    public function equals(string $subject, bool $caseInsensitive = false): bool ;

    /**
     * @param string[] $subject
     * @param bool $caseInsensitive
     * @return bool
     */
    public function equalsAny(array $subject, bool $caseInsensitive = false): bool ;

    /**
     * @return bool
     */
    public function isEmpty(): bool ;
    
    /**
     * @param int $index
     * @return StringInterface
     * @throws \RuntimeException
     */
    public function index(int $index): StringInterface ;

    /**
     * @param int $start
     * @param int $end
     * @return StringInterface
     */
    public function interval(int $start, int $end): StringInterface ; 

    /**
     * @param int $index
     * @return bool
     */
    public function has(int $index): bool ;
    
    /**
     * @return StringInterface|null
     */
    public function first():? StringInterface ;

    /**
     * @return StringInterface|null
     */
    public function last():? StringINterface ;

    /**
     * @param string $char
     * @return StringInterface
     */
    public function wrap(string $char): StringInterface ;
    
    /**
     * @param string|null $char
     * @return bool
     */
    public function isWrapped(string $char): bool ;
    
    /**
     * @param int $pos
     * @return StringInterface[]
     */
    public function break(int $pos): array ;

    /**
     * @param int $pos
     * @param int|null $length
     * @return StringInterface
     */
    public function substring(int $pos, int $length = null): StringInterface ;

    /**
     * @param int $length
     * @return StringInterface
     */
    public function start(int $length): StringInterface ; 

    /**
     * @param int $length
     * @return StringInterface
     */
    public function end(int $length): StringInterface ;

    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param int $limit
     * @param int|null $count
     * @return StringInterface
     */
    public function pregReplace($pattern, $replacement, int $limit = -1, int &$count = null): StringInterface ; 

    /**
     * @param string $pattern
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return bool
     */
    public function match(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0): bool ;

    /**
     * @param string $pattern
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return bool
     */
    public function matchAll(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0): bool ;
    
    /**
     * @return StringInterface
     */
    public function revers(): StringInterface ;

    /**
     * @return StringInterface
     */
    public function lcFirst(): StringInterface ;

    /**
     * @param int $len
     * @return StringInterface
     */
    public function rand(int $len): StringInterface ;

    /**
     * Write string
     */
    public function write(): void ;

    /**
     * @return int
     */
    public function lastIndex():? int ;

    /**
     * @return int|null
     */
    public function firstIndex():? int ;

    /**
     * @return StringInterface
     */
    public function shuffle(): StringInterface ;

    /**
     * @return StringInterface
     */
    public function toUpper(): StringInterface ;

    /**
     * @return StringInterface
     */
    public function toLower(): StringInterface ; 

    /**
     * @param int $length
     * @return StringInterface[]
     */
    public function split(int $length = 1): array ;

    /**
     * @param mixed ... $args
     * @return StringInterface
     * @throws \RuntimeException
     */
    public function format(... $args): StringInterface ;

    /**
     * @return bool
     */
    public function isJson(): bool ;
}
