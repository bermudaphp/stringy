<?php

namespace Bermuda\String;

use ArrayAccess;
use Bermuda\Arrayable;
use Countable;
use IteratorAggregate;

interface _String extends Arrayable, IteratorAggregate, ArrayAccess, Countable, Stringable
{
    public const TRIM_LEFT = 1;
    public const TRIM_RIGHT = 2;

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
     * @return _String|bool
     */
    public function insensitive(bool $mode = null): _String|bool;

    /**
     * @return _String
     */
    public function copy(): _String;

    /**
     * @param callable $callback
     * @return _String
     */
    public function transform(callable $callback): _String;

    /**
     * @return _String
     */
    public function underscored(): _String;

    /**
     * @return _String
     */
    public function dasherize(): _String;

    /**
     * @param string $needle
     * @param int $offset
     * @param int|null $length
     * @return int
     */
    public function countSubstring(string $needle, int $offset = 0, int $length = null): int;

    /**
     * @param $delimiter
     * @return _String
     */
    public function delimit($delimiter): _String;

    /**
     * @param string $encoding
     * @return _String
     */
    public function encode(string $encoding): _String;

    /**
     * @param string $delim
     * @param int $limit
     * @return _String[]
     */
    public function explode(string $delim = '/', int $limit = PHP_INT_MAX): array;

    /**
     * @param string|string[] $needle
     * @param int $offset
     * @return bool
     */
    public function contains(string|array $needle, int $offset = 0): bool;

    /**
     * @param string $needle
     * @param int $offset
     * @return int|null
     */
    public function indexOf(string $needle, int $offset = 0): ?int;

    /**
     * @param string|string[] $needle
     * @param int $offset
     * @return bool
     */
    public function containsAll(string|array $needle, int $offset = 0): bool;

    /**
     * @param int $length
     * @param string $end
     * @return _String
     */
    public function truncate(int $length = 200, string $end = '...'): _String;

    /**
     * @param int $length
     * @return _String
     */
    public function start(int $length): _String;

    /**
     * @param int $pos
     * @param int|null $length
     * @return _String
     */
    public function slice(int $pos, int $length = null): _String;

    /**
     * @return int
     */
    public function getBytes(): int;

    /**
     * @param string $needle
     * @param bool $withNeedle
     * @return _String|null
     */
    public function before(string $needle, bool $withNeedle = false): ?_String;

    /**
     * @param string $needle
     * @param bool $withNeedle
     * @return _String|null
     */
    public function after(string $needle, bool $withNeedle = false): ?_String;

    /**
     * @param string $algorithm
     * @return string
     */
    public function hash(string $algorithm = 'sha512'): string;

    /**
     * @param string|string[] $search
     * @param string|string[] $replace
     * @return _String
     */
    public function replace(string|array $search, string|array $replace): _String;

    /**
     * @param string $prefix
     * @return _String
     */
    public function prepend(string $prefix): _String;

    /**
     * @param string $suffix
     * @return _String
     */
    public function append(string $suffix): _String;

    /**
     * @param string $char
     * @return _String
     */
    public function wrap(string $char): _String;

    /**
     * @param string $char
     * @return bool
     */
    public function isWrapped(string $char): bool;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return int|null
     */
    public function lastIndex(): ?int;

    /**
     * @return int
     */
    public function length(): int;

    /**
     * @return _String|null
     */
    public function first(): ?_String;

    /**
     * @param int $offset
     * @return _String
     */
    public function index(int $offset): _String;

    /**
     * @param int $index
     * @return bool
     */
    public function has(int $index): bool;

    /**
     * @param string $start
     * @param string $end
     * @return _String|null
     */
    public function between(string $start, string $end): ?_String;

    /**
     * @return _String|null
     */
    public function last(): ?_String;

    /**
     * @param int $pos
     * @return _String[]
     */
    public function break(int $pos): array;

    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param int $limit
     * @param int|null $count
     * @return _String
     */
    public function pregReplace(string|array $pattern, string|array $replacement, int $limit = -1, int &$count = null): _String;

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
     * @return _String
     */
    public function reverse(): _String;

    /**
     * @return _String
     */
    public function lowerCaseFirst(): _String;

    /**
     * @param int $length
     * @return _String
     */
    public function rand(int $length): _String;

    /**
     * Write string
     */
    public function write(): void;

    /**
     * @return int|null
     */
    public function firstIndex(): ?int;

    /**
     * @return _String
     */
    public function shuffle(): _String;

    /**
     * @return _String
     */
    public function toUpperCase(): _String;

    /**
     * @param string $chars
     * @param int $length
     * @param int $mode
     * @return _String
     */
    public function pad(string $chars, int $length, int $mode = STR_PAD_BOTH): _String;

    /**
     * @param int $times
     * @return _String
     */
    public function repeat(int $times): _String;

    /**
     * @param int $length
     * @return _String[]
     */
    public function split(int $length = 1): array;

    /**
     * @param int $tabLength
     * @return mixed
     */
    public function toSpaces(int $tabLength = 4): _String;

    /**
     * @param string|string[] $needle
     * @return bool
     */
    public function endsWith(string|array $needle): bool;

    /**
     * @param string[]|string $needle
     * @return bool
     */
    public function equals(array|string $needle): bool;

    /**
     * @param int $length
     * @return _String
     */
    public function end(int $length): _String;

    /**
     * @param string $substring
     * @return _String
     */
    public function removeLeft(string $substring): _String;

    /**
     * @param string $characters
     * @param int|null $mode
     * @return _String
     */
    public function trim(string $characters = " \t\n\r\0\x0B", ?int $mode = null): _String;

    /**
     * @param string $substring
     * @return _String
     */
    public function removeRight(string $substring): _String;

    /**
     * @param string|array $needle
     * @return bool
     */
    public function startsWith(string|array $needle): bool;

    /**
     * @return bool
     */
    public function isAlpha(): bool;

    /**
     * @param string $pattern
     * @return bool
     */
    public function mbMatch(string $pattern): bool;

    /**
     * @return bool
     */
    public function isAlphanumeric(): bool;

    /**
     * @return bool
     */
    public function isBlank(): bool;

    /**
     * @return bool
     */
    public function isHexadecimal(): bool;

    /**
     * @return bool
     */
    public function isLowerCase(): bool;

    /**
     * @return bool
     */
    public function isSerialized(): bool;

    /**
     * @return bool
     */
    public function isBase64(): bool;

    /**
     * @return bool
     */
    public function isUpperCase(): bool;

    /**
     * @return _String
     */
    public function stripWhitespace(): _String;

    /**
     * @return _String
     */
    public function swapCase(): _String;

    /**
     * @param string|null $ignore
     * @return _String
     */
    public function titleize(string|array $ignore = null): _String;

    /**
     * @return _String
     */
    public function upperCaseFirst(): _String;

    /**
     * @return _String
     */
    public function toLowerCase(): _String;

    /**
     * @param int $tabLength
     * @return _String
     */
    public function toTabs(int $tabLength = 4): _String;

    /**
     * @return _String
     */
    public function toTitleCase(): _String;

    /**
     * @param int|null $mode
     * @return _String
     */
    public function convertCase(int $mode = null): _String;

    /**
     * @param string ...$tokens
     * @return _String
     */
    public function format(string ...$tokens): _String;

    /**
     * @return bool
     */
    public function isJson(): bool;

    /**
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string;

    /**
     * @param int $start
     * @param int $end
     * @return _String
     */
    public function interval(int $start, int $end): _String;
}
