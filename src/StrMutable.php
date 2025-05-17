<?php

declare(strict_types=1);

namespace Bermuda\Stdlib;

use InvalidArgumentException;
use Stringable;
use RuntimeException;

/**
 * Mutable string class
 *
 * Provides string manipulation methods that modify the current instance.
 * Most operations modify the string in-place and return $this for method chaining.
 */
final class StrMutable implements StringInterface
{
    use BaseString;

    /**
     * Create a mutable string
     *
     * @param string|Stringable $string Initial string value
     * @param string $encoding Character encoding to use
     * @return self New mutable string instance
     */
    public static function create(string|Stringable $string, string $encoding = 'UTF-8'): self
    {
        return new self($string, $encoding);
    }

    /**
     * Get the character at the specified index
     *
     * @param int $index Position of the character (negative values count from the end)
     * @return self New string containing only the specified character
     * @throws RuntimeException If the index is out of bounds
     */
    public function at(int $index): self
    {
        if (!$this->has($index)) {
            throw new RuntimeException('Invalid offset: ' . $index);
        }

        $copy = clone $this;
        $copy->value = $this->charAt($index) ?? '';

        return $copy;
    }

    /**
     * Get the first character of the string
     *
     * @return self|null New string with the first character or null if the string is empty
     */
    public function first(): ?self
    {
        $firstIndex = $this->firstIndex();
        return $firstIndex !== null ? $this->at($firstIndex) : null;
    }

    /**
     * Get the last character of the string
     *
     * @return self|null New string with the last character or null if the string is empty
     */
    public function last(): ?self
    {
        $lastIndex = $this->lastIndex();
        return $lastIndex !== null ? $this->at($lastIndex) : null;
    }

    /**
     * Get a substring
     *
     * @param int $start Start position (negative values count from the end)
     * @param int|null $length Length of the substring or null to get all characters from the start position
     * @return self The current instance with the value modified to the substring
     */
    public function substring(int $start, ?int $length = null): self
    {
        $this->value = Stringy::substring($this->value, $start, $length, $this->encoding);
        return $this;
    }

    /**
     * Get a substring between two strings
     *
     * @param string $start Starting delimiter
     * @param string $end Ending delimiter
     * @return self|null Current instance with the value modified to the substring between delimiters or null if start delimiter not found
     */
    public function between(string $start, string $end): ?self
    {
        $startIndex = $this->indexOf($start);

        if ($startIndex === null) {
            $this->value = '';
            return null;
        }

        $startIndex += mb_strlen($start, $this->encoding);
        $this->value = mb_substr($this->value, $startIndex, null, $this->encoding);

        $endIndex = $this->indexOf($end);

        if ($endIndex === null) {
            return $this;
        }

        $this->value = mb_substr($this->value, 0, $endIndex, $this->encoding);

        return $this;
    }

    /**
     * Get a substring before the specified substring
     *
     * @param string $substring Substring to find
     * @param bool $inclusive Whether to include the found substring in the result
     * @return self|null Current instance with the value modified to the substring before the specified substring or null if not found
     */
    public function before(string $substring, bool $inclusive = false): ?self
    {
        $pos = $this->indexOf($substring);

        if ($pos === null) {
            $this->value = '';
            return null;
        }

        if ($inclusive) {
            $this->value = mb_substr($this->value, 0, $pos + mb_strlen($substring, $this->encoding), $this->encoding);
        } else {
            $this->value = mb_substr($this->value, 0, $pos, $this->encoding);
        }

        return $this;
    }

    /**
     * Get a substring after the specified substring
     *
     * @param string $substring Substring to find
     * @param bool $inclusive Whether to include the found substring in the result
     * @return self|null Current instance with the value modified to the substring after the specified substring or null if not found
     */
    public function after(string $substring, bool $inclusive = false): ?self
    {
        $pos = $this->indexOf($substring);

        if ($pos === null) {
            $this->value = '';
            return null;
        }

        if ($inclusive) {
            $this->value = mb_substr($this->value, $pos, null, $this->encoding);
        } else {
            $this->value = mb_substr($this->value, $pos + mb_strlen($substring, $this->encoding), null, $this->encoding);
        }

        return $this;
    }

