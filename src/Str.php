<?php

declare(strict_types=1);

namespace Bermuda\Stringy;

use InvalidArgumentException;
use Stringable;
use RuntimeException;

/**
 * Immutable string class
 *
 * Provides string manipulation methods that always return a new instance
 * without modifying the original string.
 */
final class Str implements StringInterface
{
    use BaseString;

    /**
     * Create a string from a string value
     *
     * @param string|Stringable $string Initial string value
     * @param string $encoding Character encoding to use
     * @return self New string instance
     */
    public static function from(string|Stringable $string, string $encoding = 'UTF-8'): self
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
     * @return self New string containing the substring
     */
    public function substring(int $start, ?int $length = null): self
    {
        $substring = Stringy::substring($this->value, $start, $length, $this->encoding);
        return new self($substring, $this->encoding);
    }

    /**
     * Get a substring between two strings
     *
     * @param string $start Starting delimiter
     * @param string $end Ending delimiter
     * @return self|null Substring between delimiters or null if start delimiter not found
     */
    public function between(string $start, string $end): ?self
    {
        $startIndex = $this->indexOf($start);

        if ($startIndex === null) {
            return null;
        }

        $startIndex += mb_strlen($start, $this->encoding);
        $after = $this->substring($startIndex);

        $endIndex = $after->indexOf($end);

        if ($endIndex === null) {
            return $after;
        }

        return $after->substring(0, $endIndex);
    }

    /**
     * Get a substring before the specified substring
     *
     * @param string $substring Substring to find
     * @param bool $inclusive Whether to include the found substring in the result
     * @return self|null Substring before the specified substring or null if not found
     */
    public function before(string $substring, bool $inclusive = false): ?self
    {
        $pos = $this->indexOf($substring);

        if ($pos === null) {
            return null;
        }

        if ($inclusive) {
            return $this->substring(0, $pos + mb_strlen($substring, $this->encoding));
        }

        return $this->substring(0, $pos);
    }

