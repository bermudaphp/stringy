<?php

declare(strict_types=1);

namespace Bermuda\Stringy;

use Bermuda\Stdlib\Arrayable;
use DateTimeInterface;
use DateTimeZone;
use Countable;
use Stringable;
use IteratorAggregate;
use ArrayAccess;

/**
 * Interface for string objects
 *
 * Provides a comprehensive API for string manipulation operations
 * with full Unicode support and consistent behavior across implementations.
 */
interface StringInterface extends Stringable, ArrayAccess, Arrayable, Countable, IteratorAggregate
{
    /**
     * Character encoding used for the string (e.g., UTF-8, ASCII)
     */
    public string $encoding { get; }

    /**
     * The underlying string value
     */
    public string $value { get; }

    /**
     * Flag indicating whether the string contains multibyte characters
     */
    public bool $isMultibyte { get; }

    /**
     * Get string representation
     *
     * @return string The current string value
     */
    public function toString(): string;

    /**
     * Get a copy of the object
     *
     * @return StringInterface A new instance with the same value and encoding
     */
    public function copy(): StringInterface;

    /**
     * Change string encoding
     *
     * @param string $encoding The new encoding to use
     * @return StringInterface New string with the specified encoding
     */
    public function encode(string $encoding): StringInterface;

    /**
     * Get the number of bytes in the string
     *
     * @return int Number of bytes in the string
     */
    public function getBytes(): int;

    /**
     * Check if the string starts with the specified substring
     *
     * @param string|array<string> $substring Substring or array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string starts with any of the specified substrings
     */
    public function startsWith(string|array $substring, bool $caseSensitive = true): bool;

    /**
     * Check if the string ends with the specified substring
     *
     * @param string|array<string> $substring Substring or array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string ends with any of the specified substrings
     */
    public function endsWith(string|array $substring, bool $caseSensitive = true): bool;

    /**
     * Check if the string contains the specified substring
     *
     * @param string|array<string> $substring Substring or array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string contains any of the specified substrings
     */
    public function contains(string|array $substring, bool $caseSensitive = true): bool;

    /**
     * Check if the string contains all the specified substrings
     *
     * @param array<string> $substrings Array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string contains all the specified substrings
     */
    public function containsAll(array $substrings, bool $caseSensitive = true): bool;

    /**
     * Compare with one of the passed strings
     *
     * @param array<string|Stringable> $values Array of strings to compare with
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string equals any of the specified values
     */
    public function equalsAny(array $values, bool $caseSensitive = true): bool;

    /**
     * Get the length of the string
     *
     * @return int Number of characters in the string
     */
    public function length(): int;

    /**
     * Get the character at the specified index
     *
     * @param int $index Position of the character (negative values count from the end)
     * @return string|null The character at the specified position or null if the index is out of bounds
     */
    public function charAt(int $index): ?string;

    /**
     * Get the character at the specified index
     *
     * @param int $index Position of the character (negative values count from the end)
     * @return StringInterface A new string object containing only the specified character
     * @throws \RuntimeException If the index is out of bounds
     */
    public function at(int $index): StringInterface;

    /**
     * Check if the character exists at the specified index
     *
     * @param int $index Position to check (negative values count from the end)
     * @return bool True if the index is valid for this string
     */
    public function has(int $index): bool;

    /**
     * Get the first character of the string
     *
     * @return StringInterface|null A new string with the first character or null if the string is empty
     */
    public function first(): ?StringInterface;

    /**
     * Get the last character of the string
     *
     * @return StringInterface|null A new string with the last character or null if the string is empty
     */
    public function last(): ?StringInterface;

    /**
     * Get the index of the first character
     *
     * @return int|null 0 if the string is not empty, null otherwise
     */
    public function firstIndex(): ?int;

    /**
     * Get the index of the last character
     *
     * @return int|null Index of the last character or null if the string is empty
     */
    public function lastIndex(): ?int;

    /**
     * Find the position of the first occurrence of a substring
     *
     * @param string $substring Substring to find
     * @param int $offset Start position for the search
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return int|null Position of the first occurrence or null if not found
     */
    public function indexOf(string $substring, int $offset = 0, bool $caseSensitive = true): ?int;

    /**
     * Find the position of the last occurrence of a substring
     *
     * @param string $substring Substring to find
     * @param int $offset Start position for the search
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return int|null Position of the last occurrence or null if not found
     */
    public function lastIndexOf(string $substring, int $offset = 0, bool $caseSensitive = true): ?int;