    /**
     * Split the string into two parts
     *
     * @param string|int $delimiter Delimiter string or position
     * @return array<self> Array containing two string objects: [before, after]
     */
    public function split(string|int $delimiter): array
    {
        if (is_string($delimiter)) {
            $index = $this->indexOf($delimiter);

            if ($index === null) {
                return [$this->copy(), new self('', $this->encoding)];
            }

            $index += mb_strlen($delimiter, $this->encoding);
        } else {
            $index = $delimiter;
        }

        $first = clone $this;
        $first->substring(0, $index);

        $second = clone $this;
        $second->substring($index);

        return [$first, $second];
    }

    /**
     * Split the string by a delimiter
     *
     * @param string $delimiter Delimiter string
     * @param int $limit Maximum number of segments to return
     * @param bool $asStrings Whether to return results as strings instead of string objects
     * @return array<self|string> Array of string segments
     */
    public function explode(string $delimiter = '/', int $limit = PHP_INT_MAX, bool $asStrings = false): array
    {
        $segments = explode($delimiter, $this->value, $limit);

        if ($asStrings) {
            return $segments;
        }

        return array_map(fn($segment) => new self($segment, $this->encoding), $segments);
    }

    /**
     * Change string encoding
     *
     * @param string $encoding New encoding to use
     * @return self Current instance with the encoding changed
     */
    public function encode(string $encoding): self
    {
        $this->value = mb_convert_encoding($this->value, $encoding, $this->encoding);
        $this->encoding = $encoding;
        $this->isMultibyte = Stringy::isMultibyte($this->value);

        return $this;
    }

    /**
     * Insert a substring at the specified position
     *
     * @param string $substring Substring to insert
     * @param int $position Position where to insert the substring
     * @return self Current instance with the substring inserted
     */
    public function insert(string $substring, int $position): self
    {
        if ($position < 0) {
            $position = $this->length() + $position;

            if ($position < 0) {
                $position = 0;
            }
        }

        if ($position > $this->length()) {
            $position = $this->length();
        }

        if ($this->isMultibyte) {
            $before = mb_substr($this->value, 0, $position, $this->encoding);
            $after = mb_substr($this->value, $position, null, $this->encoding);
        } else {
            $before = substr($this->value, 0, $position);
            $after = substr($this->value, $position);
        }

        $this->value = $before . $substring . $after;

        return $this;
    }

    /**
     * Wrap the string with the specified character
     *
     * @param string $char Character to add at the beginning and end of the string
     * @return self Current instance wrapped with the specified character
     */
    public function wrap(string $char): self
    {
        $this->value = $char . $this->value . $char;
        return $this;
    }

    /**
     * Pad the string with characters to the specified length
     *
     * @param string $chars Characters for padding
     * @param int $length Target length
     * @param int $mode Padding mode (STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH)
     * @return self Current instance padded to the specified length
     */
    public function pad(string $chars, int $length, int $mode = STR_PAD_BOTH): self
    {
        $this->value = str_pad($this->value, $length, $chars, $mode);
        return $this;
    }

    /**
     * Pad the string with characters to the right up to the specified length
     *
     * @param string $chars Characters for padding
     * @param int $length Target length
     * @return self Current instance padded to the right
     */
    public function padEnd(string $chars, int $length): self
    {
        $this->value = str_pad($this->value, $length, $chars, STR_PAD_RIGHT);
        return $this;
    }

    /**
     * Pad the string with characters to the left up to the specified length
     *
     * @param string $chars Characters for padding
     * @param int $length Target length
     * @return self Current instance padded to the left
     */
    public function padStart(string $chars, int $length): self
    {
        $this->value = str_pad($this->value, $length, $chars, STR_PAD_LEFT);
        return $this;
    }

    /**
     * Remove whitespace characters from the beginning and end of the string
     *
     * @param string $characters Characters to remove
     * @return self Current instance with whitespace removed
     */
    public function trim(string $characters = " \t\n\r\0\x0B"): self
    {
        $this->value = Stringy::trim($this->value, $characters, $this->encoding);
        return $this;
    }

    /**
     * Remove whitespace characters from the beginning of the string
     *
     * @param string $characters Characters to remove
     * @return self Current instance with whitespace removed from the beginning
     */
    public function trimStart(string $characters = " \t\n\r\0\x0B"): self
    {
        $this->value = Stringy::trimStart($this->value, $characters, $this->encoding);
        return $this;
    }

