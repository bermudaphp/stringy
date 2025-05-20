<?php

declare(strict_types=1);

namespace Bermuda\Stdlib;

use ArrayIterator;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use Stringable;
use Traversable;
use RuntimeException;

/**
 * Base trait for string classes
 *
 * Provides common implementation for string manipulation methods.
 * Used by both immutable and mutable string classes to avoid code duplication.
 */
trait BaseString
{
    protected(set) string $value;
    protected(set) string $encoding;
    protected(set) bool $isMultibyte;

    /**
     * Create a lazy string instance
     *
     * @param callable(StringInterface $object): void $initializer Function that returns the string value
     * @return StringInterface Lazy-initialized string instance
     *
     * For example
     * $str = Str::createLazy(static function (Str $str) {
     *     $str->__construct(hash('sha256', 'The quick brown fox jumped over the lazy dog.'));
     * })
     */
    public static function createLazy(callable $initializer): StringInterface
    {
        $reflector = new \ReflectionClass(static::class);
        return $reflector->newLazyGhost($initializer);
    }

    /**
     * Constructor
     *
     * @param string|Stringable $string Initial string value
     * @param string $encoding Character encoding to use
     * @throws InvalidArgumentException If the encoding is not supported
     */
    public function __construct(string|Stringable $string, ?string $encoding = null)
    {
        $this->value = (string) $string;
        $this->encoding = $encoding;
        // Determine if the string is multibyte
        $this->isMultibyte = Stringy::isMultibyte($this->value);
    }

    /**
     * Method for converting object to string
     *
     * @return string Current string value
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Get string representation
     *
     * @return string Current string value
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Get a copy of the object
     *
     * @return StringInterface New instance with the same value and encoding
     */
    public function copy(): StringInterface
    {
        return clone $this;
    }

    /**
     * Get the number of bytes in the string
     *
     * @return int Number of bytes in the string
     */
    public function getBytes(): int
    {
        return strlen($this->value);
    }

    /**
     * Check if the string starts with the specified substring
     *
     * @param string|array<string> $substring Substring or array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string starts with any of the specified substrings
     */
    public function startsWith(string|array $substring, bool $caseSensitive = true): bool
    {
        return Stringy::startsWith($this->value, $substring, $caseSensitive);
    }

    /**
     * Check if the string ends with the specified substring
     *
     * @param string|array<string> $substring Substring or array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string ends with any of the specified substrings
     */
    public function endsWith(string|array $substring, bool $caseSensitive = true): bool
    {
        return Stringy::endsWith($this->value, $substring, $caseSensitive);
    }

    /**
     * Check if the string contains the specified substring
     *
     * @param string|array<string> $substring Substring or array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string contains any of the specified substrings
     */
    public function contains(string|array $substring, bool $caseSensitive = true): bool
    {
        return Stringy::contains($this->value, $substring, $caseSensitive);
    }

    /**
     * Check if the string contains all the specified substrings
     *
     * @param array<string> $substrings Array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string contains all the specified substrings
     */
    public function containsAll(array $substrings, bool $caseSensitive = true): bool
    {
        return Stringy::containsAll($this->value, $substrings, $caseSensitive);
    }

    /**
     * Compare with one of the passed strings
     *
     * @param array<string|Stringable> $values Array of strings to compare with
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string equals any of the specified values
     */
    public function equalsAny(array $values, bool $caseSensitive = true): bool
    {
        return Stringy::equalsAny($this->value, $values, $caseSensitive);
    }

    /**
     * Get the length of the string
     *
     * @return int Number of characters in the string
     */
    public function length(): int
    {
        if ($this->isMultibyte) {
            return mb_strlen($this->value, $this->encoding);
        }

        return strlen($this->value);
    }

    /**
     * Get the character at the specified index
     *
     * @param int $index Position of the character (negative values count from the end)
     * @return string|null The character at the specified position or null if the index is out of bounds
     */
    public function charAt(int $index): ?string
    {
        $length = $this->length();

        if ($index < 0) {
            $index = $length + $index;
        }

        if ($index < 0 || $index >= $length) {
            return null;
        }

        if ($this->isMultibyte) {
            return mb_substr($this->value, $index, 1, $this->encoding);
        }

        return substr($this->value, $index, 1);
    }