    /**
     * Count the number of occurrences of a substring
     *
     * @param string $substring Substring to count
     * @param bool $caseSensitive Whether to perform case-sensitive counting
     * @return int Number of occurrences
     */
    public function countSubstr(string $substring, bool $caseSensitive = true): int;

    /**
     * Get a substring
     *
     * @param int $start Start position (negative values count from the end)
     * @param int|null $length Length of the substring or null to get all characters from the start position
     * @return StringInterface A new string object containing the substring
     */
    public function substring(int $start, ?int $length = null): StringInterface;

    /**
     * Get the beginning of the string
     *
     * @param int $length Number of characters to get from the beginning
     * @return StringInterface A new string object containing the first $length characters
     */
    public function start(int $length): StringInterface;

    /**
     * Get the end of the string
     *
     * @param int $length Number of characters to get from the end
     * @return StringInterface A new string object containing the last $length characters
     */
    public function end(int $length): StringInterface;

    /**
     * Remove the specified number of characters from the beginning of the string
     *
     * @param int $length Number of characters to remove
     * @return StringInterface A new string object with characters removed from the beginning
     */
    public function removeStart(int $length): StringInterface;

    /**
     * Remove the specified number of characters from the end of the string
     *
     * @param int $length Number of characters to remove
     * @return StringInterface A new string object with characters removed from the end
     */
    public function removeEnd(int $length): StringInterface;

    /**
     * Get a substring between two strings
     *
     * @param string $start Starting delimiter
     * @param string $end Ending delimiter
     * @return StringInterface|null A new string object with the substring between delimiters or null if start delimiter not found
     */
    public function between(string $start, string $end): ?StringInterface;

    /**
     * Get a substring before the specified substring
     *
     * @param string $substring Substring to find
     * @param bool $inclusive Whether to include the found substring in the result
     * @return StringInterface|null A new string object with the substring before the specified substring or null if not found
     */
    public function before(string $substring, bool $inclusive = false): ?StringInterface;

    /**
     * Get a substring after the specified substring
     *
     * @param string $substring Substring to find
     * @param bool $inclusive Whether to include the found substring in the result
     * @return StringInterface|null A new string object with the substring after the specified substring or null if not found
     */
    public function after(string $substring, bool $inclusive = false): ?StringInterface;

    /**
     * Split the string into two parts
     *
     * @param string|int $delimiter Delimiter string or position
     * @return array<StringInterface> Array containing two string objects: [before, after]
     */
    public function split(string|int $delimiter): array;

    /**
     * Split the string by a delimiter
     *
     * @param string $delimiter Delimiter string
     * @param int $limit Maximum number of segments to return
     * @param bool $asStrings Whether to return results as strings instead of string objects
     * @return array<StringInterface|string> Array of string segments
     */
    public function explode(string $delimiter = '/', int $limit = PHP_INT_MAX, bool $asStrings = false): array;

    /**
     * Get an array of characters in the string
     *
     * @return array<string> Array of individual characters
     */
    public function toArray(): array;

    /**
     * Check if the string is empty
     *
     * @return bool True if the string is empty
     */
    public function isEmpty(): bool;

    /**
     * Check if the string is empty or contains only whitespace characters
     *
     * @return bool True if the string is empty or contains only whitespace
     */
    public function isBlank(): bool;

    /**
     * Check if the string contains only letters
     *
     * @return bool True if the string contains only letters
     */
    public function isAlpha(): bool;

    /**
     * Check if the string contains only digits
     *
     * @return bool True if the string contains only digits
     */
    public function isNumeric(): bool;

    /**
     * Convert the string to a number
     *
     * @return int|float Numeric representation of the string
     */
    public function toNumber(): int|float;

    /**
     * Check if the string contains only letters and digits
     *
     * @return bool True if the string contains only letters and digits
     */
    public function isAlphanumeric(): bool;

    /**
     * Check if the string is a hexadecimal number
     *
     * @return bool True if the string is a valid hexadecimal number
     */
    public function isHex(): bool;

    /**
     * Check if the string is serialized data
     *
     * @return bool True if the string is serialized data
     */
    public function isSerialized(): bool;

    /**
     * Check if the string is Base64 encoded
     *
     * @return bool True if the string is Base64 encoded
     */
    public function isBase64(): bool;

    /**
     * Check if the string is JSON
     *
     * @return bool True if the string is valid JSON
     */
    public function isJson(): bool;

    /**
     * Get the string as JSON format
     *
     * @param int $options JSON encoding options
     * @return string JSON representation of the string
     */
    public function toJson(int $options = 0): string;