    /**
     * Remove whitespace characters from the end of the string
     *
     * @param string $characters Characters to remove
     * @return self Current instance with whitespace removed from the end
     */
    public function trimEnd(string $characters = " \t\n\r\0\x0B"): self
    {
        $this->value = Stringy::trimEnd($this->value, $characters, $this->encoding);
        return $this;
    }

    /**
     * Remove all whitespace characters
     *
     * @return self Current instance with all whitespace removed
     */
    public function stripWhitespace(): self
    {
        $this->value = preg_replace('/\s+/u', '', $this->value) ?? $this->value;
        return $this;
    }

    /**
     * Remove duplicate whitespace characters
     *
     * @return self Current instance with duplicate whitespace removed
     */
    public function collapseWhitespace(): self
    {
        $this->value = preg_replace('/\s+/u', ' ', $this->value) ?? $this->value;
        $this->value = trim($this->value);

        return $this;
    }

    /**
     * Remove the specified prefix from the string
     *
     * @param string $prefix Prefix to remove
     * @return self Current instance with the prefix removed if it exists
     */
    public function removePrefix(string $prefix): self
    {
        if ($prefix === '' || !$this->startsWith($prefix)) {
            return $this;
        }

        $prefixLength = mb_strlen($prefix, $this->encoding);
        $this->value = mb_substr($this->value, $prefixLength, null, $this->encoding);

        return $this;
    }

    /**
     * Remove the specified suffix from the string
     *
     * @param string $suffix Suffix to remove
     * @return self Current instance with the suffix removed if it exists
     */
    public function removeSuffix(string $suffix): self
    {
        if ($suffix === '' || !$this->endsWith($suffix)) {
            return $this;
        }

        $stringLength = mb_strlen($this->value, $this->encoding);
        $suffixLength = mb_strlen($suffix, $this->encoding);

        $this->value = mb_substr($this->value, 0, $stringLength - $suffixLength, $this->encoding);

        return $this;
    }

    /**
     * Capitalize the first character of the string
     *
     * @return self Current instance with the first character capitalized
     */
    public function capitalize(): self
    {
        if ($this->value === '') {
            return $this;
        }

        if ($this->isMultibyte) {
            $first = mb_substr($this->value, 0, 1, $this->encoding);
            $rest = mb_substr($this->value, 1, null, $this->encoding);

            $this->value = mb_strtoupper($first, $this->encoding) . $rest;
        } else {
            $this->value = ucfirst($this->value);
        }

        return $this;
    }

    /**
     * Convert the first character of the string to lowercase
     *
     * @return self Current instance with the first character converted to lowercase
     */
    public function uncapitalize(): self
    {
        if ($this->value === '') {
            return $this;
        }

        if ($this->isMultibyte) {
            $first = mb_substr($this->value, 0, 1, $this->encoding);
            $rest = mb_substr($this->value, 1, null, $this->encoding);

            $this->value = mb_strtolower($first, $this->encoding) . $rest;
        } else {
            $this->value = lcfirst($this->value);
        }

        return $this;
    }

    /**
     * Capitalize the first character of each word
     *
     * @return self Current instance with the first character of each word capitalized
     */
    public function capitalizeWords(): self
    {
        $this->value = mb_convert_case($this->value, MB_CASE_TITLE, $this->encoding);
        return $this;
    }

    /**
     * Limit the string to the specified length
     *
     * @param int $length Maximum length
     * @param string $suffix String to append if truncated
     * @param bool $preserveWords Whether to preserve whole words
     * @return self Current instance truncated to the specified length
     */
    public function truncate(int $length = 200, string $suffix = '...', bool $preserveWords = false): self
    {
        $this->value = Stringy::truncate($this->value, $length, $suffix, $preserveWords, $this->encoding);
        return $this;
    }

    /**
     * Convert the string to lowercase
     *
     * @return self Current instance converted to lowercase
     */
    public function toLowerCase(): self
    {
        $this->value = mb_strtolower($this->value, $this->encoding);
        return $this;
    }

