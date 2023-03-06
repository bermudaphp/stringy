<?php

namespace Bermuda\Stdlib;

class StrWrp implements \Stringable, \Countable
{
    protected bool $multibyte;
    public function __construct(
        protected string  $value = '',
        protected ?string $encoding = null,
    ) {
        if ($encoding === null) {
            $encoding = mb_detect_encoding($this->value, strict: true);
            if ($encoding === false) {
                throw new RuntimeException('Could not determine encoding');
            }
            $this->encoding = $encoding;
        }

        $this->multibyte = StrHelper::isMultibyte($this->value);
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->length();
    }

    /**
     * @param int $pos
     * @param int|null $length
     * @return StrWrp
     */
    public function slice(int $pos, int $length = null): StrWrp
    {
        $copy = clone $this;
        $copy->value = $this->multibyte ?
            mb_substr($this->value, $pos, $length, $this->encoding)
            : substr($this->value, $pos, $length);

        return $copy;
    }

    /**
     * @return StrWrp[]
     */
    public function toArray(): array
    {
        return [$this];
    }

    /**
     * @return \Generator<string>
     */
    public function getIterator(): \Generator
    {
        foreach (str_split($this->value) as $item) yield $item ;
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function each(callable $callback): bool
    {
        $last = $this->lastIndex();
        for ($i = $this->firstIndex(); $last >= $i; $i++) {
            if ($callback($this->value[$i], $i) !== true) {
                return false;
            }
        }
        
        return true;
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
        return StrHelper::isBool($this->value);
    }

    /**
     * @return bool
     */
    public function toBoolean(): bool
    {
        return StrHelper::toBool($this->value);
    }

    /**
     * @return bool
     */
    public function isNumeric(): bool
    {
        return is_numeric($this->value);
    }

    /**
     * @return int|float
     */
    public function toNumber(): int|float
    {
        if (!$this->isNumeric()) {
            return 0;
        }

        return $this->value + 0;
    }

    /**
     * @return bool
     */
    public function isDate(): bool
    {
        return StrHelper::isDate($this->value);
    }

    /**
     * @param \DateTimeZone|null $tz
     * @return \DateTimeInterface
     * @throws \Exception
     */
    public function toDate(\DateTimeZone $tz = null): \DateTimeInterface
    {
        return new \DateTimeImmutable($this->value, $tz);
    }
    
    /**
     * @param string $encoding
     * @return StrWrp
     */
    public function encode(string $encoding): StrWrp
    {
        $copy = clone $this;
        $copy->value = StrHelper::encode($encoding, $this->value);
        $copy->encoding = $encoding;
        
        return $copy;
    }

    /**
     * @param string $delim
     * @param int $limit
     * @return string[]
     */
    public function explode(string $delim = '/', int $limit = PHP_INT_MAX): array
    {
        return explode($delim, $this->value, $limit);
    }

    /**
     * @return StrWrp
     */
    public function upperCaseFirst(): StrWrp
    {
        $copy = clone $this;
        $copy->value = ucfirst($this->value);
        
        return $copy;
    }

    /**
     * @param string|string[] $needle
     * @param bool $ignoreCase
     * @return bool
     */
    public function contains(string|array $needle, bool $ignoreCase = true): bool
    {
        return StrHelper::contains($this->value, $needle, $ignoreCase);
    }

    /**
     * @param string[] $needle
     * @param bool $ignoreCase
     * @return bool
     */
    public function containsAll(array $needle, bool $ignoreCase = true): bool
    {
        return StrHelper::containsAll($this->value, $needle, $ignoreCase);
    }

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $ignoreCase
     * @return int|null
     */
    public function indexOf(string $needle, int $offset = 0, bool $ignoreCase = true): ?int
    {
        if ($ignoreCase) {
            if ($this->multibyte) {
                return @($i = stripos($this->value, $needle, $offset)) !== false ? $i : null;
            }

            return @($i = mb_stripos($this->value, $needle, $offset, $this->encoding)) !== false ? $i : null;
        }

        if ($this->multibyte) {
            return @($i = mb_strpos($this->value, $needle, $offset, $this->encoding)) !== false ? $i : null;
        }

        return @($i = strpos($this->value, $needle, $offset)) !== false ? $i : null;
    }

    /**
     * @param int $length
     * @param string $end
     * @return StrWrp
     */
    public function truncate(int $length = 200, string $end = '...'): StrWrp
    {
        $copy = $this->start($length);
        $copy->value .= $end;
        
        return $copy;
    }

    /**
     * @param int $length
     * @param bool $remove
     * @return StrWrp
     */
    public function start(int $length, bool $remove = false): StrWrp
    {
        if ($remove) {
            return $this->slice(abs($length));
        }

        return $this->slice(0, abs($length));
    }

    /**
     * @return int
     */
    public function getBytes(): int
    {
        return strlen($this->value);
    }

    /**
     * @param string $needle
     * @param bool $withNeedle
     * @return StrWrp|null
     */
    public function before(string $needle, bool $withNeedle = false): ?StrWrp
    {
        if (($index = $this->indexOf($needle)) !== null) {
            return $this->start($withNeedle ? $index + mb_strlen($needle, $this->encoding) : $index);
        }

        return null;
    }
    
    public function copy(): static
    {
        return clone $this;
    }

    /**
     * @param string $needle
     * @param bool $withNeedle
     * @return StrWrp|null
     */
    public function after(string $needle, bool $withNeedle = false): ?StrWrp
    {
        if (($index = $this->indexOf($needle)) !== null) {
            return $this->slice($withNeedle ? $index : $index + mb_strlen($needle, $this->encoding));
        }

        return null;
    }

    /**
     * @return int
     */
    public function length(): int
    {
        return $this->multibyte ? mb_strlen($this->value, $this->encoding) : strlen($this->value);
    }

    /**
     * @param string $algorithm
     * @return string
     */
    public function hash(string $algorithm = 'sha512'): string
    {
        return hash($algorithm, $this->value);
    }

    /**
     * @param string $characters
     * @return StrWrp
     */
    public function trim(string $characters = " \t\n\r\0\x0B"): StrWrp
    {
        $copy = clone $this;
        $copy->value = trim($this->value, $characters);
        
        return $copy;
    }
    
    public function ltrim(string $characters = " \t\n\r\0\x0B"): StrWrp
    {
        $copy = clone $this;
        $copy->value = ltrim($this->value, $characters);

        return $copy;
    }

    public function rtrim(string $characters = " \t\n\r\0\x0B"): StrWrp
    {
        $copy = clone $this;
        $copy->value = rtrim($this->value, $characters);

        return $copy;
    }
    
    /**
     * @param string|string[] $search
     * @param string|string[] $replace
     * @param bool $ignoreCase
     * @return StrWrp
     */
    public function replace(string|array $search, string|array $replace, bool $ignoreCase = true): StrWrp
    {
        $copy = clone $this;
        $copy->value = $ignoreCase ? str_ireplace($search, $replace, $this->value)
            : str_replace($search, $replace, $this->value);

        return $copy;
    }

    /**
     * @param string $prefix
     * @return StrWrp
     */
    public function prepend(string $prefix): StrWrp
    {
        $copy = clone $this;
        $copy->value = $prefix . $this->value;
        
        return $copy;
    }

    /**
     * @param string $suffix
     * @return StrWrp
     */
    public function append(string $suffix): StrWrp
    {
        $copy = clone $this;
        $copy->value .= $suffix;
        
        return $copy;
    }

    /**
     * @param string[]|string $needle
     * @param bool $ignoreCase
     * @return bool
     */
    public function equals(array|string $needle, bool $ignoreCase = true): bool
    {
        return StrHelper::equals($this->value, $needle, $ignoreCase);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    /**
     * @param string $char
     * @return StrWrp
     */
    public function wrap(string $char): StrWrp
    {
        $copy = clone $this;
        $copy->value = $char . $this->value . $char;
        
        return $copy;
    }

    /**
     * @param string $char
     * @param bool $ignoreCase
     * @return bool
     */
    public function isWrapped(string $char, bool $ignoreCase = true): bool
    {
        return StrHelper::isWrapped($this->value, $char, $ignoreCase);
    }

    /**
     * @return StrWrp|null
     */
    public function first(): ?StrWrp
    {
        return $this->index(0);
    }

    /**
     * @param int $offset
     * @return StrWrp
     * @throws RuntimeException
     */
    public function index(int $offset): StrWrp
    {
        if (!$this->has($offset)) {
            throw new RuntimeException('Invalid offset: ' . $offset);
        }

        $copy = clone $this;
        $copy->value = $this->value[$offset];

        return $copy;
    }

    /**
     * @param string $start
     * @param string $end
     * @return StrWrp|null
     */
    public function between(string $start, string $end): ?StrWrp
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
     * @return StrWrp|null
     */
    public function last(): ?StrWrp
    {
        return $this->index($this->lastIndex());
    }

    /**
     * @param int|string $needle
     * @return StrWrp[]
     */
    public function break(int|string $needle): array
    {
        if (!is_int($needle)){
            $needle = $this->indexOf($needle) + StrHelper::length($needle);
        }

        return [$this->start($needle), $this->slice($needle)];
    }

    /**
     * @param int $length
     * @param bool $remove
     * @return StrWrp
     */
    public function end(int $length, bool $remove = false): StrWrp
    {
        if ($remove) {
            return $this->start($this->count() - abs($length));
        }

        return $this->slice(-$length = abs($length), $length);
    }

    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param int $limit
     * @param int|null $count
     * @return StrWrp
     */
    public function pregReplace(string|array $pattern, string|array $replacement, int $limit = -1, int &$count = null): StrWrp
    {
        $copy = clone $this;
        $copy->value = preg_replace($pattern, $replacement, $this->value, $limit, $count);

        return $copy;
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
        return StrHelper::match($this->value, $pattern, $matches, $flags, $offset);
    }
    
    /**
     * @return StrWrp
     */
    public function reverse(): StrWrp
    {
        $copy = clone $this;
        $copy->value = strrev($this->value);

        return $copy;
    }

    /**
     * @return StrWrp
     */
    public function lowerCaseFirst(): StrWrp
    {
        $copy = clone $this;
        $copy->value = lcfirst($this->value);

        return $copy;
    }

    /**
     * @param int $length
     * @return StrWrp
     */
    public function rand(int $length): StrWrp
    {
        $copy = clone $this;
        $copy->value = StrHelper::random($length, $this->value);

        return $copy;
    }

    /**
     * Print string
     */
    public function print(): void
    {
        echo $this->value;
    }

    /**
     * @return int|null
     */
    public function firstIndex(): ?int
    {
        return $this->length() === 0 ? null : 0;
    }

    /**
     * @return StrWrp
     * @throws Exception
     */
    public function shuffle(): StrWrp
    {
        $copy = clone $this;
        $copy->value = StrHelper::shuffle($this->value);

        return $copy;
    }

    /**
     * @param callable $callback
     * @return StrWrp
     */
    public function transform(callable $callback): StrWrp
    {
        $copy = clone $this;
        $copy->value = (string) $callback($this);

        return $copy;
    }

    /**
     * @return StrWrp
     */
    public function underscored(): StrWrp
    {
        return $this->delimit('_');
    }

    /**
     * @param string $delimiter
     * @return StrWrp
     */
    public function delimit(string $delimiter): StrWrp
    {
        $copy = clone $this;

        $old = mb_regex_encoding();
        mb_regex_encoding($this->encoding);

        $copy->value = mb_ereg_replace('\B([A-Z])', '-\1', $this->trim());
        $copy->value = mb_ereg_replace('[-_\s]+', $delimiter, mb_strtolower($copy->value));

        mb_regex_encoding($old);

        return $copy;
    }

    /**
     * @return StrWrp
     */
    public function dasherize(): StrWrp
    {
        return $this->delimit('_');
    }

    /**
     * @param string $needle
     * @param int $offset
     * @param int|null $length
     * @return int
     */
    public function substrCount(string $needle, int $offset = 0, int $length = null): int
    {
        return substr_count($this->value, $needle, $offset, $length);
    }

    /**
     * @return StrWrp
     */
    public function toUpperCase(): StrWrp
    {
        $copy = clone $this;
        $copy->value = mb_convert_case($this->value, CASE_LOWER);

        return $copy;
    }

    /**
     * @return StrWrp
     */
    public function toLowerCase(): StrWrp
    {
        $copy = clone $this;
        $copy->value = mb_convert_case($this->value, CASE_UPPER);

        return $copy;
    }

    /**
     * @param string $chars
     * @param int $length
     * @param int $mode
     * @return StrWrp
     */
    public function pad(string $chars, int $length, int $mode = STR_PAD_BOTH): StrWrp
    {
        $copy = clone $this;
        $copy->value = str_pad($this->value, $length, $chars, $mode);
        
        return $copy;
    }

    /**
     * @param int $times
     * @return StrWrp
     */
    public function repeat(int $times): StrWrp
    {
        $copy = clone $this;
        $copy->value = str_repeat($this->value, abs($times));

        return $copy;
    }

    /**
     * @param int $length
     * @return string[]
     */
    public function split(int $length = 1): array
    {
        if ($length < 1) {
            throw new LogicException('Argument [length] must be larger by zero');
        }

        for ($count = $this->length(); $count > 0; $count -= $length) {
            $chars[] = substr($this->value, -$count, $length);
        }

        return $chars ?? [];
    }

    /**
     * @param string $pattern
     * @return bool
     */
    public function mbMatch(string $pattern): bool
    {
        return StrHelper::mbMatch($pattern, $this->value);
    }

    /**
     * @param int $tabLength
     * @return mixed
     */
    public function toSpaces(int $tabLength = 4): StrWrp
    {
        $copy = clone $this;
        $copy->value = str_replace("\t", str_repeat(' ', abs($tabLength)), $this->value);

        return $copy;
    }

    /**
     * @param string|string[] $needle
     * @return bool
     */
    public function endsWith(string|array $needle): bool
    {
        return StrHelper::endsWith($this->value, $needle);
    }

    /**
     * @param string $substring
     * @return StrWrp
     */
    public function removeLeft(string $substring): StrWrp
    {
        return $this->trim($substring);
    }

    /**
     * @param string $substring
     * @return StrWrp
     */
    public function removeRight(string $substring): StrWrp
    {
        return $this->trim($substring);
    }

    /**
     * @param string|array $needle
     * @return bool
     */
    public function startsWith(string|array $needle): bool
    {
        return StrHelper::startsWith($this->value, $needle);
    }

    /**
     * @return bool
     */
    public function isAlpha(): bool
    {
        return StrHelper::isAlpha($this->value);
    }

    /**
     * @return bool
     */
    public function isAlphanumeric(): bool
    {
        return StrHelper::isAlphanumeric($this->value);
    }

    /**
     * @return bool
     */
    public function isBlank(): bool
    {
        return StrHelper::isBlank($this->value);
    }

    /**
     * @return bool
     */
    public function isHexadecimal(): bool
    {
        return StrHelper::isHexadecimal($this->value);
    }

    /**
     * @return bool
     */
    public function isLowerCase(): bool
    {
        return StrHelper::isLowerCase($this->value);
    }

    /**
     * @return bool
     */
    public function isSerialized(): bool
    {
        return StrHelper::isSerialized($this->value);
    }

    /**
     * @return bool
     */
    public function isBase64(): bool
    {
        return StrHelper::isBase64($this->value);
    }

    /**
     * @return bool
     */
    public function isUpperCase(): bool
    {
        return StrHelper::isUpperCase($this->value);
    }

    /**
     * @return StrWrp
     */
    public function stripWhitespace(): StrWrp
    {
        $copy = clone $this;
        $copy->value = mb_ereg_replace('[[:space:]]+', '', $this->value);

        return $copy;
    }

    /**
     * @return StrWrp
     */
    public function swapCase(): StrWrp
    {
        $copy = clone $this;
        $copy->value = StrHelper::swapCase($this->value);

        return $copy;
    }

    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @param string|array $ignore
     * @return StrWrp
     */
    public function titleize(string|array $ignore = []): StrWrp
    {
        $copy = clone $this;
        $copy->value = StrHelper::titleize($copy->value, $ignore);

        return $copy;
    }

    /**
     * @param int $tabLength
     * @return StrWrp
     */
    public function toTabs(int $tabLength = 4): StrWrp
    {
        $copy = clone $this;
        $copy->value = str_replace(
            str_repeat(' ', $tabLength),
            "\t", $this->value);

        return $copy;
    }

    /**
     * @param string ...$tokens
     * @return StrWrp
     */
    public function format(string ...$tokens): StrWrp
    {
        return new self(sprintf($this->value, ... $tokens));
    }

    /**
     * @return bool
     */
    public function isJson(): bool
    {
        return StrHelper::isJson($this->value);
    }

    /**
     * @param int $options
     * @return string
     * @throws JsonException
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->value, $options | JSON_THROW_ON_ERROR);
    }
}