    /**
     * Get a substring after the specified substring
     *
     * @param string $substring Substring to find
     * @param bool $inclusive Whether to include the found substring in the result
     * @return self|null Substring after the specified substring or null if not found
     */
    public function after(string $substring, bool $inclusive = false): ?self
    {
        $pos = $this->indexOf($substring);

        if ($pos === null) {
            return null;
        }

        if ($inclusive) {
            return $this->substring($pos);
        }

        return $this->substring($pos + mb_strlen($substring, $this->encoding));
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

        return [$this->substring(0, $index), $this->substring($index)];
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
     * @return self New string with the specified encoding
     */
    public function encode(string $encoding): self
    {
        $copy = clone $this;
        $copy->value = mb_convert_encoding($this->value, $encoding, $this->encoding);
        $copy->encoding = $encoding;
        $copy->isMultibyte = Stringy::isMultibyte($copy->value);

        return $copy;
    }

    /**
     * Insert a substring at the specified position
     *
     * @param string $substring Substring to insert
     * @param int $position Position where to insert the substring
     * @return self New string with the substring inserted
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

        return new self($before . $substring . $after, $this->encoding);
    }

    /**
     * Wrap the string with the specified character
     *
     * @param string $char Character to add at the beginning and end of the string
     * @return self New string wrapped with the specified character
     */
    public function wrap(string $char): self
    {
        return new self($char . $this->value . $char, $this->encoding);
    }

    /**
     * Remove whitespace characters from the beginning and end of the string
     *
     * @param string $characters Characters to remove
     * @return self New string with whitespace removed
     */
    public function trim(string $characters = " \t\n\r\0\x0B"): self
    {
        if ($this->isMultibyte) {
            $characters = preg_quote($characters, '/');
            $result = preg_replace('/^[' . $characters . ']+|[' . $characters . ']+$/u', '', $this->value);
            return new self($result ?? $this->value, $this->encoding);
        }

        return new self(trim($this->value, $characters), $this->encoding);
    }

    /**
     * Remove whitespace characters from the beginning of the string
     *
     * @param string $characters Characters to remove
     * @return self New string with whitespace removed from the beginning
     */
    public function trimStart(string $characters = " \t\n\r\0\x0B"): self
    {
        if ($this->isMultibyte) {
            $characters = preg_quote($characters, '/');
            $result = preg_replace('/^[' . $characters . ']+/u', '', $this->value);
            return new self($result ?? $this->value, $this->encoding);
        }

        return new self(ltrim($this->value, $characters), $this->encoding);
    }

    /**
     * Remove whitespace characters from the end of the string
     *
     * @param string $characters Characters to remove
     * @return self New string with whitespace removed from the end
     */
    public function trimEnd(string $characters = " \t\n\r\0\x0B"): self
    {
        if ($this->isMultibyte) {
            $characters = preg_quote($characters, '/');
            $result = preg_replace('/[' . $characters . ']+$/u', '', $this->value);
            return new self($result ?? $this->value, $this->encoding);
        }

        return new self(rtrim($this->value, $characters), $this->encoding);
    }

    /**
     * Remove all whitespace characters
     *
     * @return self New string with all whitespace removed
     */
    public function stripWhitespace(): self
    {
        return new self(preg_replace('/\s+/u', '', $this->value) ?? $this->value, $this->encoding);
    }

    /**
     * Remove duplicate whitespace characters
     *
     * @return self New string with duplicate whitespace removed
     */
    public function collapseWhitespace(): self
    {
        return new self(preg_replace('/\s+/u', ' ', trim($this->value)) ?? $this->value, $this->encoding);
    }

    /**
     * Remove the specified prefix from the string
     *
     * @param string $prefix Prefix to remove
     * @return self New string with the prefix removed if it exists
     */
    public function removePrefix(string $prefix): self
    {
        if ($prefix === '' || !$this->startsWith($prefix)) {
            return clone $this;
        }

        $prefixLength = mb_strlen($prefix, $this->encoding);
        return new self(mb_substr($this->value, $prefixLength, null, $this->encoding), $this->encoding);
    }

    /**
     * Remove the specified suffix from the string
     *
     * @param string $suffix Suffix to remove
     * @return self New string with the suffix removed if it exists
     */
    public function removeSuffix(string $suffix): self
    {
        if ($suffix === '' || !$this->endsWith($suffix)) {
            return clone $this;
        }

        $stringLength = mb_strlen($this->value, $this->encoding);
        $suffixLength = mb_strlen($suffix, $this->encoding);

        return new self(
            mb_substr($this->value, 0, $stringLength - $suffixLength, $this->encoding),
            $this->encoding
        );
    }

    /**
     * Capitalize the first character of the string
     *
     * @return self New string with the first character capitalized
     */
    public function capitalize(): self
    {
        if ($this->value === '') {
            return clone $this;
        }

        if ($this->isMultibyte) {
            $first = mb_substr($this->value, 0, 1, $this->encoding);
            $rest = mb_substr($this->value, 1, null, $this->encoding);

            return new self(mb_strtoupper($first, $this->encoding) . $rest, $this->encoding);
        }

        return new self(ucfirst($this->value), $this->encoding);
    }

    /**
     * Convert the first character of the string to lowercase
     *
     * @return self New string with the first character converted to lowercase
     */
    public function uncapitalize(): self
    {
        if ($this->value === '') {
            return clone $this;
        }

        if ($this->isMultibyte) {
            $first = mb_substr($this->value, 0, 1, $this->encoding);
            $rest = mb_substr($this->value, 1, null, $this->encoding);

            return new self(mb_strtolower($first, $this->encoding) . $rest, $this->encoding);
        }

        return new self(lcfirst($this->value), $this->encoding);
    }

    /**
     * Capitalize the first character of each word
     *
     * @return self New string with the first character of each word capitalized
     */
    public function capitalizeWords(): self
    {
        return new self(mb_convert_case($this->value, MB_CASE_TITLE, $this->encoding), $this->encoding);
    }

    /**
     * Limit the string to the specified length
     *
     * @param int $length Maximum length
     * @param string $suffix String to append if truncated
     * @param bool $preserveWords Whether to preserve whole words
     * @return self New string truncated to the specified length
     */
    public function truncate(int $length = 200, string $suffix = '...', bool $preserveWords = false): self
    {
        $result = Stringy::truncate($this->value, $length, $suffix, $preserveWords, $this->encoding);
        return new self($result, $this->encoding);
    }

    /**
     * Convert the string to lowercase
     *
     * @return self New string converted to lowercase
     */
    public function toLowerCase(): self
    {
        return new self(mb_strtolower($this->value, $this->encoding), $this->encoding);
    }

    /**
     * Convert the string to uppercase
     *
     * @return self New string converted to uppercase
     */
    public function toUpperCase(): self
    {
        return new self(mb_strtoupper($this->value, $this->encoding), $this->encoding);
    }

    /**
     * Swap the case of each character
     *
     * @return self New string with the case of each character swapped
     */
    public function swapCase(): self
    {
        return new self(Stringy::swapCase($this->value, $this->encoding), $this->encoding);
    }

    /**
     * Convert the string where each word starts with a capital letter
     *
     * @param array<string> $ignore Words to ignore during titleizing
     * @return self New string with words titleized
     */
    public function titleize(array $ignore = []): self
    {
        return new self(Stringy::titleize($this->value, $ignore), $this->encoding);
    }

    /**
     * Convert the string to kebab-case format
     *
     * @return self New string in kebab-case format
     */
    public function toKebabCase(): self
    {
        $string = Stringy::toSnakeCase($this->value, $this->encoding);
        return new self(str_replace('_', '-', $string), $this->encoding);
    }

    /**
     * Convert the string to snake_case format
     *
     * @return self New string in snake_case format
     */
    public function toSnakeCase(): self
    {
        return new self(Stringy::toSnakeCase($this->value, $this->encoding), $this->encoding);
    }

    /**
     * Convert the string with the specified delimiter
     *
     * @param string $delimiter Delimiter to use
     * @return self New string with the specified delimiter
     */
    public function delimit(string $delimiter): self
    {
        $old = mb_regex_encoding();

        try {
            mb_regex_encoding($this->encoding);

            $string = mb_ereg_replace('\B([A-Z])', '-\1', $this->trim()->toString());
            $string = mb_ereg_replace('[-_\s]+', $delimiter, mb_strtolower($string, $this->encoding));

            return new self($string, $this->encoding);
        } finally {
            mb_regex_encoding($old);
        }
    }

    /**
     * Convert the string to camelCase format
     *
     * @return self New string in camelCase format
     */
    public function toCamelCase(): self
    {
        $string = Stringy::toSnakeCase($this->value, $this->encoding);
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        $string = lcfirst($string);

        return new self($string, $this->encoding);
    }

    /**
     * Convert the string to PascalCase format
     *
     * @return self New string in PascalCase format
     */
    public function toPascalCase(): self
    {
        $string = $this->toCamelCase()->toString();
        return new self(ucfirst($string), $this->encoding);
    }

    /**
     * Replace all occurrences of a substring with a new substring
     *
     * @param string|array<string> $search String or array of strings to search for
     * @param string|array<string> $replace String or array of strings to replace with
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return self New string with all occurrences replaced
     */
    public function replace(string|array $search, string|array $replace, bool $caseSensitive = true): self
    {
        if ($caseSensitive) {
            $result = str_replace($search, $replace, $this->value);
        } else {
            $result = str_ireplace($search, $replace, $this->value);
        }

        return new self($result, $this->encoding);
    }

    /**
     * Replace text in a string using regular expressions
     *
     * @param string|array<string> $pattern Pattern or array of patterns to search for
     * @param string|array<string> $replacement Replacement string or array of strings
     * @param int $limit Maximum replacements (-1 for unlimited)
     * @param int|null $count If provided, will be set to the number of replacements performed
     * @return self New string instance with replacements
     * @throws RuntimeException If an error occurs during pattern replacement
     */
    public function replaceBy(string|array $pattern, string|array $replacement, int $limit = -1, ?int &$count = null): StringInterface
    {
        if (is_string($pattern) && $pattern === '') {
            return clone $this;
        }

        if ($this->isMultibyte) {
            $result = Stringy::replace($this->value, $pattern, $replacement, $limit, $count, $this->encoding);
        } else {
            $result = @preg_replace($pattern, $replacement, $this->value, $limit, $count);
        }

        if ($result === null) {
            throw new RuntimeException('Error in regular expression replacement: ' . preg_last_error_msg());
        }

        return new self($result, $this->encoding);
    }

    /**
     * Replace text using a callback function
     *
     * @param string|array<string> $pattern Pattern or array of patterns to search for
     * @param callable $callback Callback function for replacement
     * @param int $limit Maximum replacements (-1 for unlimited)
     * @param int|null $count If provided, will be set to the number of replacements
     * @return self New string instance with replacements
     * @throws RuntimeException If an error occurs during pattern replacement
     */
    public function replaceCallback(string|array $pattern, callable $callback, int $limit = -1, ?int &$count = null): StringInterface
    {
        if (is_string($pattern) && $pattern === '') {
            return clone $this;
        }

        if ($this->isMultibyte) {
            $result = Stringy::replaceCallback($this->value, $pattern, $callback, $limit, $count, $this->encoding);
        } else {
            $result = @preg_replace_callback($pattern, $callback, $this->value, $limit, $count);
        }

        if ($result === null) {
            throw new RuntimeException('Error in regular expression replacement: ' . preg_last_error_msg());
        }

        return new self($result, $this->encoding);
    }

    /**
     * Replace using a regular expression
     *
     * @param string|array<string> $pattern Pattern or array of patterns
     * @param string|array<string> $replacement Replacement string or array
     * @param int $limit Maximum number of replacements
     * @param int|null $count If provided, will be filled with the number of replacements
     * @return self New string with the pattern replaced
     * @throws RuntimeException If an error occurs during pattern replacement
     */
    public function replacePattern(string|array $pattern, string|array $replacement, int $limit = -1, ?int &$count = null): self
    {
        $result = @preg_replace($pattern, $replacement, $this->value, $limit, $count);

        if ($result === null) {
            throw new RuntimeException('Error in regular expression replacement: ' . preg_last_error_msg());
        }

        return new self($result, $this->encoding);
    }

    /**
     * Add a prefix if it doesn't exist
     *
     * @param string $prefix Prefix to ensure
     * @return self New string with the prefix added if it doesn't exist
     */
    public function ensurePrefix(string $prefix): self
    {
        if ($this->startsWith($prefix)) {
            return clone $this;
        }

        return new self($prefix . $this->value, $this->encoding);
    }

    /**
     * Add a suffix if it doesn't exist
     *
     * @param string $suffix Suffix to ensure
     * @return self New string with the suffix added if it doesn't exist
     */
    public function ensureSuffix(string $suffix): self
    {
        if ($this->endsWith($suffix)) {
            return clone $this;
        }

        return new self($this->value . $suffix, $this->encoding);
    }

    /**
     * Add a substring to the end of the string
     *
     * @param string $suffix String to append
     * @return self New string with the suffix appended
     */
    public function append(string $suffix): self
    {
        return new self($this->value . $suffix, $this->encoding);
    }

    /**
     * Add a substring to the beginning of the string
     *
     * @param string $prefix String to prepend
     * @return self New string with the prefix prepended
     */
    public function prepend(string $prefix): self
    {
        return new self($prefix . $this->value, $this->encoding);
    }

    /**
     * Repeat the string the specified number of times
     *
     * @param int $times Number of times to repeat
     * @return self New string repeated the specified number of times
     * @throws InvalidArgumentException If times is negative
     */
    public function repeat(int $times): self
    {
        if ($times < 0) {
            throw new InvalidArgumentException('Times must be a non-negative integer');
        }

        if ($times === 0 || $this->value === '') {
            return new self('', $this->encoding);
        }

        if ($times === 1) {
            return clone $this;
        }

        return new self(str_repeat($this->value, $times), $this->encoding);
    }

    /**
     * Reverse the string
     *
     * @return self New string with characters in reverse order
     */
    public function reverse(): self
    {
        if ($this->value === '') {
            return clone $this;
        }

        if ($this->isMultibyte) {
            $chars = $this->toArray();
            $result = implode('', array_reverse($chars));
        } else {
            $result = strrev($this->value);
        }

        return new self($result, $this->encoding);
    }

    /**
     * Shuffle the characters of the string
     *
     * @return self New string with characters shuffled
     */
    public function shuffle(): self
    {
        if ($this->value === '') {
            return clone $this;
        }

        return new self(Stringy::shuffle($this->value), $this->encoding);
    }

    /**
     * Apply a function to the string
     *
     * @param callable $callback Function to apply to the string
     * @return self New string with the function applied
     */
    public function transform(callable $callback): self
    {
        $result = (string) $callback($this);
        return new self($result, $this->encoding);
    }

    /**
     * Replace tabs with spaces
     *
     * @param int $tabSize Number of spaces to replace each tab with
     * @return self New string with tabs replaced by spaces
     */
    public function tabsToSpaces(int $tabSize = 4): self
    {
        $spaces = str_repeat(' ', abs($tabSize));
        return new self(str_replace("\t", $spaces, $this->value), $this->encoding);
    }

    /**
     * Replace spaces with tabs
     *
     * @param int $tabSize Number of spaces that correspond to a tab
     * @return self New string with spaces replaced by tabs
     */
    public function spacesToTabs(int $tabSize = 4): self
    {
        $spaces = str_repeat(' ', abs($tabSize));
        return new self(str_replace($spaces, "\t", $this->value), $this->encoding);
    }

    /**
     * Format the string
     *
     * @param string ...$args Arguments to format the string with
     * @return self New string formatted with the arguments
     */
    public function format(string ...$args): self
    {
        return new self(sprintf($this->value, ...$args), $this->encoding);
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
}