    /**
     * Convert the string to uppercase
     *
     * @return self Current instance converted to uppercase
     */
    public function toUpperCase(): self
    {
        $this->value = mb_strtoupper($this->value, $this->encoding);
        return $this;
    }

    /**
     * Swap the case of each character
     *
     * @return self Current instance with the case of each character swapped
     */
    public function swapCase(): self
    {
        $this->value = Stringy::swapCase($this->value, $this->encoding);
        return $this;
    }

    /**
     * Convert the string where each word starts with a capital letter
     *
     * @param array<string> $ignore Words to ignore during titleizing
     * @return self Current instance with words titleized
     */
    public function titleize(array $ignore = []): self
    {
        $this->value = Stringy::titleize($this->value, $ignore);
        return $this;
    }

    /**
     * Convert the string to kebab-case format
     *
     * @return self Current instance in kebab-case format
     */
    public function toKebabCase(): self
    {
        $this->toSnakeCase();
        $this->value = str_replace('_', '-', $this->value);

        return $this;
    }

    /**
     * Convert the string to snake_case format
     *
     * @return self Current instance in snake_case format
     */
    public function toSnakeCase(): self
    {
        $this->value = Stringy::toSnakeCase($this->value, $this->encoding);
        return $this;
    }

    /**
     * Convert the string with the specified delimiter
     *
     * @param string $delimiter Delimiter to use
     * @return self Current instance with the specified delimiter
     */
    public function delimit(string $delimiter): self
    {
        $this->value = Stringy::delimit($this->value, $delimiter, $this->encoding);
        return $this;
    }

    /**
     * Convert the string to camelCase format
     *
     * @return self Current instance in camelCase format
     */
    public function toCamelCase(): self
    {
        $this->toSnakeCase();
        $this->value = str_replace('_', ' ', $this->value);
        $this->value = ucwords($this->value);
        $this->value = str_replace(' ', '', $this->value);
        $this->value = lcfirst($this->value);

        return $this;
    }

    /**
     * Convert the string to PascalCase format
     *
     * @return self Current instance in PascalCase format
     */
    public function toPascalCase(): self
    {
        $this->toCamelCase();
        $this->value = ucfirst($this->value);

        return $this;
    }

    /**
     * Replace all occurrences of a substring with a new substring
     *
     * @param string|array<string> $search String or array of strings to search for
     * @param string|array<string> $replace String or array of strings to replace with
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return self Current instance with all occurrences replaced
     */
    public function replace(string|array $search, string|array $replace, bool $caseSensitive = true): self
    {
        if ($caseSensitive) {
            $this->value = str_replace($search, $replace, $this->value);
        } else {
            $this->value = str_ireplace($search, $replace, $this->value);
        }

        return $this;
    }

    /**
     * Replace text in a string using regular expressions
     *
     * @param string|array<string> $pattern Pattern or array of patterns to search for
     * @param string|array<string> $replacement Replacement string or array of strings
     * @param int $limit Maximum replacements (-1 for unlimited)
     * @param int|null $count If provided, will be set to the number of replacements performed
     * @return self Current instance with replacements
     * @throws RuntimeException If an error occurs during pattern replacement
     */
    public function replaceBy(string|array $pattern, string|array $replacement, int $limit = -1, ?int &$count = null): StringInterface
    {
        if (is_string($pattern) && $pattern === '') {
            return $this;
        }

        if ($this->isMultibyte) {
            $result = Stringy::replace($this->value, $pattern, $replacement, $limit, $count, $this->encoding);
        } else {
            $result = @preg_replace($pattern, $replacement, $this->value, $limit, $count);
        }

        if ($result === null) {
            throw new RuntimeException('Error in regular expression replacement: ' . preg_last_error_msg());
        }

        $this->value = $result;
        return $this;
    }

