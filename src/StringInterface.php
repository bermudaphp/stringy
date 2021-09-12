<?php

namespace Bermuda\String;

use ArrayAccess;
use Bermuda\Arrayable;
use Countable;
use IteratorAggregate;
use RuntimeException;

interface IString extends IteratorAggregate, ArrayAccess, Countable, Arrayable, Jsonable, Stringable
{
    /**
     * dump string and die
     */
    public function dd(): void;

    /**
     * @return string
     */
    public function encoding(): string;

    public function slice(int $bytes): IString;

    /**
     * @param string $encoding
     * @return IString
     */
    public function encode(string $encoding): IString;

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseInsensitive
     * @return int|null
     */
    public function indexOf(string $needle, int $offset = 0, bool $caseInsensitive = false): ?int;

    /**
     * @return IString
     */
    public function copy(): IString;

    /**
     * @param string $delim
     * @return IString[]|string[]
     */
    public function explode(string $delim = '/', int $limit = PHP_INT_MAX, bool $asString = false): array;

    /**
     * @return IString
     */
    public function ucFirst(): IString;

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseInsensitive
     * @return bool
     */
    public function contains(string $needle, int $offset = 0, bool $caseInsensitive = false): bool;

    /**
     * @param int $length
     * @param string $substring
     * @return IString
     */
    public function truncate(int $length = 200, string $substring = '...'): IString;

    /**
     * @return int
     */
    public function length(): int;

    /**
     * @return int
     */
    public function getBytes(): int;

    /**
     * @param string $needle
     * @param bool $requireNeedle
     * @param bool $caseInsensitive
     * @return IString|null
     */
    public function before(string $needle, bool $requireNeedle = true, bool $caseInsensitive = false): ?IString;

    /**
     * @param string $needle
     * @param bool $requireNeedle
     * @param bool $caseInsensitive
     * @return IString|null
     */
    public function after(string $needle, bool $requireNeedle = true, bool $caseInsensitive = false): ?IString;

    /**
     * @param string $algorithm
     * @return string
     */
    public function hash(string $algorithm = 'sha512'): string;

    /**
     * @param string $charlist
     * @return Stringy
     */
    public function trim(string $charlist = ' '): IString;

    /**
     * @param string $charlist
     * @return IString
     */
    public function ltrim(string $charlist = ' '): IString;

    /**
     * @param string $charlist
     * @return IString
     */
    public function rtrim(string $charlist = ' '): IString;

    /**
     * @param string|array $search
     * @param string|array $replace
     * @return IString
     */
    public function replace($search, $replace): IString;

    /**
     * @param string $subject
     * @return IString
     */
    public function prepend(string $subject): IString;

    /**
     * @param string $subject
     * @return IString
     */
    public function append(string $subject): IString;

    /**
     * @param string $subject
     * @param bool $caseInsensitive
     * @return bool
     */
    public function equals(string $subject, bool $caseInsensitive = false): bool;

    /**
     * @param string[] $subject
     * @param bool $caseInsensitive
     * @return bool
     */
    public function equalsAny(array $subject, bool $caseInsensitive = false): bool;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param int $index
     * @return IString
     * @throws RuntimeException
     */
    public function index(int $index): IString;

    /**
     * @param int $start
     * @param int $end
     * @return IString
     */
    public function interval(int $start, int $end): IString;

    /**
     * @param int $index
     * @return bool
     */
    public function has(int $index): bool;

    /**
     * @return IString|null
     */
    public function first(): ?IString;

    /**
     * @return IString|null
     */
    public function last(): ?IString;

    /**
     * @param string $char
     * @return IString
     */
    public function wrap(string $char): IString;

    /**
     * @param string|null $char
     * @return bool
     */
    public function isWrapped(string $char): bool;

    /**
     * @param int $pos
     * @return IString[]
     */
    public function break(int $pos): array;

    /**
     * @param int $pos
     * @param int|null $length
     * @return IString
     */
    public function substring(int $pos, int $length = null): IString;

    /**
     * @param int $length
     * @return IString
     */
    public function start(int $length): IString;

    /**
     * @param int $length
     * @return IString
     */
    public function end(int $length): IString;

    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param int $limit
     * @param int|null $count
     * @return IString
     */
    public function pregReplace($pattern, $replacement, int $limit = -1, int &$count = null): IString;

    /**
     * @param string $pattern
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return bool
     */
    public function match(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0): bool;

    /**
     * @param string $pattern
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return bool
     */
    public function matchAll(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0): bool;

    /**
     * @return IString
     */
    public function revers(): IString;

    /**
     * @return IString
     */
    public function lcFirst(): IString;

    /**
     * @param int $len
     * @return IString
     */
    public function rand(int $len): IString;

    /**
     * Write string
     */
    public function write(): void;

    /**
     * @return int
     */
    public function lastIndex(): ?int;

    /**
     * @return int|null
     */
    public function firstIndex(): ?int;

    /**
     * @return IString
     */
    public function shuffle(): IString;

    /**
     * @return IString
     */
    public function toUpper(): IString;

    /**
     * @return IString
     */
    public function toLower(): IString;

    /**
     * @param int $length
     * @return IString[]
     */
    public function split(int $length = 1): array;

    /**
     * @param mixed ... $args
     * @return IString
     * @throws RuntimeException
     */
    public function format(...$args): IString;

    /**
     * @return bool
     */
    public function isJson(): bool;
}
