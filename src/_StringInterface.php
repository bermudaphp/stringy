<?php

namespace Bermuda\String;

use ArrayAccess;
use Bermuda\Arrayable;
use Countable;
use IteratorAggregate;
use RuntimeException;

interface _StringInterface extends IteratorAggregate, ArrayAccess, Countable, Arrayable, Jsonable, Stringable
{
    /**
     * @return string
     */
    public function encoding(): string;
    
    public function slice(int $bytes): self;

    /**
     * @param string $encoding
     * @return self
     */
    public function encode(string $encoding): self;

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseInsensitive
     * @return int|null
     */
    public function indexOf(string $needle, int $offset = 0, bool $caseInsensitive = false): ?int;

    /**
     * @return self
     */
    public function copy(): self;

    /**
     * @param string $delim
     * @return self[]|string[]
     */
    public function explode(string $delim = '/', int $limit = PHP_INT_MAX, bool $asString = false): array;

    /**
     * @return self
     */
    public function ucFirst(): self;

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
     * @return self
     */
    public function truncate(int $length = 200, string $substring = '...'): self;

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
     * @return self|null
     */
    public function before(string $needle, bool $requireNeedle = true, bool $caseInsensitive = false):? self;

    /**
     * @param string $needle
     * @param bool $requireNeedle
     * @param bool $caseInsensitive
     * @return self|null
     */
    public function after(string $needle, bool $requireNeedle = true, bool $caseInsensitive = false):? self;

    /**
     * @param string $algorithm
     * @return string
     */
    public function hash(string $algorithm = 'sha512'): string;

    /**
     * @param string $charlist
     * @return Stringy
     */
    public function trim(string $charlist = ' '): self;

    /**
     * @param string $charlist
     * @return self
     */
    public function ltrim(string $charlist = ' '): self;

    /**
     * @param string $charlist
     * @return self
     */
    public function rtrim(string $charlist = ' '): self;

    /**
     * @param string|array $search
     * @param string|array $replace
     * @return self
     */
    public function replace($search, $replace): self;

    /**
     * @param string $subject
     * @return self
     */
    public function prepend(string $subject): self;

    /**
     * @param string $subject
     * @return self
     */
    public function append(string $subject): self;

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
     * @return self
     * @throws RuntimeException
     */
    public function index(int $index): self;

    /**
     * @param int $start
     * @param int $end
     * @return self
     */
    public function interval(int $start, int $end): self;

    /**
     * @param int $index
     * @return bool
     */
    public function has(int $index): bool;

    /**
     * @return self|null
     */
    public function first(): ?self;

    /**
     * @return self|null
     */
    public function last(): ?self;

    /**
     * @param string $char
     * @return self
     */
    public function wrap(string $char): self;

    /**
     * @param string|null $char
     * @return bool
     */
    public function isWrapped(string $char): bool;

    /**
     * @param int $pos
     * @return self[]
     */
    public function break(int $pos): array;

    /**
     * @param int $pos
     * @param int|null $length
     * @return self
     */
    public function substring(int $pos, int $length = null): self;

    /**
     * @param int $length
     * @return self
     */
    public function start(int $length): self;

    /**
     * @param int $length
     * @return self
     */
    public function end(int $length): self;

    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param int $limit
     * @param int|null $count
     * @return self
     */
    public function pregReplace($pattern, $replacement, int $limit = -1, int &$count = null): self;

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
     * @return self
     */
    public function revers(): self;

    /**
     * @return self
     */
    public function lcFirst(): self;

    /**
     * @param int $len
     * @return self
     */
    public function rand(int $len): self;

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
     * @return self
     */
    public function shuffle(): self;

    /**
     * @return self
     */
    public function toUpper(): self;

    /**
     * @return self
     */
    public function toLower(): self;

    /**
     * @param int $length
     * @return self[]
     */
    public function split(int $length = 1): array;

    /**
     * @param mixed ... $args
     * @return self
     * @throws RuntimeException
     */
    public function format(...$args): self;

    /**
     * @return bool
     */
    public function isJson(): bool;
}