    /**
     * Replace text using a callback function
     *
     * @param string|array<string> $pattern Pattern or array of patterns to search for
     * @param callable $callback Callback function for replacement
     * @param int $limit Maximum replacements (-1 for unlimited)
     * @param int|null $count If provided, will be set to the number of replacements
     * @return self Current instance with replacements
     * @throws RuntimeException If an error occurs during pattern replacement
     */
    public function replaceCallback(string|array $pattern, callable $callback, int $limit = -1, ?int &$count = null): StringInterface
    {
        if (is_string($pattern) && $pattern === '') {
            return $this;
        }

        if ($this->isMultibyte) {
            $result = Stringy::replaceCallback($this->value, $pattern, $callback, $limit, $count, $this->encoding);
        } else {
            $result = @preg_replace_callback($pattern, $callback, $this->value, $limit, $count);
        }

        if ($result === null) {
            throw new RuntimeException('Error in regular expression replacement: ' . preg_last_error_msg());
        }

        $this->value = $result;
        return $this;
    }

    /**
     * Replace using a regular expression
     *
     * @param string|array<string> $pattern Pattern or array of patterns
     * @param string|array<string> $replacement Replacement string or array
     * @param int $limit Maximum number of replacements
     * @param int|null $count If provided, will be filled with the number of replacements
     * @return self Current instance with the pattern replaced
     * @throws RuntimeException If an error occurs during pattern replacement
     */
    public function replacePattern(string|array $pattern, string|array $replacement, int $limit = -1, ?int &$count = null): self
    {
        $result = @preg_replace($pattern, $replacement, $this->value, $limit, $count);

        if ($result === null) {
            throw new RuntimeException('Error in regular expression replacement: ' . preg_last_error_msg());
        }

        $this->value = $result;

        return $this;
    }

    /**
     * Add a prefix if it doesn't exist
     *
     * @param string $prefix Prefix to ensure
     * @return self Current instance with the prefix added if it doesn't exist
     */
    public function ensurePrefix(string $prefix): self
    {
        if ($this->startsWith($prefix)) {
            return $this;
        }

        $this->value = $prefix . $this->value;

        return $this;
    }

    /**
     * Add a suffix if it doesn't exist
     *
     * @param string $suffix Suffix to ensure
     * @return self Current instance with the suffix added if it doesn't exist
     */
    public function ensureSuffix(string $suffix): self
    {
        if ($this->endsWith($suffix)) {
            return $this;
        }

        $this->value .= $suffix;

        return $this;
    }

    /**
     * Add a substring to the end of the string
     *
     * @param string $suffix String to append
     * @return self Current instance with the suffix appended
     */
    public function append(string $suffix): self
    {
        $this->value .= $suffix;
        return $this;
    }

    /**
     * Add a substring to the beginning of the string
     *
     * @param string $prefix String to prepend
     * @return self Current instance with the prefix prepended
     */
    public function prepend(string $prefix): self
    {
        $this->value = $prefix . $this->value;
        return $this;
    }

    /**
     * Repeat the string the specified number of times
     *
     * @param int $times Number of times to repeat
     * @return self Current instance repeated the specified number of times
     * @throws InvalidArgumentException If times is negative
     */
    public function repeat(int $times): self
    {
        if ($times < 0) {
            throw new InvalidArgumentException('Times must be a non-negative integer');
        }

        if ($times === 0) {
            $this->value = '';
            return $this;
        }

        if ($times === 1 || $this->value === '') {
            return $this;
        }

        $this->value = str_repeat($this->value, $times);

        return $this;
    }

    /**
     * Reverse the string
     *
     * @return self Current instance with characters in reverse order
     */
    public function reverse(): self
    {
        if ($this->value === '') {
            return $this;
        }

        if ($this->isMultibyte) {
            $chars = $this->toArray();
            $this->value = implode('', array_reverse($chars));
        } else {
            $this->value = strrev($this->value);
        }

        return $this;
    }

    /**
     * Shuffle the characters of the string
     *
     * @return self Current instance with characters shuffled
     */
    public function shuffle(): self
    {
        if ($this->value === '') {
            return $this;
        }

        $this->value = Stringy::shuffle($this->value);
        return $this;
    }

    /**
     * Apply a function to the string
     *
     * @param callable $callback Function to apply to the string
     * @return self Current instance with the function applied
     */
    public function transform(callable $callback): self
    {
        $this->value = (string) $callback($this);
        return $this;
    }

    /**
     * Replace tabs with spaces
     *
     * @param int $tabSize Number of spaces to replace each tab with
     * @return self Current instance with tabs replaced by spaces
     */
    public function tabsToSpaces(int $tabSize = 4): self
    {
        $spaces = str_repeat(' ', abs($tabSize));
        $this->value = str_replace("\t", $spaces, $this->value);

        return $this;
    }

