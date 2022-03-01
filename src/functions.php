<?php

namespace Bermuda\String;

use Bermuda\Iterator\StringIterator;
use Bermuda\String\StringHelper as Helper;
use ForceUTF8\Encoding;
use LogicException;
use RuntimeException;
use function mb_stripos;
use function mb_strpos;
use function mb_substr;

function _string(string $text = '', ?string $encoding = null, bool $insensitive = false): _String
{
    return new class($text, $encoding, $insensitive) implements _String {
        private bool $multibyte;

        public function __construct(private string  $text = '',
                                    private ?string $encoding = null,
                                    private bool    $insensitive = false)
        {
            if ($encoding === null) {
                $encoding = mb_detect_encoding($text, strict: true);
                if ($encoding === false) {
                    throw new RuntimeException('Could not determine encoding');
                }
                $this->encoding = $encoding;
            }

            $this->multibyte = Helper::isMultibyte($text);
        }

        public function copy(): _String
        {
            return clone $this;
        }

        /**
         * @return string
         */
        public function __toString(): string
        {
            return $this->text;
        }

        /**
         * @param int $pos
         * @param int|null $length
         * @return _String
         */
        public function slice(int $pos, int $length = null): _String
        {
            $copy = $this->copy();
            $copy->text = $this->multibyte ?
                mb_substr($this->text, $pos, $length, $this->encoding)
                : substr($this->text, $pos, $length);

            return $copy;
        }

        /**
         * @return _String[]
         */
        public function toArray(): array
        {
            return [$this];
        }

        /**
         * @return \iterable<_String>
         */
        public function getIterator(): \Generator
        {
            $last = $this->lastIndex();
            for ($i = $this->firstIndex(); $last >= $i; $i++) {
                yield new self($this->text[$i], $this->encoding, $this->insensitive);
            }
        }

        /**
         * @param callable $callback
         * @return void
         */
        public function each(callable $callback): void
        {
            $last = $this->lastIndex();
            for ($i = $this->firstIndex(); $last >= $i; $i++) {
                if ($callback($this->text[$i], $i) === true) {
                    break;
                }
            }
        }

        /**
         * @return string
         */
        public function getEncoding(): string
        {
            return $this->encoding;
        }

        /**
         * @return bool
         */
        public function isMultibyte(): bool
        {
            return $this->multibyte;
        }

        public function isBool(): bool
        {
            return Helper::isBool($this->text);
        }

        /**
         * @return bool
         */
        public function toBoolean(): bool
        {
            if ($this->equals(['on', 'y', 'yes', '1'])) {
                return true;
            }

            return false;
        }

        /**
         * @return bool
         */
        public function isNumeric(): bool
        {
            return is_numeric($this->text);
        }

        /**
         * @return int|float
         */
        public function toNumber(): int|float
        {
            if (!$this->isNumeric()) {
                return 0;
            }

            return $this->text + 0;
        }

        /**
         * @param string $var
         * @return bool
         */
        public function isDate(): bool
        {
            try {
                new \DateTime($var);
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        }

        /**
         * @param \DateTimeZone|null $tz
         * @return \DateTimeInterface
         * @throws \Exception
         */
        public function toDate(\DateTimeZone $tz = null): \DateTimeInterface
        {
            return new \DateTimeImmutable($this->text, $tz);
        }

        /**
         * @param bool|null $mode
         * @return _String|bool
         */
        public function insensitive(bool $mode = null): _String|bool
        {
            if ($mode !== null && $mode !== $this->insensitive) {
                $copy = $this->copy();
                $copy->insensitive = $mode;

                return $copy;
            }

            return $this->insensitive;
        }

        /**
         * @param string $encoding
         * @return _String
         */
        public function encode(string $encoding): _String
        {
            return new self(Helper::encode($encoding, $this->text), $encoding, $this->insensitive);
        }

        /**
         * @param string $delim
         * @param int $limit
         * @return _String[]
         */
        public function explode(string $delim = '/', int $limit = PHP_INT_MAX): array
        {
            $segments = [];

            foreach (explode($delim, $this->text, $limit) as $i => $segment) {
                $segments[$i] = clone $this;
                $segments[$i]->text = $segment;
            }

            return $segments;
        }

        /**
         * @return _String
         */
        public function upperCaseFirst(): _String
        {
            $copy = clone $this;
            $copy->text = ucfirst($copy->text);

            return $copy;
        }

        /**
         * @param string|string[] $needle
         * @param int $offset
         * @return bool
         */
        public function contains(string|array $needle, int $offset = 0): bool
        {
            is_array($needle) ?: $needle = [$needle];
            foreach ($needle as $value) {
                if ($this->indexOf($value, $offset) !== null) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param string[] $needle
         * @param int $offset
         * @return bool
         */
        public function containsAll(array $needle, int $offset = 0): bool
        {
            foreach ($needle as $value) {
                if ($this->indexOf((string)$value, $offset) === null) {
                    return false;
                }
            }

            return true;
        }

        /**
         * @param string $needle
         * @param int $offset
         * @return int|null
         */
        public function indexOf(string $needle, int $offset = 0): ?int
        {
            if ($this->insensitive) {
                if ($this->multibyte) {
                    return @($i = stripos($this->text, $needle, $offset)) !== false ? $i : null;
                }

                return @($i = mb_stripos($this->text, $needle, $offset, $this->encoding)) !== false ? $i : null;
            }

            if ($this->multibyte) {
                return @($i = mb_strpos($this->text, $needle, $offset, $this->encoding)) !== false ? $i : null;
            }

            return @($i = strpos($this->text, $needle, $offset)) !== false ? $i : null;
        }

        /**
         * @param int $length
         * @param string $end
         * @return _String
         */
        public function truncate(int $length = 200, string $end = '...'): _String
        {
            $copy = $this->start($length);
            $copy->text .= $end;

            return $copy;
        }

        /**
         * @param int $length
         * @return _String
         */
        public function start(int $length): _String
        {
            return $this->slice(0, $length);
        }

        /**
         * @return int
         */
        public function getBytes(): int
        {
            return strlen($this->text);
        }

        /**
         * @param string $needle
         * @param bool $withNeedle
         * @return _String|null
         */
        public function before(string $needle, bool $withNeedle = false): ?_String
        {
            if (($index = $this->indexOf($needle, 0)) !== null) {
                return $this->start($withNeedle ? $index + mb_strlen($needle, $this->encoding) : $index);
            }

            return null;
        }

        /**
         * @param string $needle
         * @param bool $withNeedle
         * @return _String|null
         */
        public function after(string $needle, bool $withNeedle = false): ?_String
        {
            if (($index = $this->indexOf($needle, 0)) !== null) {
                return $this->slice($withNeedle ? $index : $index + mb_strlen($needle, $this->encoding));
            }

            return null;
        }

        /**
         * @return int
         */
        public function length(): int
        {
            return $this->multibyte ? mb_strlen($this->text, $this->encoding) : strlen($this->text);
        }

        /**
         * @param string $algorithm
         * @return string
         */
        public function hash(string $algorithm = 'sha512'): string
        {
            return hash($algorithm, $this->text);
        }

        /**
         * @param string $characters
         * @param int|null $mode
         * @return _String
         */
        public function trim(string $characters = " \t\n\r\0\x0B", ?int $mode = null): _String
        {
            $copy = clone $this;
            $copy->text = match ($mode) {
                self::TRIM_LEFT => ltrim($this->text, $characters),
                self::TRIM_RIGHT => rtrim($this->text, $characters),
                default => trim($this->text, $characters)
            };

            return $copy;
        }

        /**
         * @param string|string[] $search
         * @param string|string[] $replace
         * @return _String
         */
        public function replace(string|array $search, string|array $replace): _String
        {
            $text = $this->insensitive ? str_ireplace($search, $replace, $this->text)
                : str_replace($search, $replace, $this->text);

            return new self($text, insensitive: $this->insensitive);
        }

        /**
         * @param string $prefix
         * @return _String
         */
        public function prepend(string $prefix): _String
        {
            return new self($prefix . $this->text, insensitive: $this->insensitive);
        }

        /**
         * @param string $suffix
         * @return _String
         */
        public function append(string $suffix): _String
        {
            return new self($this->text . $suffix, insensitive: $this->insensitive);
        }

        /**
         * @param string[]|string $needle
         * @return bool
         */
        public function equals(array|string $needle): bool
        {
            return str_equals($this->text, $needle, $this->insensitive);
        }

        /**
         * @return bool
         */
        public function isEmpty(): bool
        {
            return empty($this->text);
        }

        /**
         * @param string $char
         * @return _String
         */
        public function wrap(string $char): _String
        {
            return new self($char . $this->text . $char, insensitive: $this->insensitive);
        }

        /**
         * @param string $char
         * @return bool
         */
        public function isWrapped(string $char): bool
        {
            if ($this->isEmpty()) {
                return false;
            }

            return str_equals($this->text[0], $char, $this->insensitive) &&
                str_equals($this->text[$this->lastIndex()], $char, $this->insensitive);
        }

        /**
         * @return _String|null
         */
        public function first(): ?_String
        {
            return $this->index(0);
        }

        /**
         * @param int $offset
         * @return _String
         * @throws RuntimeException
         */
        public function index(int $offset): _String
        {
            if (!$this->has($offset)) {
                throw new RuntimeException('Invalid offset: ' . $offset);
            }

            $copy = clone $this;
            $copy->text = $this->text[$offset];

            return $copy;
        }

        /**
         * @param string $start
         * @param string $end
         * @return _String|null
         */
        public function between(string $start, string $end): ?_String
        {
            if (($index = $this->indexOf($start)) === null) {
                return null;
            }

            $endIndex = ($substring = $this->slice($index + mb_strlen($start)))->indexOf($end);

            if ($endIndex !== null) {
                return $substring->slice(0, $endIndex);
            }

            return $substring;
        }

        /**
         * @param int $index
         * @return bool
         */
        public function has(int $index): bool
        {
            return abs($index) <= $this->lastIndex();
        }

        /**
         * @return int|null
         */
        public function lastIndex(): ?int
        {
            return ($count = $this->length()) !== 0 ? $count - 1 : null;
        }

        /**
         * @return _String|null
         */
        public function last(): ?_String
        {
            return $this->index($this->lastIndex());
        }

        /**
         * @param int|string $needle
         * @return _String[]
         */
        public function break(int|string $needle): array
        {
            if (!is_int($needle)){
                $needle = $this->indexOf($needle) + Helper::length($needle);
            }

            return [$this->start($needle), $this->slice($needle)];
        }

        /**
         * @param int $length
         * @return _String
         */
        public function end(int $length): _String
        {
            return $this->slice(-$length = abs($length), $length);
        }

        /**
         * @param string|string[] $pattern
         * @param string|string[] $replacement
         * @param int $limit
         * @param int|null $count
         * @return _String
         */
        public function pregReplace(string|array $pattern, string|array $replacement, int $limit = -1, int &$count = null): _String
        {
            $result = preg_replace($pattern, $replacement, $this->text, $limit, $count);
            return new self($result, insensitive: $this->insensitive);
        }

        /**
         * @param string $pattern
         * @param array|null $matches
         * @param int $flags
         * @param int $offset
         * @return bool
         */
        public function match(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0): bool
        {
            return str_match($pattern, $this->text, $matches, $flags, $offset) === 1;
        }

        /**
         * @param string $pattern
         * @param array|null $matches
         * @param int $flags
         * @param int $offset
         * @return int|null
         */
        public function matchAll(string $pattern, ?array &$matches = [], int $flags = PREG_PATTERN_ORDER, int $offset = 0):? int
        {
            return str_match_all($pattern, $matches, $flags, $offset);
        }

        /**
         * @return _String
         */
        public function reverse(): _String
        {
            $copy = clone $this;
            $copy->text = strrev($this->text);

            return $copy;
        }

        /**
         * @return _String
         */
        public function lowerCaseFirst(): _String
        {
            $copy = clone $this;
            $copy->text = lcfirst($this->text);

            return $copy;
        }

        /**
         * @param int $length
         * @return _String
         */
        public function rand(int $length): _String
        {
            $copy = clone $this;
            $copy->text = Helper::random($length, $this->text);

            return $copy;
        }

        /**
         * Write string
         */
        public function write(): void
        {
            echo $this->text;
        }

        /**
         * @return int|null
         */
        public function firstIndex(): ?int
        {
            return $this->length() === 0 ? null : 0;
        }

        /**
         * @return _String
         */
        public function shuffle(): _String
        {
            $copy = clone $this;
            $copy->text = Helper::shuffle($this->text);

            return $copy;
        }

        /**
         * @param callable $callback
         * @return _String
         */
        public function transform(callable $callback): _String
        {
            $copy = clone $this;
            $copy->text = (string)$callback($this);

            return $copy;
        }

        /**
         * @return _String
         */
        public function underscored(): _String
        {
            return $this->delimit('_');
        }

        /**
         * @param string $delimiter
         * @return _String
         */
        public function delimit(string $delimiter): _String
        {
            $copy = clone $this;

            $old = mb_regex_encoding();
            mb_regex_encoding($this->encoding);

            $copy->text = mb_ereg_replace('\B([A-Z])', '-\1', $this->trim());
            $copy->text = mb_ereg_replace('[-_\s]+', $delimiter, mb_strtolower($copy->text));

            mb_regex_encoding($old);

            return $copy;
        }

        /**
         * @return _String
         */
        public function dasherize(): _String
        {
            return $this->delimit('_');
        }

        /**
         * @param string $needle
         * @param int $offset
         * @param int|null $length
         * @return int
         */
        public function countSubstring(string $needle, int $offset = 0, int $length = null): int
        {
            return substr_count($this->text, $needle, $offset, $length);
        }

        /**
         * @return _String
         */
        public function toUpperCase(): _String
        {
            $copy = clone $this;
            $copy->text = $this->multibyte ?
                mb_strtoupper($this->text, $this->encoding)
                : strtoupper($this->text);

            return $copy;
        }

        /**
         * @return _String
         */
        public function toLowerCase(): _String
        {
            $copy = clone $this;
            $copy->text = $this->multibyte ?
                mb_strtolower($this->text, $this->encoding)
                : strtolower($this->text);

            return $copy;
        }

        /**
         * @param string $chars
         * @param int $length
         * @param int $mode
         * @return _String
         */
        public function pad(string $chars, int $length, int $mode = STR_PAD_BOTH): _String
        {
            return new self(
                str_pad($this->text, $length, $chars, $mode),
                insensitive: $this->insensitive
            );
        }

        /**
         * @param int $times
         * @return _String
         */
        public function repeat(int $times): _String
        {
            $copy = clone $this;
            $copy->text = str_repeat($this->text, abs($times));

            return $copy;
        }

        /**
         * @param int $length
         * @return _String[]
         */
        public function split(int $length = 1): array
        {
            if ($length < 1) {
                throw new LogicException('Argument [length] must be larger by zero');
            }

            $chars = [];

            for ($count = $this->length(); $count > 0; $count -= $length) {
                $chars[] = $this->slice(-$count, $length);
            }

            return $chars;
        }

        /**
         * @param string $pattern
         * @return bool
         */
        public function mbMatch(string $pattern): bool
        {
            return Helper::mbMatch($pattern, $this->text);
        }

        /**
         * @param int $tabLength
         * @return mixed
         */
        public function toSpaces(int $tabLength = 4): _String
        {
            $copy = clone $this;
            $copy->text = str_replace("\t", str_repeat(' ', abs($tabLength)), $this->text);

            return $copy;
        }

        /**
         * @param string|string[] $needle
         * @return bool
         */
        public function endsWith(string|array $needle): bool
        {
            is_array($needle) ?: $needle = [$needle];

            foreach ($needle as $value) {
                if ($this->end(mb_strlen($value))->equals($value)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param string $substring
         * @return _String
         */
        public function removeLeft(string $substring): _String
        {
            return $this->trim($substring, self::TRIM_LEFT);
        }

        /**
         * @param string $substring
         * @return _String
         */
        public function removeRight(string $substring): _String
        {
            return $this->trim($substring, self::TRIM_RIGHT);
        }

        /**
         * @param string|array $needle
         * @return bool
         */
        public function startsWith(string|array $needle): bool
        {
            is_array($needle) ?: $needle = [$needle];

            foreach ($needle as $value) {
                if ($this->start(mb_strlen($value))->equals($value)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @return bool
         */
        public function isAlpha(): bool
        {
            return Helper::isAlpha($this->text);
        }

        /**
         * @return bool
         */
        public function isAlphanumeric(): bool
        {
            return Helper::isAlphanumeric($this->text);
        }

        /**
         * @return bool
         */
        public function isBlank(): bool
        {
            return Helper::isBlank($this->text);
        }

        /**
         * @return bool
         */
        public function isHexadecimal(): bool
        {
            return Helper::isHexadecimal($this->text);
        }

        /**
         * @return bool
         */
        public function isLowerCase(): bool
        {
            return Helper::isLowerCase($this->text);
        }

        /**
         * @return bool
         */
        public function isSerialized(): bool
        {
            return Helper::isSerialized($this->text);
        }

        /**
         * @return bool
         */
        public function isBase64(): bool
        {
            return Helper::isBase64($this->text);
        }

        /**
         * @return bool
         */
        public function isUpperCase(): bool
        {
            return Helper::isUpperCase($this->text);
        }

        /**
         * @return _String
         */
        public function stripWhitespace(): _String
        {
            $copy = clone $this;
            $copy->text = mb_ereg_replace('[[:space:]]+', '', $this->text);

            return $copy;
        }

        /**
         * @return _String
         */
        public function swapCase(): _String
        {
            $copy = clone $this;
            $copy->text = preg_replace_callback(
                '/[\S]/u',
                static function ($match) use ($copy) {
                    if ($match[0] == mb_strtoupper($match[0], $copy->encoding)) {
                        return mb_strtolower($match[0], $copy->encoding);
                    }

                    return mb_strtoupper($match[0], $copy->encoding);
                },
                $copy->text
            );

            return $copy;
        }

        /**
         * @param string|array|null $ignore
         * @return _String
         */
        public function titleize(string|array $ignore = null): _String
        {
            is_array($ignore) ?: $ignore = [$ignore];

            $copy = clone $this;
            $copy->text = preg_replace_callback(
                '/([\S]+)/u',
                static function ($match) use ($copy, $ignore) {
                    if ($ignore && in_array($match[0], $ignore)) {
                        return $match[0];
                    }

                    return ucfirst(strtolower($match[0]));
                },
                $this->text
            );

            return $copy;
        }

        /**
         * @param int $tabLength
         * @return _String
         */
        public function toTabs(int $tabLength = 4): _String
        {
            $copy = clone $this;
            $copy->text = str_replace(
                str_repeat(' ', $tabLength),
                "\t", $this->text);

            return $copy;
        }

        /**
         * @param int|null $mode
         * @return _String
         */
        public function convertCase(int $mode = null): _String
        {
            $copy = clone $this;
            $copy->text = mb_convert_case($this->text, $mode, $this->encoding);

            return $copy;
        }

        /**
         * @return _String
         */
        public function toTitleCase(): _String
        {
            return $this->convertCase(MB_CASE_TITLE);
        }

        /**
         * @return int
         */
        public function count(): int
        {
            return $this->length();
        }

        /**
         * @param string ...$tokens
         * @return _String
         */
        public function format(string ...$tokens): _String
        {
            return new self(sprintf($this->text, ... $tokens), insensitive: $this->insensitive);
        }

        /**
         * @return bool
         */
        public function isJson(): bool
        {
            return Json::isJson($this->text);
        }

        /**
         * @param int $options
         * @return string
         */
        public function toJson(int $options = 0): string
        {
            return json_encode($this->text, $options | JSON_THROW_ON_ERROR);
        }

        /**
         * Whether a offset exists
         * @link https://php.net/manual/en/arrayaccess.offsetexists.php
         * @param mixed $offset <p>
         * An offset to check for.
         * </p>
         * @return boolean true on success or false on failure.
         * </p>
         * <p>
         * The return value will be casted to boolean if non-boolean was returned.
         * @since 5.0.0
         */
        public function offsetExists($offset): bool
        {
            return (int)$offset > 0 && (int)$offset <= $this->length();
        }

        /**
         * Offset to retrieve
         * @link https://php.net/manual/en/arrayaccess.offsetget.php
         * @param int|string $offset <p>
         * The offset to retrieve.
         * </p>
         * @return mixed Can return all value types.
         * @since 5.0.0
         */
        public function offsetGet($offset): _String
        {
            if (is_string($offset) && str_contains($offset, ':')) {
                list($start, $end) = explode(':', $offset, 2);
                return $this->interval((int)$start, (int)$end);
            }

            return $this->index((int)$offset);
        }

        /**
         * @param int $start
         * @param int $end
         * @return _String
         */
        public function interval(int $start, int $end): _String
        {
            $copy = clone $this;
            $copy->text = Helper::interval($this->text, $start, $end);

            return $copy;
        }

        /**
         * Offset to set
         * @link https://php.net/manual/en/arrayaccess.offsetset.php
         * @param mixed $offset <p>
         * The offset to assign the value to.
         * </p>
         * @param mixed $value <p>
         * The value to set.
         * </p>
         * @return void
         * @since 5.0.0
         */
        public function offsetSet($offset, $value): void
        {
            throw new RuntimeException('Object is immutable');
        }

        /**
         * Offset to unset
         * @link https://php.net/manual/en/arrayaccess.offsetunset.php
         * @param mixed $offset <p>
         * The offset to unset.
         * </p>
         * @return void
         * @since 5.0.0
         */
        public function offsetUnset($offset): void
        {
            throw new RuntimeException('Object is immutable');
        }
    };
}

function _encode(string $encoding, string $text, bool $insensitive = false): _String
{
    return _string($text, insensitive: $insensitive)->encode($encoding);
}

/**
 * @param string $haystack
 * @param string|string[] $needle
 * @param bool $insensitive
 * @return bool
 */
function str_starts_with(string $haystack, string|array $needle, bool $insensitive = false): bool
{
    $callback = $insensitive ? 'strncasecmp' : 'strncmp';
    foreach (is_array($needle) ? $needle : [$needle] as $value) {
        if ($callback($haystack, $value, mb_strlen($value)) === 0) {
            return true;
        }
    }

    return false;
}

/**
 * @param string $haystack
 * @param string|string[] $needle
 * @param bool $insensitive
 * @return bool
 */
function str_ends_with(string $haystack, string|array $needle, bool $insensitive = false): bool
{
    return str_starts_with(strrev($haystack), array_map('strrev', is_array($needle) ? $needle : [$needle]), $insensitive);
}

/**
 * @param string $subject
 * @param int $length
 * @return string
 */
function str_slice(string $subject, int $length): string
{
    return substr($subject, 0, $length);
}

/**
 * @param string $haystack
 * @param string|string[] $needle
 * @param int $offset
 * @param bool $insensitive
 * @return bool
 */
function str_contains(string $haystack, string|array $needle, int $offset = 0, bool $insensitive = false): bool
{
    $callback = $insensitive ? 'stripos' : 'strpos';
    foreach (is_array($needle) ? $needle : [$needle] as $value) {
        if ($callback($haystack, $value, $offset) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * @param string $haystack
 * @param string[] $needle
 * @param int $offset
 * @param bool $insensitive
 * @return bool
 */
function str_contains_all(string $haystack, array $needle, int $offset = 0, bool $insensitive = false): bool
{
    $callback = $insensitive ? 'stripos' : 'strpos';
    foreach ($needle as $value) {
        if ($callback($haystack, (string) $value, $offset) === false) {
            return false;
        }
    }

    return true;
}

/**
 * @param string $pattern
 * @param string $subject
 * @param array|null $matches
 * @param int $flags
 * @param int $offset
 * @return bool
 */
function str_match(string $pattern, string $subject, array &$matches = null, int $flags = 0, int $offset = 0): bool
{
    $result = @preg_match($pattern, $subject, $matches, $flags, $offset) === 1;

    if (error_get_last() !== null) {

        if (error_get_last()['message'] === 'preg_match(): Delimiter must not be alphanumeric or backslash') {
            return @preg_match('~'.$pattern.'~', $subject, $matches, $flags, $offset) === 1;
        }

        throw new RuntimeException(error_get_last()['message']);
    }

    return $result;
}

/**
 * @param string[] $patterns
 * @param string $subject
 * @param array|null $matches
 * @param int $flags
 * @param int $offset
 * @return bool
 */
function str_match_any(array $patterns, string $subject, array &$matches = null, int $flags = 0, int $offset = 0): bool
{
    foreach ($patterns as $pattern) if (str_match($pattern, $subject, $matches, $flags, $offset)) return true;
    return false;
}


/**
 * @param string $pattern
 * @param string $subject
 * @param array|null $matches
 * @param int $flags
 * @param int $offset
 * @return int|null
 */
function str_match_all(string $pattern, string $subject, array &$matches = null, int $flags = PREG_PATTERN_ORDER, int $offset = 0):? int
{
    $result = @preg_match_all($pattern, $subject, $matches, $flags, $offset);

    if (($error = error_get_last()) !== null) {
        throw new RuntimeException($error['message']);
    }

    return $result;
}

/**
 * @param string $haystack
 * @param string $needle
 * @param bool $withNeedle
 * @return string|null
 */
function str_before(string $haystack, string $needle, bool $withNeedle = false):? string
{
    $index = strpos($haystack, $needle);

    if ($index === false){
        return null;
    }

    return str_slice($haystack, $withNeedle ? $index + mb_strlen($needle) : $index);
}

/**
 * @param string $haystack
 * @param string $needle
 * @param bool $withNeedle
 * @return string|null
 */
function str_after(string $haystack, string $needle, bool $withNeedle = false):? string
{
    $index = strpos($haystack, $needle);

    if ($index === false){
        return null;
    }

    return substr($haystack, !$withNeedle ? $index + mb_strlen($needle) : $index);
}

/**
 * @param string $subject
 * @param string|array $any
 * @param bool $insensitive
 * @return bool
 */
function str_equals(string $subject, string|array $any, bool $insensitive = true): bool
{
    if ($insensitive) {
        $subject = strtolower($subject);
    }

    foreach (is_array($any) ? $any : [$any] as $value) {
        $result = $insensitive ? $subject === strtolower((string)$value)
            : $subject === (string)$value;

        if ($result) {
            return true;
        }
    }

    return false;
}