    /**
     * Check if the string represents a boolean value
     *
     * @return bool True if the string represents a boolean value
     */
    public function isBoolean(): bool;

    /**
     * Convert the string to a boolean value
     *
     * @return bool|null Boolean value or null if the string does not represent a boolean
     */
    public function toBoolean(): ?bool;

    /**
     * Check if the string is a date
     *
     * @return bool True if the string is a valid date
     */
    public function isDate(): bool;

    /**
     * Convert the string to a date
     *
     * @param DateTimeZone|null $timezone Timezone to use for date parsing
     * @return DateTimeInterface|null DateTime object or null if the string is not a valid date
     */
    public function toDate(?DateTimeZone $timezone = null): ?DateTimeInterface;

    /**
     * Check if the string contains only uppercase characters
     *
     * @return bool True if the string contains only uppercase characters
     */
    public function isUpperCase(): bool;

    /**
     * Check if the string contains only lowercase characters
     *
     * @return bool True if the string contains only lowercase characters
     */
    public function isLowerCase(): bool;

    /**
     * Check if the string contains lowercase characters
     *
     * @return bool True if the string contains at least one lowercase character
     */
    public function hasLowerCase(): bool;

    /**
     * Check if the string contains digits
     *
     * @return bool True if the string contains at least one digit
     */
    public function hasDigits(): bool;

    /**
     * Check if the string contains uppercase characters
     *
     * @return bool True if the string contains at least one uppercase character
     */
    public function hasUpperCase(): bool;

    /**
     * Check if the string contains symbol characters
     *
     * @return bool True if the string contains at least one symbol character
     */
    public function hasSymbols(): bool;

    /**
     * Split the string into an array of lines
     *
     * @return array<string|StringInterface> Array of lines
     */
    public function lines(): array;

    /**
     * Split the string into an array of words
     *
     * @return array<string|StringInterface> Array of words
     */
    public function words(): array;

    /**
     * Check if the string matches a regular expression
     *
     * @param string $pattern Regular expression pattern
     * @param array<mixed>|null $matches If provided, will be filled with the matches
     * @param int $flags Regular expression flags
     * @param int $offset Start position for matching
     * @return bool True if the pattern matches the string
     */
    public function match(string $pattern, ?array &$matches = null, int $flags = 0, int $offset = 0): bool;

    /**
     * Check if the string matches a regular expression multiple times
     *
     * @param string $pattern Regular expression pattern
     * @param array<mixed>|null $matches If provided, will be filled with the matches
     * @param int $flags Regular expression flags
     * @param int $offset Start position for matching
     * @return bool True if the pattern matches the string
     */
    public function matchAll(string $pattern, ?array &$matches = null, int $flags = PREG_PATTERN_ORDER, int $offset = 0): bool;

    /**
     * Replace text in a string using regular expressions
     *
     * @param string|array<string> $pattern Pattern or array of patterns to search for
     * @param string|array<string> $replacement Replacement string or array of strings
     * @param int $limit Maximum replacements (-1 for unlimited)
     * @param int|null $count If provided, will be set to the number of replacements performed
     * @return StringInterface New string instance with replacements
     */
    public function replaceBy(string|array $pattern, string|array $replacement, int $limit = -1, ?int &$count = null): StringInterface;

    /**
     * Replace text using a callback function
     *
     * @param string|array<string> $pattern Pattern or array of patterns to search for
     * @param callable $callback Callback function for replacement
     * @param int $limit Maximum replacements (-1 for unlimited)
     * @param int|null $count If provided, will be set to the number of replacements
     * @return StringInterface New string instance with replacements
     */
    public function replaceCallback(string|array $pattern, callable $callback, int $limit = -1, ?int &$count = null): StringInterface;

    /**
     * Insert a substring at the specified position
     *
     * @param string $substring Substring to insert
     * @param int $position Position where to insert the substring
     * @return StringInterface New string with the substring inserted
     */
    public function insert(string $substring, int $position): StringInterface;

    /**
     * Wrap the string with the specified character
     *
     * @param string $char Character to add at the beginning and end of the string
     * @return StringInterface New string wrapped with the specified character
     */
    public function wrap(string $char): StringInterface;

    /**
     * Check if the string is wrapped with the specified character
     *
     * @param string $char Character to check for wrapping
     * @param bool $caseSensitive Whether to perform case-sensitive check
     * @return bool True if the string is wrapped with the specified character
     */
    public function isWrapped(string $char, bool $caseSensitive = false): bool;

