<?php

namespace Bermuda\String;

use ArrayAccess;
use Bermuda\Arrayable;
use Countable;
use IteratorAggregate;

interface StringInterface extends IteratorAggregate, ArrayAccess, Countable, Arrayable, Jsonable, Stringable
{

    /**
     * @return _StringInterface
     */
    public function copy(): _StringInterface
    
    /**
     * @param int $pos
     * @param int|null $length
     * @return _StringInterface
     */
    public function slice(int $pos, int $length = null): _StringInterface;

    /**
     * @return string
     */
    public function getEncoding(): string;

    /**
     * @return bool
     */
    public function isMultibyte(): bool;

    /**
     * @param bool|null $mode
     * @return _StringInterface|bool
     */
    public function insensitive(bool $mode = null): _StringInterface|bool;

    /**
     * @param string $encoding
     * @return _StringInterface
     */
    public function encode(string $encoding): _StringInterface;

    /**
     * @param string $delim
     * @param int $limit
     * @return _StringInterface[]
     */
    public function explode(string $delim = '/', int $limit = PHP_INT_MAX): array;

    /**
     * @return _StringInterface
     */
    public function ucfirst(): _StringInterface;

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseInsensitive
     * @return bool
     */
    public function contains(string $needle, int $offset = 0): bool;

    /**
     * @param string $needle
     * @param int $offset
     * @return int|null
     */
    public function indexOf(string $needle, int $offset = 0): ?int;

    /**
     * @param int $length
     * @param string $end
     * @return _StringInterface
     */
    public function truncate(int $length = 200, string $end = '...'): _StringInterface;

    /**
     * @param int $length
     * @return _StringInterface
     */
    public function start(int $length): _StringInterface;

    /**
     * @return int
     */
    public function getBytes(): int;

    /**
     * @param string $needle
     * @param bool $withNeedle
     * @return _StringInterface|null
     */
    public function before(string $needle, bool $withNeedle = true): ?_StringInterface;

    /**
     * @param string $needle
     * @param bool $withNeedle
     * @return _StringInterface|null
     */
    public function after(string $needle, bool $withNeedle = true): ?_StringInterface;

    /**
     * @return int
     */
    public function length(): int;

    /**
     * @param string $algorithm
     * @return string
     */
    public function hash(string $algorithm = 'sha512'): string;

    /**
     * @param string $characters
     * @return _StringInterface
     */
    public function trim(string $characters = " \t\n\r\0\x0B"): _StringInterface;

    /**
     * @param string $characters
     * @return _StringInterface
     */
    public function ltrim(string $characters = " \t\n\r\0\x0B"): _StringInterface;

    /**
     * @param string $characters
     * @return _StringInterface
     */
    public function rtrim(string $characters = " \t\n\r\0\x0B"): _StringInterface;

    /**
     * @param string|string[] $search
     * @param string|string[] $replace
     * @return _StringInterface
     */
    public function replace(string|array $search, string|array $replace): _StringInterface;

    /**
     * @param string $prefix
     * @return _StringInterface
     */
    public function prepend(string $prefix): _StringInterface;

    /**
     * @param string $suffix
     * @return _StringInterface
     */
    public function append(string $suffix): _StringInterface;

    /**
     * @param string[]|string $needle
     * @return bool
     */
    public function equals(array|string $needle): bool;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param string $char
     * @return _StringInterface
     */
    public function wrap(string $char): _StringInterface;

    /**
     * @param string $char
     * @return bool
     */
    public function isWrapped(string $char): bool;

    /**
     * @return _StringInterface|null
     */
    public function first(): ?_StringInterface;

    /**
     * @param int $offset
     * @return _StringInterface
     * @throws RuntimeException
     */
    public function index(int $offset): _StringInterface;

    /**
     * @param string $start
     * @param string $end
     * @return _StringInterface|null
     */
    public function between(string $start, string $end): ?_StringInterface;

    /**
     * @param int $index
     * @return bool
     */
    public function has(int $index): bool;

    /**
     * @return int|null
     */
    public function lastIndex(): ?int;

    /**
     * @return _StringInterface|null
     */
    public function last(): ?_StringInterface;

    /**
     * @param int $pos
     * @return _StringInterface[]
     */
    public function break(int $pos): array;

    /**
     * @param int $length
     * @return _StringInterface
     */
    public function end(int $length): _StringInterface;

    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param int $limit
     * @param int|null $count
     * @return _StringInterface
     */
    public function preplace(string|array $pattern, string|array $replacement, int $limit = -1, int &$count = null): _StringInterface;

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
     * @return _StringInterface
     */
    public function reverse(): _StringInterface;

    /**
     * @return _StringInterface
     */
    public function lcfirst(): _StringInterface;

    /**
     * @param int $length
     * @return _StringInterface
     */
    public function rand(int $length): _StringInterface;

    /**
     * Write string
     */
    public function write(): void;

    /**
     * @return int|null
     */
    public function firstIndex(): ?int;

    /**
     * @return _StringInterface
     */
    public function shuffle(): _StringInterface;

    /**
     * @return _StringInterface
     */
    public function toUpper(): _StringInterface;

    /**
     * @return _StringInterface
     */
    public function toLower(): _StringInterface;

    /**
     * @param string $chars
     * @param int $length
     * @return _StringInterface
     */
    public function pad(string $chars, int $length, int $mode = STR_PAD_BOTH): _StringInterface;

    /**
     * @param int $times
     * @return _StringInterface
     */
    public function repeat(int $times): _StringInterface;

    /**
     * @param int $length
     * @return _StringInterface[]
     */
    public function split(int $length = 1): array;

    /**
     * @param string ...$tokens
     * @return _StringInterface
     */
    public function format(string ...$tokens): _StringInterface;

    /**
     * @return bool
     */
    public function isJson(): bool;
}