    /**
     * Check if the character exists at the specified index
     *
     * @param int $index Position to check (negative values count from the end)
     * @return bool True if the index is valid for this string
     */
    public function has(int $index): bool
    {
        $lastIndex = $this->lastIndex();
        return $lastIndex !== null && abs($index) <= $lastIndex;
    }

    /**
     * Get the index of the first character
     *
     * @return int|null 0 if the string is not empty, null otherwise
     */
    public function firstIndex(): ?int
    {
        return $this->length() !== 0 ? 0 : null;
    }

    /**
     * Get the index of the last character
     *
     * @return int|null Index of the last character or null if the string is empty
     */
    public function lastIndex(): ?int
    {
        $count = $this->length();
        return $count !== 0 ? $count - 1 : null;
    }

    /**
     * Find the position of the first occurrence of a substring
     *
     * @param string $substring Substring to find
     * @param int $offset Start position for the search
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return int|null Position of the first occurrence or null if not found
     */
    public function indexOf(string $substring, int $offset = 0, bool $caseSensitive = true): ?int
    {
        return Stringy::indexOf($this->value, $substring, $offset, $caseSensitive, $this->encoding);
    }

    /**
     * Find the position of the last occurrence of a substring
     *
     * @param string $substring Substring to find
     * @param int $offset Start position for the search
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return int|null Position of the last occurrence or null if not found
     */
    public function lastIndexOf(string $substring, int $offset = 0, bool $caseSensitive = true): ?int
    {
        return Stringy::lastIndexOf($this->value, $substring, $offset, $caseSensitive, $this->encoding);
    }

    /**
     * Count the number of occurrences of a substring
     *
     * @param string $substring Substring to count
     * @param bool $caseSensitive Whether to perform case-sensitive counting
     * @return int Number of occurrences
     */
    public function countSubstr(string $substring, bool $caseSensitive = true): int
    {
        return Stringy::countSubstring($this->value, $substring, $caseSensitive, $this->encoding);
    }

    /**
     * Get an iterator for the string
     *
     * @return StringIterator Iterator for the string characters
     */
    public function getIterator(): StringIterator
    {
        return new StringIterator($this->value);
    }

    /**
     * Get the number of characters in the string
     *
     * @return int Number of characters in the string
     */
    public function count(): int
    {
        return $this->length();
    }