    /**
     * Pad the string with characters to the specified length
     *
     * @param string $chars Characters for padding
     * @param int $length Target length
     * @param int $mode Padding mode (STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH)
     * @return StringInterface New string padded to the specified length
     */
    public function pad(string $chars, int $length, int $mode = STR_PAD_BOTH): StringInterface;

    /**
     * Pad the string with characters to the right up to the specified length
     *
     * @param string $chars Characters for padding
     * @param int $length Target length
     * @return StringInterface New string padded to the right
     */
    public function padEnd(string $chars, int $length): StringInterface;

    /**
     * Pad the string with characters to the left up to the specified length
     *
     * @param string $chars Characters for padding
     * @param int $length Target length
     * @return StringInterface New string padded to the left
     */
    public function padStart(string $chars, int $length): StringInterface;

    /**
     * Remove whitespace characters from the beginning and end of the string
     *
     * @param string $characters Characters to remove
     * @return StringInterface New string with whitespace removed
     */
    public function trim(string $characters = " \t\n\r\0\x0B"): StringInterface;

    /**
     * Remove whitespace characters from the beginning of the string
     *
     * @param string $characters Characters to remove
     * @return StringInterface New string with whitespace removed from the beginning
     */
    public function trimStart(string $characters = " \t\n\r\0\x0B"): StringInterface;

    /**
     * Remove whitespace characters from the end of the string
     *
     * @param string $characters Characters to remove
     * @return StringInterface New string with whitespace removed from the end
     */
    public function trimEnd(string $characters = " \t\n\r\0\x0B"): StringInterface;

    /**
     * Remove all whitespace characters
     *
     * @return StringInterface New string with all whitespace removed
     */
    public function stripWhitespace(): StringInterface;

    /**
     * Remove duplicate whitespace characters
     *
     * @return StringInterface New string with duplicate whitespace removed
     */
    public function collapseWhitespace(): StringInterface;

    /**
     * Remove the specified prefix from the string
     *
     * @param string $prefix Prefix to remove
     * @return StringInterface New string with the prefix removed if it exists
     */
    public function removePrefix(string $prefix): StringInterface;

    /**
     * Remove the specified suffix from the string
     *
     * @param string $suffix Suffix to remove
     * @return StringInterface New string with the suffix removed if it exists
     */
    public function removeSuffix(string $suffix): StringInterface;

    /**
     * Capitalize the first character of the string
     *
     * @return StringInterface New string with the first character capitalized
     */
    public function capitalize(): StringInterface;

    /**
     * Convert the first character of the string to lowercase
     *
     * @return StringInterface New string with the first character converted to lowercase
     */
    public function uncapitalize(): StringInterface;

    /**
     * Capitalize the first character of each word
     *
     * @return StringInterface New string with the first character of each word capitalized
     */
    public function capitalizeWords(): StringInterface;

    /**
     * Convert the string to lowercase
     *
     * @return StringInterface New string converted to lowercase
     */
    public function toLowerCase(): StringInterface;

    /**
     * Convert the string to uppercase
     *
     * @return StringInterface New string converted to uppercase
     */
    public function toUpperCase(): StringInterface;

    /**
     * Swap the case of each character
     *
     * @return StringInterface New string with the case of each character swapped
     */
    public function swapCase(): StringInterface;

    /**
     * Convert the string where each word starts with a capital letter
     *
     * @param array<string> $ignore Words to ignore during titleizing
     * @return StringInterface New string with words titleized
     */
    public function titleize(array $ignore = []): StringInterface;

    /**
     * Limit the string to the specified length
     *
     * @param int $length Maximum length
     * @param string $suffix String to append if truncated
     * @param bool $preserveWords Whether to preserve whole words
     * @return StringInterface New string truncated to the specified length
     */
    public function truncate(int $length = 200, string $suffix = '...', bool $preserveWords = false): StringInterface;

    /**
     * Convert the string to kebab-case format
     *
     * @return StringInterface New string in kebab-case format
     */
    public function toKebabCase(): StringInterface;

    /**
     * Convert the string to snake_case format
     *
     * @return StringInterface New string in snake_case format
     */
    public function toSnakeCase(): StringInterface;

    /**
     * Convert the string to camelCase format
     *
     * @return StringInterface New string in camelCase format
     */
    public function toCamelCase(): StringInterface;

    /**
     * Convert the string to PascalCase format
     *
     * @return StringInterface New string in PascalCase format
     */
    public function toPascalCase(): StringInterface;

    /**
     * Replace the first occurrence of a substring with a new substring
     *
     * @param string $search String to search for
     * @param string $replace String to replace with
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return StringInterface New string with the first occurrence replaced
     */
    public function replaceFirst(string $search, string $replace, bool $caseSensitive = true): StringInterface;