    /**
     * Replace spaces with tabs
     *
     * @param int $tabSize Number of spaces that correspond to a tab
     * @return self Current instance with spaces replaced by tabs
     */
    public function spacesToTabs(int $tabSize = 4): self
    {
        $spaces = str_repeat(' ', abs($tabSize));
        $this->value = str_replace($spaces, "\t", $this->value);

        return $this;
    }

    /**
     * Format the string
     *
     * @param string ...$args Arguments to format the string with
     * @return self Current instance formatted with the arguments
     */
    public function format(string ...$args): self
    {
        $this->value = sprintf($this->value, ...$args);
        return $this;
    }

    /**
     * Perform a function for each character of the string
     *
     * @param callable $callback Function to apply to each character
     * @return bool True if all callbacks returned true, false otherwise
     */
    public function each(callable $callback): bool
    {
        $lastIndex = $this->lastIndex();

        if ($lastIndex === null) {
            return true;
        }

        for ($i = 0; $i <= $lastIndex; $i++) {
            if ($callback($this->charAt($i), $i) !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set the string value directly
     *
     * @param string $string New string value
     * @return self Current instance with the value set
     */
    public function setString(string $string): self
    {
        $this->value = $string;
        $this->isMultibyte = Stringy::isMultibyte($this->value);

        return $this;
    }

    /**
     * Convert the string to ASCII
     *
     * @param string $language Source text language for specific transliteration rules
     * @param bool $strict Whether to strictly remove non-ASCII characters
     * @return self Current instance converted to ASCII
     */
    public function toAscii(string $language = '', bool $strict = false): self
    {
        $this->value = Unicode::toAscii($this->value, $language, $strict);
        return $this;
    }

    /**
     * ArrayAccess implementation - Set the character at the specified offset
     *
     * @param numeric-string|int $offset The offset to assign the value to
     * @param string $value The character to set
     * @return void
     * @throws \OutOfRangeException If offset is not an integer or is out of bounds
     * @throws \InvalidArgumentException If value is not a string or is longer than 1 character
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Check if offset is valid
        if (!is_numeric($offset)) {
            throw new \OutOfRangeException("Illegal offset: $offset");
        }

        $offset = (int) $offset;

        // Check if position exists
        if (!$this->has($offset)) {
            throw new \OutOfRangeException("Invalid position: $offset");
        }

        // Check if value is a string
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Value must be a string, " . gettype($value) . " given");
        }

        // Check if value is a single character
        if (mb_strlen($value, $this->encoding) !== 1) {
            throw new \InvalidArgumentException("Value must be a single character");
        }

        // Handle negative offset
        if ($offset < 0) {
            $offset = $this->length() + $offset;
        }

        // Replace the character at the specified position
        if ($this->isMultibyte) {
            $before = mb_substr($this->value, 0, $offset, $this->encoding);
            $after = mb_substr($this->value, $offset + 1, null, $this->encoding);
            $this->value = $before . $value . $after;
        } else {
            $this->value = substr_replace($this->value, $value, $offset, 1);
        }
    }

    /**
     * ArrayAccess implementation - Unset (remove) the character at the specified offset
     *
     * @param numeric-string|int $offset The offset to unset
     * @return void
     * @throws \OutOfRangeException If offset is not an integer or is out of bounds
     */
    public function offsetUnset(mixed $offset): void
    {
        // Check if offset is valid
        if (!is_numeric($offset)) {
            throw new \OutOfRangeException("Illegal offset: $offset");
        }

        $offset = (int) $offset;

        // Check if position exists
        if (!$this->has($offset)) {
            throw new \OutOfRangeException("Invalid position: $offset");
        }

        // Handle negative offset
        if ($offset < 0) {
            $offset = $this->length() + $offset;
        }

        // Remove the character at the specified position
        if ($this->isMultibyte) {
            $before = mb_substr($this->value, 0, $offset, $this->encoding);
            $after = mb_substr($this->value, $offset + 1, null, $this->encoding);
            $this->value = $before . $after;
        } else {
            $this->value = substr_replace($this->value, '', $offset, 1);
        }
    }
}