    /**
     * Get an array of characters in the string
     *
     * @return array<string> Array of individual characters
     */
    public function toArray(): array
    {
        if ($this->value === '') {
            return [];
        }

        return preg_split('//u', $this->value, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Check if the string is empty
     *
     * @return bool True if the string is empty
     */
    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    /**
     * Check if the string is empty or contains only whitespace characters
     *
     * @return bool True if the string is empty or contains only whitespace
     */
    public function isBlank(): bool
    {
        return Stringy::isBlank($this->value);
    }

    /**
     * Check if the string contains only letters
     *
     * @return bool True if the string contains only letters
     */
    public function isAlpha(): bool
    {
        return Stringy::isAlpha($this->value);
    }

    /**
     * Check if the string contains only digits
     *
     * @return bool True if the string contains only digits
     */
    public function isNumeric(): bool
    {
        return is_numeric($this->value);
    }

    /**
     * Convert the string to a number
     *
     * @return int|float Numeric representation of the string
     */
    public function toNumber(): int|float
    {
        if (!$this->isNumeric()) {
            return 0;
        }

        return $this->value + 0;
    }

    /**
     * Check if the string contains only letters and digits
     *
     * @return bool True if the string contains only letters and digits
     */
    public function isAlphanumeric(): bool
    {
        return Stringy::isAlphanumeric($this->value);
    }

    /**
     * Check if the string is a hexadecimal number
     *
     * @return bool True if the string is a valid hexadecimal number
     */
    public function isHex(): bool
    {
        return Stringy::isHex($this->value);
    }

    /**
     * Check if the string is serialized data
     *
     * @return bool True if the string is serialized data
     */
    public function isSerialized(): bool
    {
        return Stringy::isSerialized($this->value);
    }

    /**
     * Check if the string is Base64 encoded
     *
     * @return bool True if the string is Base64 encoded
     */
    public function isBase64(): bool
    {
        return Stringy::isBase64($this->value);
    }

    /**
     * Check if the string is JSON
     *
     * @return bool True if the string is valid JSON
     */
    public function isJson(): bool
    {
        return Stringy::isJson($this->value);
    }

    /**
     * Get the string as JSON format
     *
     * @param int $options JSON encoding options
     * @return string JSON representation of the string
     * @throws \JsonException If JSON encoding fails
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->value, $options | JSON_THROW_ON_ERROR);
    }

    /**
     * Check if the string represents a boolean value
     *
     * @return bool True if the string represents a boolean value
     */
    public function isBoolean(): bool
    {
        return Stringy::isBoolean($this->value);
    }

    /**
     * Convert the string to a boolean value
     *
     * @return bool|null Boolean value or null if the string does not represent a boolean
     */
    public function toBoolean(): ?bool
    {
        return Stringy::toBoolean($this->value);
    }

    /**
     * Check if the string is a date
     *
     * @return bool True if the string is a valid date
     */
    public function isDate(): bool
    {
        return Stringy::isDate($this->value);
    }

    /**
     * Convert the string to a date
     *
     * @param DateTimeZone|null $timezone Timezone to use for date parsing
     * @return DateTimeInterface|null DateTime object or null if the string is not a valid date
     */
    public function toDate(?DateTimeZone $timezone = null): ?DateTimeInterface
    {
        try {
            return new DateTimeImmutable($this->value, $timezone);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Check if the string contains only uppercase characters
     *
     * @return bool True if the string contains only uppercase characters
     */
    public function isUpperCase(): bool
    {
        return Stringy::isUpperCase($this->value);
    }

    /**
     * Check if the string contains only lowercase characters
     *
     * @return bool True if the string contains only lowercase characters
     */
    public function isLowerCase(): bool
    {
        return Stringy::isLowerCase($this->value);
    }

    /**
     * Check if the string contains lowercase characters
     *
     * @return bool True if the string contains at least one lowercase character
     */
    public function hasLowerCase(): bool
    {
        return preg_match('/[a-z\p{Ll}]/u', $this->value) === 1;
    }

    /**
     * Check if the string contains digits
     *
     * @return bool True if the string contains at least one digit
     */
    public function hasDigits(): bool
    {
        return preg_match('/[0-9\p{N}]/u', $this->value) === 1;
    }

    /**
     * Check if the string contains uppercase characters
     *
     * @return bool True if the string contains at least one uppercase character
     */
    public function hasUpperCase(): bool
    {
        return preg_match('/[A-Z\p{Lu}]/u', $this->value) === 1;
    }

    /**
     * Check if the string contains symbol characters (punctuation, math, currency, etc.)
     *
     * @return bool True if the string contains at least one symbol character
     */
    public function hasSymbols(): bool
    {
        return preg_match('/[\p{P}\p{S}]/u', $this->value) === 1;
    }

    /**
     * Split the string into an array of lines
     *
     * @return array<string|static> Array of lines
     */
    public function lines(): array
    {
        if ($this->value === '') {
            return [];
        }

        // Split by common line separators (LF, CRLF, CR)
        $lines = preg_split('/\r\n|\n|\r/u', $this->value);

        // If a static method is called, we return an array of strings
        // If an instance method is called, we return an array of instances
        if (static::class === __CLASS__) {
            return $lines;
        }

        return array_map(fn($line) => new static($line, $this->encoding), $lines);
    }

    /**
     * Split the string into an array of words
     *
     * @return array<string|static> Array of words
     */
    public function words(): array
    {
        if ($this->value === '') {
            return [];
        }

        // Split by whitespace
        $words = preg_split('/\s+/u', trim($this->value));

        // Filter out empty values
        $words = array_filter($words, fn($word) => $word !== '');

        // If a static method is called, we return an array of strings
        // If an instance method is called, we return an array of instances
        if (static::class === __CLASS__) {
            return $words;
        }

        return array_map(fn($word) => new static($word, $this->encoding), $words);
    }

    /**
     * Check if the string matches a regular expression
     *
     * @param string $pattern Regular expression pattern
     * @param array<mixed>|null $matches If provided, will be filled with the matches
     * @param int $flags Regular expression flags
     * @param int $offset Start position for matching
     * @return bool True if the pattern matches the string
     * @throws RuntimeException If an error occurs during pattern matching
     */
    public function match(string $pattern, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        if ($this->isMultibyte) {
            return Stringy::match($this->value, $pattern, $matches, $flags, $offset, $this->encoding);
        }

        $result = @preg_match($pattern, $this->value, $matches, $flags, $offset);

        if ($result === false) {
            throw new RuntimeException('Error in regular expression: ' . preg_last_error_msg());
        }

        return $result === 1;
    }

    /**
     * Check if the string matches a regular expression multiple times
     *
     * @param string $pattern Regular expression pattern
     * @param array<mixed>|null $matches If provided, will be filled with the matches
     * @param int $flags Regular expression flags
     * @param int $offset Start position for matching
     * @return bool True if the pattern matches the string
     * @throws RuntimeException If an error occurs during pattern matching
     */
    public function matchAll(string $pattern, ?array &$matches = null, int $flags = PREG_PATTERN_ORDER, int $offset = 0): bool
    {
        if ($this->isMultibyte) {
            return Stringy::matchAll($this->value, $pattern, $matches, $flags, $offset, $this->encoding);
        }

        $result = @preg_match_all($pattern, $this->value, $matches, $flags, $offset);

        if ($result === false) {
            throw new RuntimeException('Error in regular expression: ' . preg_last_error_msg());
        }

        return $result > 0;
    }

    /**
     * Check if the string is wrapped with the specified character
     *
     * @param string $char Character to check for wrapping
     * @param bool $caseSensitive Whether to perform case-sensitive check
     * @return bool True if the string is wrapped with the specified character
     */
    public function isWrapped(string $char, bool $caseSensitive = false): bool
    {
        if ($this->value === '') {
            return false;
        }

        $first = $this->charAt(0);
        $last = $this->charAt($this->lastIndex() ?? 0);

        if (!$caseSensitive) {
            $first = mb_strtolower($first ?? '', $this->encoding);
            $last = mb_strtolower($last ?? '', $this->encoding);
            $char = mb_strtolower($char, $this->encoding);
        }

        return $first === $char && $last === $char;
    }

    /**
     * Get a hash of the string
     *
     * @param string $algorithm Hashing algorithm to use
     * @return string Hash of the string
     */
    public function hash(string $algorithm = 'sha256'): string
    {
        return Stringy::hash($this->value, $algorithm);
    }

    /**
     * Compare with another string
     *
     * @param string|Stringable $other String to compare with
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the strings are equal
     */
    public function equals(string|Stringable $other, bool $caseSensitive = true): bool
    {
        $otherStr = (string) $other;

        if ($caseSensitive) {
            return $this->value === $otherStr;
        }

        if ($this->isMultibyte) {
            return mb_strtolower($this->value, $this->encoding) === mb_strtolower($otherStr, $this->encoding);
        }

        return strtolower($this->value) === strtolower($otherStr);
    }

    /**
     * Output the string
     *
     * @return void
     */
    public function print(): void
    {
        echo $this->value;
    }

    /**
     * Get the beginning of the string
     *
     * @param int $length Number of characters to get from the beginning
     * @return static A new string object containing the first $length characters
     */
    public function start(int $length): StringInterface
    {
        return $this->substring(0, abs($length));
    }

    /**
     * Get the end of the string
     *
     * @param int $length Number of characters to get from the end
     * @return static A new string object containing the last $length characters
     */
    public function end(int $length): StringInterface
    {
        $length = abs($length);
        return $this->substring(-$length, $length);
    }

    /**
     * Remove the specified number of characters from the beginning of the string
     *
     * @param int $length Number of characters to remove
     * @return static A new string object with characters removed from the beginning
     */
    public function removeStart(int $length): StringInterface
    {
        return $this->substring(abs($length));
    }

    /**
     * Remove the specified number of characters from the end of the string
     *
     * @param int $length Number of characters to remove
     * @return static A new string object with characters removed from the end
     */
    public function removeEnd(int $length): StringInterface
    {
        return $this->substring(0, $this->length() - abs($length));
    }

    /**
     * Replace the first occurrence of a substring with a new substring
     *
     * @param string $search String to search for
     * @param string $replace String to replace with
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return static New string with the first occurrence replaced
     */
    public function replaceFirst(string $search, string $replace, bool $caseSensitive = true): StringInterface
    {
        if ($search === '') {
            return clone $this;
        }

        $pos = null;

        if ($caseSensitive) {
            $pos = mb_strpos($this->value, $search, 0, $this->encoding);
        } else {
            $pos = mb_stripos($this->value, $search, 0, $this->encoding);
        }

        if ($pos === false) {
            return clone $this;
        }

        $start = mb_substr($this->value, 0, $pos, $this->encoding);
        $end = mb_substr($this->value, $pos + mb_strlen($search, $this->encoding), null, $this->encoding);

        $result = $start . $replace . $end;

        $copy = clone $this;
        $copy->value = $result;

        return $copy;
    }

    /**
     * Replace the last occurrence of a substring with a new substring
     *
     * @param string $search String to search for
     * @param string $replace String to replace with
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return static New string with the last occurrence replaced
     */
    public function replaceLast(string $search, string $replace, bool $caseSensitive = true): StringInterface
    {
        if ($search === '') {
            return clone $this;
        }

        $pos = null;

        if ($caseSensitive) {
            $pos = mb_strrpos($this->value, $search, 0, $this->encoding);
        } else {
            $pos = mb_strripos($this->value, $search, 0, $this->encoding);
        }

        if ($pos === false) {
            return clone $this;
        }

        $start = mb_substr($this->value, 0, $pos, $this->encoding);
        $end = mb_substr($this->value, $pos + mb_strlen($search, $this->encoding), null, $this->encoding);

        $result = $start . $replace . $end;

        $copy = clone $this;
        $copy->value = $result;

        return $copy;
    }

    /**
     * Pad the string with characters to the specified length
     *
     * @param string $chars Characters for padding
     * @param int $length Target length
     * @param int $mode Padding mode (STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH)
     * @return static New string padded to the specified length
     */
    public function pad(string $chars, int $length, int $mode = STR_PAD_BOTH): StringInterface
    {
        $copy = clone $this;
        $copy->value = str_pad($this->value, $length, $chars, $mode);

        return $copy;
    }

    /**
     * Pad the string with characters to the right up to the specified length
     *
     * @param string $chars Characters for padding
     * @param int $length Target length
     * @return static New string padded to the right
     */
    public function padEnd(string $chars, int $length): StringInterface
    {
        $copy = clone $this;
        $copy->value = str_pad($this->value, $length, $chars, STR_PAD_RIGHT);

        return $copy;
    }

    /**
     * Pad the string with characters to the left up to the specified length
     *
     * @param string $chars Characters for padding
     * @param int $length Target length
     * @return static New string padded to the left
     */
    public function padStart(string $chars, int $length): StringInterface
    {
        $copy = clone $this;
        $copy->value = str_pad($this->value, $length, $chars, STR_PAD_LEFT);

        return $copy;
    }

    /**
     * Convert the string to ASCII
     *
     * @param string $language Source text language for specific transliteration rules
     * @param bool $strict Whether to strictly remove non-ASCII characters
     * @return static New string converted to ASCII
     */
    public function toAscii(string $language = '', bool $strict = false): StringInterface
    {
        $copy = clone $this;
        $copy->value = Unicode::toAscii($this->value, $language, $strict);

        return $copy;
    }

    /**
     * ArrayAccess implementation - Check if the offset exists
     *
     * @param mixed $offset The offset to check
     * @return bool True if the offset exists
     */
    public function offsetExists(mixed $offset): bool
    {
        return is_int($offset) && $this->has($offset);
    }

    /**
     * ArrayAccess implementation - Get the value at the offset
     *
     * @param numeric-string|int $offset The offset to retrieve
     * @return string|null The character at the offset or null if the offset doesn't exist
     * @throws \OutOfBoundsException if offset is illegal
     */
    public function offsetGet(mixed $offset): ?string
    {
        if (!is_numeric($offset)) {
            throw new \OutOfRangeException("Illegal offset: $offset");
        }

        return $this->charAt((int) $offset);
    }

    /**
     * ArrayAccess implementation - Set the value at the offset (immutable, not supported)
     *
     * @param mixed $offset The offset to assign the value to
     * @param mixed $value The value to set
     * @return void
     * @throws RuntimeException Always throws exception as string objects are immutable
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Cannot modify an immutable string object');
    }

    /**
     * ArrayAccess implementation - Unset the value at the offset (immutable, not supported)
     *
     * @param mixed $offset The offset to unset
     * @return void
     * @throws RuntimeException Always throws exception as string objects are immutable
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Cannot modify an immutable string object');
    }
}