    /**
     * Replace the last occurrence of a substring with a new substring
     *
     * @param string $search String to search for
     * @param string $replace String to replace with
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return StringInterface New string with the last occurrence replaced
     */
    public function replaceLast(string $search, string $replace, bool $caseSensitive = true): StringInterface;

    /**
     * Replace all occurrences of a substring with a new substring
     *
     * @param string|array<string> $search String or array of strings to search for
     * @param string|array<string> $replace String or array of strings to replace with
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return StringInterface New string with all occurrences replaced
     */
    public function replace(string|array $search, string|array $replace, bool $caseSensitive = true): StringInterface;

    /**
     * Replace using a regular expression
     *
     * @param string|array<string> $pattern Pattern or array of patterns
     * @param string|array<string> $replacement Replacement string or array
     * @param int $limit Maximum number of replacements
     * @param int|null $count If provided, will be filled with the number of replacements
     * @return StringInterface New string with the pattern replaced
     */
    public function replacePattern(string|array $pattern, string|array $replacement, int $limit = -1, ?int &$count = null): StringInterface;

    /**
     * Add a prefix if it doesn't exist
     *
     * @param string $prefix Prefix to ensure
     * @return StringInterface New string with the prefix added if it doesn't exist
     */
    public function ensurePrefix(string $prefix): StringInterface;

    /**
     * Add a suffix if it doesn't exist
     *
     * @param string $suffix Suffix to ensure
     * @return StringInterface New string with the suffix added if it doesn't exist
     */
    public function ensureSuffix(string $suffix): StringInterface;

    /**
     * Add a substring to the end of the string
     *
     * @param string $suffix String to append
     * @return StringInterface New string with the suffix appended
     */
    public function append(string $suffix): StringInterface;

    /**
     * Add a substring to the beginning of the string
     *
     * @param string $prefix String to prepend
     * @return StringInterface New string with the prefix prepended
     */
    public function prepend(string $prefix): StringInterface;

    /**
     * Repeat the string the specified number of times
     *
     * @param int $times Number of times to repeat
     * @return StringInterface New string repeated the specified number of times
     * @throws \InvalidArgumentException If times is negative
     */
    public function repeat(int $times): StringInterface;

    /**
     * Reverse the string
     *
     * @return StringInterface New string with characters in reverse order
     */
    public function reverse(): StringInterface;

    /**
     * Shuffle the characters of the string
     *
     * @return StringInterface New string with characters shuffled
     */
    public function shuffle(): StringInterface;

    /**
     * Apply a function to the string
     *
     * @param callable $callback Function to apply to the string
     * @return StringInterface New string with the function applied
     */
    public function transform(callable $callback): StringInterface;

    /**
     * Replace tabs with spaces
     *
     * @param int $tabSize Number of spaces to replace each tab with
     * @return StringInterface New string with tabs replaced by spaces
     */
    public function tabsToSpaces(int $tabSize = 4): StringInterface;

    /**
     * Replace spaces with tabs
     *
     * @param int $tabSize Number of spaces that correspond to a tab
     * @return StringInterface New string with spaces replaced by tabs
     */
    public function spacesToTabs(int $tabSize = 4): StringInterface;

    /**
     * Format the string
     *
     * @param string ...$args Arguments to format the string with
     * @return StringInterface New string formatted with the arguments
     */
    public function format(string ...$args): StringInterface;

    /**
     * Get a hash of the string
     *
     * @param string $algorithm Hashing algorithm to use
     * @return string Hash of the string
     */
    public function hash(string $algorithm = 'sha256'): string;

    /**
     * Compare with another string
     *
     * @param string|Stringable $string String to compare with
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the strings are equal
     */
    public function equals(string|Stringable $string, bool $caseSensitive = true): bool;

    /**
     * Perform a function for each character of the string
     *
     * @param callable $callback Function to apply to each character
     * @return bool Result of the operation
     */
    public function each(callable $callback): bool;

    /**
     * Output the string
     *
     * @return void
     */
    public function print(): void;

    /**
     * Convert the string to ASCII
     *
     * @param string $language Source text language for specific transliteration rules
     * @param bool $strict Whether to strictly remove non-ASCII characters
     * @return StringInterface New string converted to ASCII
     */
    public function toAscii(string $language = '', bool $strict = false): StringInterface;

    /**
     * Create a lazy string instance
     *
     * @param callable $initializer Function that returns the string value
     * @return StringInterface Lazy-initialized string instance
     */
    public static function createLazy(callable $initializer): StringInterface;
}