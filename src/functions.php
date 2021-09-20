<?php

namespace Bermuda\String;

use Bermuda\Iterator\StringIterator;
use ForceUTF8\Encoding;
use LogicException;
use RuntimeException;
use Traversable;
use function mb_stripos;
use function mb_strpos;
use function mb_substr;

function _string(string $text, ?string $encoding = null, bool $insensitive = false): _StringInterface
{
    return new class($text, $encoding, $insensitive) implements _StringInterface {
        private bool $multibyte = false;

        public function __construct(private string  $text = '',
                                    private ?string $encoding = null,
                                    private bool    $insensitive = false)
        {
            if ($encoding === null) {
                $this->encoding = (new EncodingDetector)->detectEncoding($text);
            }

            $this->multibyte = _String::isMultibyte($text);
            $this->text = Encoding::encode($this->encoding, $text);
        }

        public function copy(): _StringInterface
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
         * @return _StringInterface
         */
        public function slice(int $pos, int $length = null): _StringInterface
        {
            $copy = $this->copy();
            $copy->text = $this->multibyte ?
                mb_substr($this->text, $pos, $length)
                : substr($this->text, $pos, $length);

            return $copy;
        }

        /**
         * @return array
         */
        public function toArray(): array
        {
            return [$this];
        }

        /**
         * Retrieve an external iterator
         * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
         * @return Traversable An instance of an object implementing <b>Iterator</b> or
         * <b>Traversable</b>
         * @since 5.0.0
         */
        public function getIterator(): StringIterator
        {
            return new StringIterator($this->subject);
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

        /**
         * @param bool|null $mode
         * @return _StringInterface|bool
         */
        public function insensitive(bool $mode = null): _StringInterface|bool
        {
            if ($mode !== null && $mode !== $this->insensitive) {
                $copy = $this->copy();
                $copy->insensitive = $mode;
            }

            return $this->insensitive;
        }

        /**
         * @param string $encoding
         * @return _StringInterface
         */
        public function encode(string $encoding): _StringInterface
        {
            return new self($this->text, $encoding, null, $this->insensitive);
        }

        /**
         * @param string $delim
         * @param int $limit
         * @return _StringInterface[]
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
         * @return _StringInterface
         */
        public function ucfirst(): _StringInterface
        {
            $copy = clone $this;
            $copy->text = ucfirst($copy->text);

            return $copy;
        }

        /**
         * @param string $needle
         * @param int $offset
         * @param bool $caseInsensitive
         * @return bool
         */
        public function contains(string $needle, int $offset = 0): bool
        {
            return $this->indexOf($needle, $offset) !== null;
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

                return @($i = mb_stripos($this->text, $needle, $offset)) !== false ? $i : null;
            }

            if ($this->multibyte) {
                return @($i = mb_strpos($this->text, $needle, $offset)) !== false ? $i : null;
            }

            return @($i = strpos($this->text, $needle, $offset)) !== false ? $i : null;
        }

        /**
         * @param int $length
         * @param string $end
         * @return _StringInterface
         */
        public function truncate(int $length = 200, string $end = '...'): _StringInterface
        {
            $copy = $this->start($length);
            $copy->text .= $end;

            return $copy;
        }

        /**
         * @param int $length
         * @return _StringInterface
         */
        public function start(int $length): _StringInterface
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
         * @return _StringInterface|null
         */
        public function before(string $needle, bool $withNeedle = true): ?_StringInterface
        {
            if (($index = $this->indexOf($needle, 0)) !== null) {
                return $this->start($withNeedle ? $index + 1 : $index);
            }

            return null;
        }

        /**
         * @param string $needle
         * @param bool $withNeedle
         * @return _StringInterface|null
         */
        public function after(string $needle, bool $withNeedle = true): ?_StringInterface
        {
            if (($index = $this->indexOf($needle, 0)) !== null) {
                return $this->slice($withNeedle ? $index : $index + mb_strlen($needle));
            }

            return null;
        }

        /**
         * @return int
         */
        public function length(): int
        {
            if ($this->multibyte) {
                return mb_strlen($this->text);
            }

            return strlen($this->text);
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
         * @return _StringInterface
         */
        public function trim(string $characters = " \t\n\r\0\x0B", ?int $mode = null): _StringInterface
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
         * @return _StringInterface
         */
        public function replace(string|array $search, string|array $replace): _StringInterface
        {
            $text = $this->insensitive ? str_ireplace($search, $replace, $this->text)
                : str_replace($search, $replace, $this->text);

            return new self($text, insensitive: $this->insensitive);
        }

        /**
         * @param string $prefix
         * @return _StringInterface
         */
        public function prepend(string $prefix): _StringInterface
        {
            return new self($prefix . $this->text, insensitive: $this->insensitive);
        }

        /**
         * @param string $suffix
         * @return _StringInterface
         */
        public function append(string $suffix): _StringInterface
        {
            return new self($this->text . $suffix, insensitive: $this->insensitive);
        }

        /**
         * @param string[]|string $needle
         * @return bool
         */
        public function equals(array|string $needle): bool
        {
            return StringHelper::equals($this->text, $needle, $this->insensitive);
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
         * @return _StringInterface
         */
        public function wrap(string $char): _StringInterface
        {
            return new self($char . $this->text . $suffix, insensitive: $this->insensitive);
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

            return StreingHelper::equals($this->text[0], $char, $this->insensitive) &&
                StreingHelper::equals($this->text[$this->lastIndex()], $char, $this->insensitive);
        }

        /**
         * @return _StringInterface|null
         */
        public function first(): ?_StringInterface
        {
            return $this->index(0);
        }

        /**
         * @param int $offset
         * @return _StringInterface
         * @throws RuntimeException
         */
        public function index(int $offset): _StringInterface
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
         * @return _StringInterface|null
         */
        public function between(string $start, string $end): ?_StringInterface
        {
            if (($index = $this->indexOf($start)) === null) {
                return null;
            }

            return $this->slice($start, abs((int)$this->indexOf($end) - $index));
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
         * @return _StringInterface|null
         */
        public function last(): ?_StringInterface
        {
            return $this->index($this->lastIndex());
        }

        /**
         * @param int $pos
         * @return _StringInterface[]
         */
        public function break(int $pos): array
        {
            return [$this->start($pos), $this->slice($pos)];
        }

        /**
         * @param int $length
         * @return _StringInterface
         */
        public function end(int $length): _StringInterface
        {
            return $this->slice(-$length = abs($length), $length);
        }

        /**
         * @param string|string[] $pattern
         * @param string|string[] $replacement
         * @param int $limit
         * @param int|null $count
         * @return _StringInterface
         */
        public function preplace(string|array $pattern, string|array $replacement, int $limit = -1, int &$count = null): _StringInterface
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
            $matched = @preg_match($pattern, $this->subject, $matches, $flags, $offset) === 1;

            if ($error = error_get_last()) {
                throw new RuntimeException($error['message']);
            }

            return $matched;
        }

        /**
         * @param string $pattern
         * @param array|null $matches
         * @param int $flags
         * @param int $offset
         * @return bool
         */
        public function matchAll(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0): bool
        {
            $matched = @preg_match_all($pattern, $this->subject, $matches, $flags, $offset) === 1;

            if ($error = error_get_last()) {
                throw new RuntimeException($error['message']);
            }

            return $matched;
        }

        /**
         * @return _StringInterface
         */
        public function reverse(): _StringInterface
        {
            $copy = clone $this;
            $copy->text = strrev($this->text);

            return $copy;
        }

        /**
         * @return _StringInterface
         */
        public function lcfirst(): _StringInterface
        {
            $copy = clone $this;
            $copy->text = lcfirst($this->subject);

            return $copy;
        }

        /**
         * @param int $length
         * @return _StringInterface
         */
        public function rand(int $length): _StringInterface
        {
            $copy = clone $this;
            $copy->text = StringHelper::random($length, $this->text);

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
         * @return _StringInterface
         */
        public function shuffle(): _StringInterface
        {
            $copy = clone $this;
            $copy->text = StringHelper::shuffle($this->text);

            return $copy;
        }

        /**
         * @return _StringInterface
         */
        public function toUpper(): _StringInterface
        {
            $copy = clone $this;
            $copy->text = strtoupper($this->text);

            return $copy;
        }

        /**
         * @return _StringInterface
         */
        public function toLower(): _StringInterface
        {
            $copy = clone $this;
            $copy->text = strtolower($this->text);

            return $copy;
        }

        /**
         * @param string $chars
         * @param int $length
         * @return _StringInterface
         */
        public function pad(string $chars, int $length, int $mode = STR_PAD_BOTH): _StringInterface
        {
            $text = str_pad($this->text, $length, $chars, $mode);
            return new self($text, insensitive: $this->insensitive);
        }

        /**
         * @param int $times
         * @return _StringInterface
         */
        public function repeat(int $times): _StringInterface
        {
            $copy = clone $this;
            $copy->text = str_repeat($this->text, $times);

            return $copy;
        }

        /**
         * @param int $length
         * @return _StringInterface[]
         */
        public function split(int $length = 1): array
        {
            if ($length < 1) {
                throw new LogicException('Argument [length] must be larger by zero');
            }

            $split = [];

            for ($count = $this->length(); $count > 0; $count -= $length) {
                $split[] = $this->slice(-$count, $length);
            }

            return $split;
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
         * @return _StringInterface
         */
        public function format(string ...$tokens): _StringInterface
        {
            return new self(sprintf($this->text, ... $tokens), insensitive: $this->insensitive);
        }

        /**
         * @return bool
         */
        public function isJson(): bool
        {
            return StringHelper::isJson($this->text);
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
        public function offsetGet($offset): _StringInterface
        {
            if (is_string($offset) && mb_strpos($offset, ':') !== false) {
                list($start, $end) = explode(':', $offset, 2);
                return $this->interval((int)$start, (int)$end);
            }

            return $this->index((int)$offset);
        }

        /**
         * @param int $start
         * @param int $end
         * @return _StringInterface
         */
        public function interval(int $start, int $end): _StringInterface
        {
            $copy = clone $this;
            $copy->text = StringHelper::interval($this->text, $start, $end);

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

/**
 * @param string $haystack
 * @param string $needle
 * @param bool $insensitive
 * @param int $offset
 * @return bool
 */
function str_contains(string $haystack, string $needle, bool $insensitive = false, int $offset = 0): bool
{
    return Str::contains($haystack, $needle, $insensitive, $offset);
}


