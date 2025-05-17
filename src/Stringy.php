<?php

declare(strict_types=1);

namespace Bermuda\Stringy;

use InvalidArgumentException;
use Stringable;

/**
 * Facade for working with strings
 *
 * Provides static methods for creating and manipulating strings.
 * This class serves as the main entry point to the library and offers utility
 * functions that can be used without creating string instances.
 */
final class Stringy
{
    /**
     * Create an immutable string
     *
     * @param string|Stringable $string The initial string value
     * @param string $encoding The character encoding to use
     * @return Str New immutable string instance
     */
    public static function of(string|Stringable $string, string $encoding = 'UTF-8'): Str
    {
        return new Str($string, $encoding);
    }

    /**
     * Create a mutable string
     *
     * @param string|Stringable $string The initial string value
     * @param string $encoding The character encoding to use
     * @return StrMutable New mutable string instance
     */
    public static function mutable(string|Stringable $string, string $encoding = 'UTF-8'): StrMutable
    {
        return new StrMutable($string, $encoding);
    }

    /**
     * Check if the string is empty
     *
     * @param string|Stringable $string String to check
     * @return bool True if the string is empty
     */
    public static function isEmpty(string|Stringable $string): bool
    {
        return (string) $string === '';
    }

    /**
     * Check if the string is empty or contains only whitespace characters
     *
     * @param string|Stringable $string String to check
     * @return bool True if the string is empty or contains only whitespace
     */
    public static function isBlank(string|Stringable $string): bool
    {
        return preg_match('/^\s*$/u', (string) $string) === 1;
    }

    /**
     * Generate a random string
     *
     * @param int $length Length of the generated string
     * @param string|null $chars Characters to use for generation (default: alphanumeric)
     * @return string Random string of the specified length
     * @throws InvalidArgumentException If length is negative
     */
    public static function random(int $length = 16, ?string $chars = null): string
    {
        if ($length < 0) {
            throw new InvalidArgumentException('Length must be a non-negative integer');
        }

        $chars ??= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if ($length <= 0) {
            return '';
        }

        $result = '';
        $max = mb_strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= mb_substr($chars, random_int(0, $max), 1);
        }

        return $result;
    }

    /**
     * Check if the string contains lowercase characters
     *
     * @param string|Stringable $string String to check
     * @return bool True if the string contains at least one lowercase character
     */
    public static function hasLowerCase(string|Stringable $string): bool
    {
        return preg_match('/[a-z\p{Ll}]/u', (string)$string) === 1;
    }

    /**
     * Check if the string contains digits
     *
     * @param string|Stringable $string String to check
     * @return bool True if the string contains at least one digit
     */
    public static function hasDigits(string|Stringable $string): bool
    {
        return preg_match('/[0-9\p{N}]/u', (string)$string) === 1;
    }

    /**
     * Check if the string contains uppercase characters
     *
     * @param string|Stringable $string String to check
     * @return bool True if the string contains at least one uppercase character
     */
    public static function hasUpperCase(string|Stringable $string): bool
    {
        return preg_match('/[A-Z\p{Lu}]/u', (string)$string) === 1;
    }

    /**
     * Check if the string contains symbol characters (punctuation, math, currency, etc.)
     *
     * @param string|Stringable $string String to check
     * @return bool True if the string contains at least one symbol character
     */
    public static function hasSymbols(string|Stringable $string): bool
    {
        return preg_match('/[\p{P}\p{S}]/u', (string)$string) === 1;
    }

    /**
     * Split the string into an array of lines
     *
     * @param string|Stringable $string String to split
     * @return array<string> Array of lines
     */
    public static function lines(string|Stringable $string): array
    {
        $string = (string)$string;

        if ($string === '') {
            return [];
        }

        // Split by common line separators (LF, CRLF, CR)
        return preg_split('/\r\n|\n|\r/u', $string);
    }

    /**
     * Split the string into an array of words
     *
     * @param string|Stringable $string String to split
     * @return array<string> Array of words
     */
    public static function words(string|Stringable $string): array
    {
        $string = (string)$string;

        if ($string === '') {
            return [];
        }

        // Split by whitespace
        $words = preg_split('/\s+/u', trim($string));

        // Filter out empty values
        return array_filter($words, fn($word) => $word !== '');
    }

    /**
     * Convert the string for use in a URL
     *
     * @param string|Stringable $string String to convert
     * @param string $separator Separator to use between words
     * @param string $encoding Character encoding to use
     * @return string URL-friendly string
     */
    public static function slug(string|Stringable $string, string $separator = '-', string $encoding = 'UTF-8'): string
    {
        if (function_exists('transliterator_transliterate')) {
            $string = transliterator_transliterate('Any-Latin; Latin-ASCII', $string);
        }

        // Convert to lowercase
        $string = mb_strtolower((string)$string, $encoding);

        // Replace non-alphanumeric characters with the separator
        $string = preg_replace('/[^a-z0-9]/u', $separator, $string);

        // Replace multiple separators with a single one
        $string = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $string);

        // Remove leading and trailing separators
        return trim($string, $separator);
    }

    /**
     * Limit the string to the specified length
     *
     * @param string|Stringable $string String to truncate
     * @param int $length Maximum length
     * @param string $suffix String to append if truncated
     * @param bool $preserveWords Whether to preserve whole words
     * @param string $encoding Character encoding to use
     * @return string Truncated string
     */
    public static function truncate(
        string|Stringable $string,
        int $length,
        string $suffix = '...',
        bool $preserveWords = false,
        string $encoding = 'UTF-8'
    ): string {
        $string = (string)$string;
        $stringLength = mb_strlen($string, $encoding);

        if ($stringLength <= $length) {
            return $string;
        }

        $truncated = mb_substr($string, 0, $length, $encoding);

        if ($preserveWords) {
            $lastSpace = mb_strrpos($truncated, ' ', 0, $encoding);

            if ($lastSpace !== false) {
                $truncated = mb_substr($truncated, 0, $lastSpace, $encoding);
            }
        }

        return $truncated . $suffix;
    }

    /**
     * Find all occurrences of a substring
     *
     * @param string|Stringable $haystack String to search in
     * @param string $needle Substring to search for
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @param string $encoding Character encoding to use
     * @return array<int> Array of positions where the substring was found
     */
    public static function findAllPositions(
        string|Stringable $haystack,
        string $needle,
        bool $caseSensitive = true,
        string $encoding = 'UTF-8'
    ): array {
        $haystack = (string) $haystack;

        if ($needle === '') {
            return [];
        }

        $positions = [];
        $offset = 0;
        $haystackLength = mb_strlen($haystack, $encoding);

        while ($offset < $haystackLength) {
            if ($caseSensitive) {
                $pos = mb_strpos($haystack, $needle, $offset, $encoding);
            } else {
                $pos = mb_stripos($haystack, $needle, $offset, $encoding);
            }

            if ($pos === false) {
                break;
            }

            $positions[] = $pos;
            $offset = $pos + 1;
        }

        return $positions;
    }

    /**
     * Compare two strings using natural order sorting
     *
     * @param string|Stringable $a First string
     * @param string|Stringable $b Second string
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return int Comparison result (-1, 0, 1)
     */
    public static function compareNatural(
        string|Stringable $a,
        string|Stringable $b,
        bool $caseSensitive = true
    ): int {
        $a = (string) $a;
        $b = (string) $b;

        if ($caseSensitive) {
            return strnatcmp($a, $b);
        }

        return strnatcasecmp($a, $b);
    }

    /**
     * Check if the string is an email
     *
     * @param string|Stringable $string String to check
     * @return bool True if the string is a valid email
     */
    public static function isEmail(string|Stringable $string): bool
    {
        return filter_var((string) $string, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check if the string is a URL
     *
     * @param string|Stringable $string String to check
     * @return bool True if the string is a valid URL
     */
    public static function isUrl(string|Stringable $string): bool
    {
        return filter_var((string) $string, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check if the string is an IP address
     *
     * @param string|Stringable $string String to check
     * @return bool True if the string is a valid IP address
     */
    public static function isIp(string|Stringable $string): bool
    {
        return filter_var((string) $string, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Get a hash of the string
     *
     * @param string|Stringable $string String to hash
     * @param string $algorithm Hashing algorithm to use
     * @return string Hash of the string
     */
    public static function hash(string|Stringable $string, string $algorithm = 'sha256'): string
    {
        return hash($algorithm, (string) $string);
    }

    /**
     * Mask part of the string
     *
     * @param string|Stringable $string String to mask
     * @param int $start Start position (negative values count from the end)
     * @param int|null $length Length of the part to mask (null for all characters from start)
     * @param string $mask Character to use for masking
     * @return string Masked string
     */
    public static function mask(string|Stringable $string, int $start, ?int $length = null, string $mask = '*'): string
    {
        $string = (string) $string;
        $strLength = mb_strlen($string);

        if ($start < 0) {
            $start = $strLength + $start;
        }

        if ($start < 0) {
            $start = 0;
        }

        if ($start >= $strLength) {
            return $string;
        }

        if ($length === null) {
            $length = $strLength - $start;
        } else if ($length < 0) {
            $length = $strLength - $start + $length;
        }

        if ($length <= 0) {
            return $string;
        }

        $before = mb_substr($string, 0, $start);
        $masked = str_repeat($mask, min($length, $strLength - $start));
        $after = mb_substr($string, $start + $length);

        return $before . $masked . $after;
    }

    /**
     * Check if the string is multibyte
     *
     * @param string $string String to check
     * @return bool True if the string contains multibyte characters
     */
    public static function isMultibyte(string $string): bool
    {
        return strlen($string) !== mb_strlen($string);
    }

    /**
     * Check if the string starts with the specified substring
     *
     * @param string $haystack String to check
     * @param string|array<string> $needle Substring or array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string starts with any of the specified substrings
     */
    public static function startsWith(string $haystack, string|array $needle, bool $caseSensitive = true): bool
    {
        if (is_array($needle)) {
            foreach ($needle as $str) {
                if (self::startsWith($haystack, $str, $caseSensitive)) {
                    return true;
                }
            }

            return false;
        }

        if ($needle === '') {
            return true;
        }

        $haystackLength = mb_strlen($haystack);
        $needleLength = mb_strlen($needle);

        if ($needleLength > $haystackLength) {
            return false;
        }

        $start = mb_substr($haystack, 0, $needleLength);

        if (!$caseSensitive) {
            $start = mb_strtolower($start);
            $needle = mb_strtolower($needle);
        }

        return $start === $needle;
    }

    /**
     * Check if the string ends with the specified substring
     *
     * @param string $haystack String to check
     * @param string|array<string> $needle Substring or array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string ends with any of the specified substrings
     */
    public static function endsWith(string $haystack, string|array $needle, bool $caseSensitive = true): bool
    {
        if (is_array($needle)) {
            foreach ($needle as $str) {
                if (self::endsWith($haystack, $str, $caseSensitive)) {
                    return true;
                }
            }

            return false;
        }

        if ($needle === '') {
            return true;
        }

        $haystackLength = mb_strlen($haystack);
        $needleLength = mb_strlen($needle);

        if ($needleLength > $haystackLength) {
            return false;
        }

        $end = mb_substr($haystack, $haystackLength - $needleLength, $needleLength);

        if (!$caseSensitive) {
            $end = mb_strtolower($end);
            $needle = mb_strtolower($needle);
        }

        return $end === $needle;
    }

    /**
     * Check if the string contains the specified substring
     *
     * @param string $haystack String to check
     * @param string|array<string> $needle Substring or array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string contains any of the specified substrings
     */
    public static function contains(string $haystack, string|array $needle, bool $caseSensitive = true): bool
    {
        if (is_array($needle)) {
            foreach ($needle as $str) {
                if (self::contains($haystack, (string)$str, $caseSensitive)) {
                    return true;
                }
            }

            return false;
        }

        if ($needle === '') {
            return true;
        }

        if ($caseSensitive) {
            return mb_strpos($haystack, $needle) !== false;
        }

        return mb_stripos($haystack, $needle) !== false;
    }

    /**
     * Match a pattern against a string with full multibyte support
     * Returns true if matches, false if not, and throws exception on error
     *
     * @param string|Stringable $string String to match against
     * @param string $pattern Regular expression pattern
     * @param array<mixed>|null &$matches Array to store matches, if provided
     * @param int $flags Flags for matching (PREG_OFFSET_CAPTURE, PREG_UNMATCHED_AS_NULL)
     * @param int $offset Offset from which to start matching
     * @param string $encoding Character encoding
     * @return bool Whether the pattern matches the string
     * @throws \RuntimeException On regex error
     */
    public static function match(
        string|Stringable $string,
        string $pattern,
        ?array &$matches = null,
        int $flags = 0,
        int $offset = 0,
        string $encoding = 'UTF-8'
    ): bool {
        // Prepare regex parameters
        list($stringValue, $preparedPattern, $byteOffset) = self::prepareRegexParams($string, $pattern, $offset, $encoding);

        // Perform the match
        $result = @preg_match($preparedPattern, $stringValue, $matches, $flags, $byteOffset);

        // Handle errors
        self::handleRegexError($result);

        // Fix offsets if PREG_OFFSET_CAPTURE flag is used
        if ($result > 0 && $matches && ($flags & PREG_OFFSET_CAPTURE) && $encoding !== 'ASCII') {
            self::fixMatchOffsets($matches, $stringValue, $encoding);
        }

        return $result > 0;
    }

    /**
     * Match all occurrences of a pattern in a string with full multibyte support
     * Returns true if any matches found, false if none, and throws exception on error
     *
     * @param string|Stringable $string String to match against
     * @param string $pattern Regular expression pattern
     * @param array<mixed>|null &$matches Array to store matches, if provided
     * @param int $flags Flags for matching (PREG_OFFSET_CAPTURE, PREG_UNMATCHED_AS_NULL, PREG_PATTERN_ORDER, PREG_SET_ORDER)
     * @param int $offset Offset from which to start matching
     * @param string $encoding Character encoding
     * @return bool Whether any matches were found
     * @throws \RuntimeException On regex error
     */
    public static function matchAll(
        string|Stringable $string,
        string $pattern,
        ?array &$matches = null,
        int $flags = PREG_PATTERN_ORDER,
        int $offset = 0,
        string $encoding = 'UTF-8'
    ): bool {
        // Prepare regex parameters
        list($stringValue, $preparedPattern, $byteOffset) = self::prepareRegexParams($string, $pattern, $offset, $encoding);

        // Perform the match
        $result = @preg_match_all($preparedPattern, $stringValue, $matches, $flags, $byteOffset);

        // Handle errors
        self::handleRegexError($result);

        // Fix offsets if PREG_OFFSET_CAPTURE flag is used
        if ($result > 0 && $matches && ($flags & PREG_OFFSET_CAPTURE) && $encoding !== 'ASCII') {
            self::fixRegexMatchesOffsets($matches, $stringValue, $encoding, $flags);
        }

        return $result > 0;
    }

    /**
     * Prepare parameters for regex operations
     *
     * @param string|Stringable $string String to work with
     * @param string $pattern Regex pattern
     * @param int $offset Character offset
     * @param string $encoding Character encoding
     * @return array<mixed> Array containing [string, prepared pattern, byte offset]
     */
    private static function prepareRegexParams(
        string|Stringable $string,
        string $pattern,
        int $offset = 0,
        string $encoding = 'UTF-8'
    ): array {
        $stringValue = (string) $string;

        // Ensure pattern has Unicode flag
        $preparedPattern = self::ensureUnicodeFlag($pattern);

        // Convert character offset to byte offset for non-ASCII encodings
        if ($offset > 0 && $encoding !== 'ASCII') {
            $byteOffset = strlen(mb_substr($stringValue, 0, $offset, $encoding));
        } else {
            $byteOffset = $offset;
        }

        return [$stringValue, $preparedPattern, $byteOffset];
    }

    /**
     * Ensure a regex pattern has the Unicode 'u' flag
     *
     * @param string $pattern Regex pattern
     * @return string Pattern with Unicode flag
     */
    private static function ensureUnicodeFlag(string $pattern): string
    {
        // Skip empty patterns
        if (empty($pattern)) {
            return $pattern;
        }

        // Check if it's a valid regex and already has 'u' flag
        if (substr($pattern, 0, 1) === '/' && preg_match('/\/[a-zA-Z]*u[a-zA-Z]*$/', $pattern)) {
            return $pattern;
        }

        // If pattern is a valid regex with delimiters
        if (substr($pattern, 0, 1) === '/' && substr($pattern, -1) === '/') {
            return substr($pattern, 0, -1) . 'u' . substr($pattern, -1);
        }

        // For patterns without delimiters, add them with 'u' flag
        return '/' . $pattern . '/u';
    }

    /**
     * Check for regex errors and throw exception if necessary
     *
     * @param mixed $result Result from preg function
     * @throws \RuntimeException If an error occurred
     */
    private static function handleRegexError($result): void
    {
        if ($result === false) {
            $error = preg_last_error();
            throw new \RuntimeException(
                'Regex error: ' . self::getRegexErrorMessage($error),
                $error
            );
        }
    }

    /**
     * Fix offsets in matchAll results
     *
     * @param array<mixed> &$matches Matches array
     * @param string $string Original string
     * @param string $encoding Character encoding
     * @param int $flags PREG flags
     */
    private static function fixRegexMatchesOffsets(
        array &$matches,
        string $string,
        string $encoding,
        int $flags
    ): void {
        if ($flags & PREG_PATTERN_ORDER) {
            foreach ($matches as &$match) {
                self::fixMatchOffsets($match, $string, $encoding);
            }
        } elseif ($flags & PREG_SET_ORDER) {
            foreach ($matches as &$matchSet) {
                self::fixMatchOffsets($matchSet, $string, $encoding);
            }
        }
    }

    /**
     * Fix byte offsets in preg_match results to character offsets for multibyte strings
     *
     * @param array<mixed> &$matches The matches array returned by preg_match
     * @param string $string Original string
     * @param string $encoding Character encoding
     */
    private static function fixMatchOffsets(array &$matches, string $string, string $encoding): void
    {
        foreach ($matches as &$match) {
            if (is_array($match) && isset($match[1]) && is_int($match[1]) && $match[1] >= 0) {
                // Convert byte offset to character offset
                $match[1] = mb_strlen(substr($string, 0, $match[1]), $encoding);
            }
        }
    }

    /**
     * Get descriptive message for a regex error code
     *
     * @param int $errorCode PREG error code
     * @return string Human-readable error message
     */
    private static function getRegexErrorMessage(int $errorCode): string
    {
        return [
            PREG_NO_ERROR => 'No error',
            PREG_INTERNAL_ERROR => 'Internal PCRE error',
            PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit exceeded',
            PREG_RECURSION_LIMIT_ERROR => 'Recursion limit exceeded',
            PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 data',
            PREG_BAD_UTF8_OFFSET_ERROR => 'Offset doesn\'t correspond to valid UTF-8 sequence',
            PREG_JIT_STACKLIMIT_ERROR => 'JIT stack limit exceeded'
        ][$errorCode] ?? 'Unknown error';
    }

    /**
     * Replace text in a string using regular expressions with full multibyte support
     *
     * @param string|Stringable $string String to perform replacement on
     * @param string|array<string> $pattern Pattern or array of patterns to search for
     * @param string|array<string> $replacement Replacement string or array of strings
     * @param int $limit Maximum number of replacements to perform (-1 for no limit)
     * @param int|null &$count If provided, will be set to the number of replacements performed
     * @param string $encoding Character encoding
     * @return string String with replacements
     * @throws \RuntimeException On regex error
     */
    public static function replace(
        string|Stringable $string,
        string|array $pattern,
        string|array $replacement,
        int $limit = -1,
        ?int &$count = null,
        string $encoding = 'UTF-8'
    ): string {
        $stringValue = (string) $string;

        // Handle arrays of patterns
        if (is_array($pattern)) {
            $patterns = array_map(function($p) {
                return self::ensureUnicodeFlag($p);
            }, $pattern);
        } else {
            $patterns = self::ensureUnicodeFlag($pattern);
        }

        // Handle replacement references in non-ASCII encodings
        if (!is_array($replacement) && $encoding !== 'ASCII' && preg_match('/\$\d+/', $replacement)) {
            // Use a callback for proper handling of multibyte replacements
            return self::replaceWithCallback($stringValue, $patterns, $replacement, $limit, $count, $encoding);
        }

        // For simple cases or ASCII encoding, use standard preg_replace
        $result = @preg_replace($patterns, $replacement, $stringValue, $limit, $count);

        // Check for errors
        self::handleRegexError($result);

        return $result;
    }

    /**
     * Replace text using a callback function with full multibyte support
     *
     * @param string|Stringable $string String to perform replacement on
     * @param string|array<string> $pattern Pattern or array of patterns to search for
     * @param callable $callback Callback function for replacement
     * @param int $limit Maximum number of replacements to perform (-1 for no limit)
     * @param int|null &$count If provided, will be set to the number of replacements performed
     * @param string $encoding Character encoding
     * @return string String with replacements
     * @throws \RuntimeException On regex error
     */
    public static function replaceCallback(
        string|Stringable $string,
        string|array $pattern,
        callable $callback,
        int $limit = -1,
        ?int &$count = null,
        string $encoding = 'UTF-8'
    ): string {
        $stringValue = (string) $string;
        $count = 0;

        // Handle arrays of patterns
        if (is_array($pattern)) {
            // Ensure Unicode flag for all patterns
            $patterns = array_map(function($p) {
                return self::ensureUnicodeFlag($p);
            }, $pattern);

            // Process each pattern one by one
            $result = $stringValue;
            foreach ($patterns as $p) {
                $tempCount = 0;
                $result = self::processSinglePatternCallback($result, $p, $callback, $limit, $tempCount, $encoding);

                $count += $tempCount;

                // Stop if we've reached the limit
                if ($limit > 0 && $count >= $limit) {
                    break;
                }
            }

            return $result;
        } else {
            // Ensure Unicode flag for single pattern
            $preparedPattern = self::ensureUnicodeFlag($pattern);

            return self::processSinglePatternCallback($stringValue, $preparedPattern, $callback, $limit, $count, $encoding);
        }
    }

    /**
     * Process a single regex pattern with a callback for multibyte strings
     *
     * @param string $string Input string
     * @param string $pattern Regex pattern
     * @param callable $callback Callback function
     * @param int $limit Maximum replacements
     * @param int &$count Count of replacements
     * @param string $encoding Character encoding
     * @return string Result string
     * @throws \RuntimeException On regex error
     */
    private static function processSinglePatternCallback(
        string $string,
        string $pattern,
        callable $callback,
        int $limit,
        int &$count,
        string $encoding
    ): string {
        $offset = 0;
        $count = 0;
        $result = '';

        while (($limit < 0 || $count < $limit)) {
            $matches = [];
            $matchResult = @preg_match($pattern, $string, $matches, PREG_OFFSET_CAPTURE, $offset);

            // Check for errors
            self::handleRegexError($matchResult);

            // If no match found, break the loop
            if ($matchResult === 0) {
                break;
            }

            // Get the full match and its position
            $fullMatch = $matches[0][0];
            $position = $matches[0][1];

            // Convert byte offsets to character offsets for all capture groups
            if ($encoding !== 'ASCII') {
                foreach ($matches as &$match) {
                    if (is_array($match) && isset($match[1])) {
                        // Convert byte offset to character offset
                        $match[1] = mb_strlen(substr($string, 0, $match[1]), $encoding);
                    }
                }
            }

            // Add text before the match
            $result .= mb_substr($string, $offset, $position - $offset, $encoding);

            // Apply the callback
            $replacement = $callback($matches);

            // Add the replacement
            $result .= $replacement;

            // Update the offset to after the match
            $offset = $position + strlen($fullMatch);

            // Increment the count
            $count++;
        }

        // Add the remaining text
        if ($offset < mb_strlen($string, $encoding)) {
            $result .= mb_substr($string, $offset, null, $encoding);
        }

        return $result;
    }

    /**
     * Replace with backreferences in multibyte strings
     *
     * @param string $string Input string
     * @param string|array<string> $pattern Regex pattern
     * @param string $replacement Replacement with backreferences
     * @param int $limit Maximum replacements
     * @param int|null &$count Count of replacements
     * @param string $encoding Character encoding
     * @return string Result string
     * @throws \RuntimeException On regex error
     */
    private static function replaceWithCallback(
        string $string,
        string|array $pattern,
        string $replacement,
        int $limit,
        ?int &$count,
        string $encoding
    ): string {
        // Parse backreferences in the replacement
        $backrefs = [];
        preg_match_all('/\$(\d+)/', $replacement, $backrefs);

        // Create a replacement callback
        $callback = function($matches) use ($replacement, $backrefs) {
            $result = $replacement;

            // Replace each backreference
            foreach ($backrefs[1] as $ref) {
                $ref = (int)$ref;
                if (isset($matches[$ref])) {
                    $result = str_replace('$' . $ref, $matches[$ref][0], $result);
                }
            }

            return $result;
        };

        // Use the callback-based implementation
        return self::replaceCallback($string, $pattern, $callback, $limit, $count, $encoding);
    }

    /**
     * Check if the string contains all the specified substrings
     *
     * @param string $haystack String to check
     * @param array<string> $needles Array of substrings to check
     * @param bool $caseSensitive Whether to perform case-sensitive comparison
     * @return bool True if the string contains all the specified substrings
     */
    public static function containsAll(string $haystack, array $needles, bool $caseSensitive = true): bool
    {
        foreach ($needles as $needle) {
            if (!self::contains($haystack, (string)$needle, $caseSensitive)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Count the number of occurrences of a substring
     *
     * @param string $haystack String to search in
     * @param string $needle Substring to count
     * @param bool $caseSensitive Whether to perform case-sensitive counting
     * @param string $encoding Character encoding to use
     * @return int Number of occurrences
     */
    public static function countSubstring(string $haystack, string $needle, bool $caseSensitive = true, string $encoding = 'UTF-8'): int
    {
        if ($needle === '') {
            return 0;
        }

        if ($caseSensitive) {
            return mb_substr_count($haystack, $needle, $encoding);
        }

        $haystack = mb_strtolower($haystack, $encoding);
        $needle = mb_strtolower($needle, $encoding);

        return mb_substr_count($haystack, $needle, $encoding);
    }

    /**
     * Find the position of the first occurrence of a substring
     *
     * @param string $haystack String to search in
     * @param string $needle Substring to find
     * @param int $offset Start position for the search
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @param string $encoding Character encoding to use
     * @return int|null Position of the first occurrence or null if not found
     */
    public static function indexOf(string $haystack, string $needle, int $offset = 0, bool $caseSensitive = true, string $encoding = 'UTF-8'): ?int
    {
        if ($caseSensitive) {
            $pos = mb_strpos($haystack, $needle, $offset, $encoding);
        } else {
            $pos = mb_stripos($haystack, $needle, $offset, $encoding);
        }

        return $pos !== false ? $pos : null;
    }

    /**
     * Find the position of the last occurrence of a substring
     *
     * @param string $haystack String to search in
     * @param string $needle Substring to find
     * @param int $offset Start position for the search
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @param string $encoding Character encoding to use
     * @return int|null Position of the last occurrence or null if not found
     */
    public static function lastIndexOf(string $haystack, string $needle, int $offset = 0, bool $caseSensitive = true, string $encoding = 'UTF-8'): ?int
    {
        if ($caseSensitive) {
            $pos = mb_strrpos($haystack, $needle, $offset, $encoding);
        } else {
            $pos = mb_strripos($haystack, $needle, $offset, $encoding);
        }

        return $pos !== false ? $pos : null;
    }

    /**
     * Check if the string contains only letters
     *
     * @param string $string String to check
     * @return bool True if the string contains only letters
     */
    public static function isAlpha(string $string): bool
    {
        return preg_match('/^[[:alpha:]]*$/u', $string) === 1;
    }

    /**
     * Check if the string contains only letters and digits
     *
     * @param string $string String to check
     * @return bool True if the string contains only letters and digits
     */
    public static function isAlphanumeric(string $string): bool
    {
        return preg_match('/^[[:alnum:]]*$/u', $string) === 1;
    }

    /**
     * Check if the string is a hexadecimal number
     *
     * @param string $string String to check
     * @return bool True if the string is a valid hexadecimal number
     */
    public static function isHex(string $string): bool
    {
        return preg_match('/^[[:xdigit:]]*$/u', $string) === 1;
    }

    /**
     * Check if the string contains only lowercase characters
     *
     * @param string $string String to check
     * @return bool True if the string contains only lowercase characters
     */
    public static function isLowerCase(string $string): bool
    {
        return $string !== '' && $string === mb_strtolower($string);
    }

    /**
     * Check if the string contains only uppercase characters
     *
     * @param string $string String to check
     * @return bool True if the string contains only uppercase characters
     */
    public static function isUpperCase(string $string): bool
    {
        return $string !== '' && $string === mb_strtoupper($string);
    }

    /**
     * Check if the string is serialized data
     *
     * @param string $string String to check
     * @return bool True if the string is serialized data
     */
    public static function isSerialized(string $string): bool
    {
        return $string === 'b:0;' || @unserialize($string) !== false;
    }

    /**
     * Check if the string is Base64 encoded
     *
     * @param string $string String to check
     * @return bool True if the string is Base64 encoded
     */
    public static function isBase64(string $string): bool
    {
        if (empty($string) || strlen($string) % 4 !== 0 || !preg_match('/^[A-Za-z0-9\/+]*={0,2}$/', $string)) {
            return false;
        }

        $decoded = base64_decode($string, true);
        return $decoded !== false && base64_encode($decoded) === $string;
    }

    /**
     * Check if the string is JSON
     *
     * @param string $string String to check
     * @return bool True if the string is valid JSON
     */
    public static function isJson(string $string): bool
    {
        if ($string === '' || $string[0] !== '{' && $string[0] !== '[') {
            return false;
        }

        try {
            json_decode($string, false, 512, JSON_THROW_ON_ERROR);
            return true;
        } catch (\JsonException) {
            return false;
        }
    }

    /**
     * Check if the string represents a boolean value
     *
     * @param string $string String to check
     * @return bool True if the string represents a boolean value
     */
    public static function isBoolean(string $string): bool
    {
        $string = mb_strtolower($string);
        return in_array($string, ['true', 'false', '1', '0', 'yes', 'no', 'y', 'n', 'on', 'off'], true);
    }

    /**
     * Convert the string to a boolean value
     *
     * @param string $string String to convert
     * @return bool|null Boolean value or null if the string does not represent a boolean
     */
    public static function toBoolean(string $string): ?bool
    {
        $string = mb_strtolower($string);

        $trueValues = ['true', '1', 'yes', 'y', 'on'];
        if (in_array($string, $trueValues, true)) {
            return true;
        }

        $falseValues = ['false', '0', 'no', 'n', 'off'];
        if (in_array($string, $falseValues, true)) {
            return false;
        }

        return null;
    }

    /**
     * Check if the string is a date
     *
     * @param string $string String to check
     * @return bool True if the string is a valid date
     */
    public static function isDate(string $string): bool
    {
        try {
            new \DateTimeImmutable($string);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Swap the case of each character
     *
     * @param string $string String to process
     * @param string $encoding Character encoding to use
     * @return string String with the case of each character swapped
     */
    public static function swapCase(string $string, string $encoding = 'UTF-8'): string
    {
        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        $result = '';

        foreach ($chars as $char) {
            $lower = mb_strtolower($char, $encoding);
            $upper = mb_strtoupper($char, $encoding);

            if ($char === $lower && $lower !== $upper) {
                $result .= $upper;
            } else {
                $result .= $lower;
            }
        }

        return $result;
    }

    /**
     * Convert the string where each word starts with a capital letter
     *
     * @param string $string String to titleize
     * @param array<string> $ignore Words to ignore during titleizing
     * @return string Titleized string
     */
    public static function titleize(string $string, array $ignore = []): string
    {
        return preg_replace_callback(
            '/([\S]+)/u',
            static function ($match) use ($ignore) {
                if (in_array($match[0], $ignore, true)) {
                    return $match[0];
                }

                return ucfirst(mb_strtolower($match[0]));
            },
            $string
        );
    }

    /**
     * Shuffle the characters of the string
     *
     * @param string $string String to shuffle
     * @return string String with characters shuffled
     */
    public static function shuffle(string $string): string
    {
        if ($string === '') {
            return '';
        }

        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        shuffle($chars);

        return implode('', $chars);
    }

    /**
     * Get a substring
     *
     * @param string $string String to get substring from
     * @param int $start Start position (negative values count from the end)
     * @param int|null $length Length of the substring or null to get all characters from the start position
     * @param string $encoding Character encoding to use
     * @return string Substring
     */
    public static function substring(string $string, int $start, ?int $length = null, string $encoding = 'UTF-8'): string
    {
        return mb_substr($string, $start, $length, $encoding);
    }

    /**
     * Convert the string to snake_case format
     *
     * @param string $string String to convert
     * @param string $encoding Character encoding to use
     * @return string String in snake_case format
     */
    public static function toSnakeCase(string $string, string $encoding = 'UTF-8'): string
    {
        // Replace spaces with underscores
        $string = preg_replace('/\s+/u', '_', $string);

        // Insert underscores before capital letters
        $string = preg_replace('/(?<=\w)(?=[A-Z])/u', '_', $string);

        // Convert to lowercase
        $string = mb_strtolower($string, $encoding);

        // Remove duplicate underscores
        $string = preg_replace('/_+/', '_', $string);

        // Remove leading and trailing underscores
        return trim($string, '_');
    }

    /**
     * Compare the string with one of the passed strings
     *
     * @param string|Stringable $string String to check
     * @param array<string|Stringable> $values Array of strings to compare with
     * @param bool $caseSensitive Whether to use case-sensitive comparison
     * @return bool True if the string equals any of the specified values
     */
    public static function equalsAny(string|Stringable $string, array $values, bool $caseSensitive = true): bool
    {
        $string = (string) $string;

        if (!$caseSensitive) {
            $string = mb_strtolower($string);
        }

        foreach ($values as $value) {
            $valueStr = (string) $value;

            if (!$caseSensitive) {
                $valueStr = mb_strtolower($valueStr);
            }

            if ($string === $valueStr) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the string to ASCII
     *
     * @param string|Stringable $string String to convert
     * @param string $language Source text language for specific transliteration rules
     * @param bool $strict Whether to strictly remove non-ASCII characters
     * @return string ASCII string
     */
    public static function toAscii(string|Stringable $string, string $language = '', bool $strict = false): string
    {
        return Unicode::toAscii((string) $string, $language, $strict);
    }

    /**
     * Replace the first occurrence of a substring with a new substring
     *
     * @param string|Stringable $string String to process
     * @param string $search String to search for
     * @param string $replace String to replace with
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return string String with the first occurrence replaced
     */
    public static function replaceFirst(string|Stringable $string, string $search, string $replace, bool $caseSensitive = true): string
    {
        $string = (string) $string;

        if ($search === '') {
            return $string;
        }

        $pos = null;

        if ($caseSensitive) {
            $pos = mb_strpos($string, $search);
        } else {
            $pos = mb_stripos($string, $search);
        }

        if ($pos === false) {
            return $string;
        }

        $start = mb_substr($string, 0, $pos);
        $end = mb_substr($string, $pos + mb_strlen($search));

        return $start . $replace . $end;
    }

    /**
     * Replace the last occurrence of a substring with a new substring
     *
     * @param string|Stringable $string String to process
     * @param string $search String to search for
     * @param string $replace String to replace with
     * @param bool $caseSensitive Whether to perform case-sensitive search
     * @return string String with the last occurrence replaced
     */
    public static function replaceLast(string|Stringable $string, string $search, string $replace, bool $caseSensitive = true): string
    {
        $string = (string) $string;

        if ($search === '') {
            return $string;
        }

        $pos = null;

        if ($caseSensitive) {
            $pos = mb_strrpos($string, $search);
        } else {
            $pos = mb_strripos($string, $search);
        }

        if ($pos === false) {
            return $string;
        }

        $start = mb_substr($string, 0, $pos);
        $end = mb_substr($string, $pos + mb_strlen($search));

        return $start . $replace . $end;
    }

    /**
     * Filter to remove non-printable characters
     *
     * @param string $string String to filter
     * @return string String with non-printable characters removed
     */
    public static function removeNonPrintable(string $string): string
    {
        return preg_replace('/[[:^print:]]/', '', $string);
    }

    /**
     * Filter to remove control characters
     *
     * @param string $string String to filter
     * @return string String with control characters removed
     */
    public static function removeControlChars(string $string): string
    {
        return preg_replace('/[[:cntrl:]]/', '', $string);
    }

    /**
     * Filter to keep only digits
     *
     * @param string $string String to filter
     * @return string String with only digits
     */
    public static function digitsOnly(string $string): string
    {
        return preg_replace('/[^0-9]/', '', $string);
    }

    /**
     * Filter to keep only letters and digits
     *
     * @param string $string String to filter
     * @return string String with only letters and digits
     */
    public static function alphanumericOnly(string $string): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $string);
    }

    /**
     * Filter to normalize spaces
     * Replaces consecutive whitespace characters with a single space
     *
     * @param string $string String to normalize
     * @return string String with normalized spaces
     */
    public static function normalizeSpaces(string $string): string
    {
        return preg_replace('/\s+/', ' ', $string);
    }

    /**
     * Filter to remove invisible characters
     * Including null bytes, BOM markers, etc.
     *
     * @param string $string String to filter
     * @return string String with invisible characters removed
     */
    public static function removeInvisibleChars(string $string): string
    {
        $nonDisplayable = [
            // Null byte
            '/\x00/',
            // BOM markers
            '/\xEF\xBB\xBF/',
            '/\xFE\xFF/',
            '/\xFF\xFE/',
            // Soft hyphen
            '/\xC2\xAD/',
            // Zero width space
            '/\xE2\x80\x8B/',
            // Zero width non-joiner
            '/\xE2\x80\x8C/',
            // Zero width joiner
            '/\xE2\x80\x8D/',
            // Other directional markers
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
        ];

        return preg_replace($nonDisplayable, '', $string);
    }

    /**
     * Get the frequency of characters in the string
     *
     * @param string $string String to analyze
     * @param bool $caseSensitive Whether to perform case-sensitive analysis
     * @return array<string, int> Array of character frequencies
     */
    public static function getCharFrequency(string $string, bool $caseSensitive = true): array
    {
        if (!$caseSensitive) {
            $string = mb_strtolower($string, 'UTF-8');
        }

        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        return array_count_values($chars);
    }

    /**
     * Get the frequency of words in the string
     *
     * @param string $string String to analyze
     * @param bool $caseSensitive Whether to perform case-sensitive analysis
     * @return array<string, int> Array of word frequencies
     */
    public static function getWordFrequency(string $string, bool $caseSensitive = true): array
    {
        if (!$caseSensitive) {
            $string = mb_strtolower($string, 'UTF-8');
        }

        $words = preg_split('/\s+/u', trim($string));
        return array_count_values($words);
    }

    /**
     * Get statistics on character types
     *
     * @param string $string String to analyze
     * @return array<string, mixed> Array of character type statistics
     */
    public static function getCharTypeStats(string $string): array
    {
        $total = mb_strlen($string, 'UTF-8');
        $letters = preg_match_all('/\p{L}/u', $string);
        $digits = preg_match_all('/\p{N}/u', $string);
        $whitespace = preg_match_all('/\s/u', $string);
        $punctuation = preg_match_all('/\p{P}/u', $string);
        $symbols = preg_match_all('/\p{S}/u', $string);
        $other = $total - $letters - $digits - $whitespace - $punctuation - $symbols;

        return [
            'total' => $total,
            'letters' => $letters,
            'digits' => $digits,
            'whitespace' => $whitespace,
            'punctuation' => $punctuation,
            'symbols' => $symbols,
            'other' => $other,
            'percentages' => [
                'letters' => $total > 0 ? round(($letters / $total) * 100, 2) : 0,
                'digits' => $total > 0 ? round(($digits / $total) * 100, 2) : 0,
                'whitespace' => $total > 0 ? round(($whitespace / $total) * 100, 2) : 0,
                'punctuation' => $total > 0 ? round(($punctuation / $total) * 100, 2) : 0,
                'symbols' => $total > 0 ? round(($symbols / $total) * 100, 2) : 0,
                'other' => $total > 0 ? round(($other / $total) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Get the word count
     *
     * @param string $string String to analyze
     * @return int Number of words
     */
    public static function getWordCount(string $string): int
    {
        $words = preg_split('/\s+/u', trim($string));
        return count(array_filter($words, 'strlen'));
    }

    /**
     * Get the sentence count
     *
     * @param string $string String to analyze
     * @return int Number of sentences
     */
    public static function getSentenceCount(string $string): int
    {
        return preg_match_all('/[.!?]+(?=\s|$)/u', $string);
    }

    /**
     * Get the average word length
     *
     * @param string $string String to analyze
     * @return float Average word length
     */
    public static function getAverageWordLength(string $string): float
    {
        $words = preg_split('/\s+/u', trim($string));
        $words = array_filter($words, 'strlen');

        if (empty($words)) {
            return 0;
        }

        $totalLength = array_sum(array_map(function ($word) {
            return mb_strlen($word, 'UTF-8');
        }, $words));

        return round($totalLength / count($words), 2);
    }

    /**
     * Get the readability score of the text (Flesch Reading Ease)
     * https://en.wikipedia.org/wiki/Flesch%E2%80%93Kincaid_readability_tests
     *
     * A high score (> 60) means the text is easy to read.
     * A low score (< 50) means the text is difficult to read.
     *
     * @param string $string String to analyze
     * @return float Readability score
     */
    public static function getReadabilityScore(string $string): float
    {
        $wordCount = self::getWordCount($string);
        $sentenceCount = self::getSentenceCount($string);

        if ($wordCount === 0 || $sentenceCount === 0) {
            return 0;
        }

        // Syllable count (approximate for English)
        $syllableCount = 0;
        $words = preg_split('/\s+/u', trim($string));

        foreach ($words as $word) {
            // Simplified syllable count (not ideal)
            $word = strtolower($word);
            $word = preg_replace('/[^a-z]/', '', $word);

            if (empty($word)) {
                continue;
            }

            // Count vowels as an approximation of syllables
            $count = preg_match_all('/[aeiouy]+/i', $word);

            // Adjustments for typical endings
            if (preg_match('/[^aeiouy]e$/i', $word)) {
                $count--;
            }

            // Minimum 1 syllable for each word
            $syllableCount += max(1, $count);
        }

        // Flesch formula for readability
        $score = 206.835 - (1.015 * ($wordCount / $sentenceCount)) - (84.6 * ($syllableCount / $wordCount));

        return round(max(0, min(100, $score)), 2);
    }

    /**
     * Calculate the Levenshtein distance between two strings
     *
     * @param string $str1 First string
     * @param string $str2 Second string
     * @return int Levenshtein distance
     */
    public static function getLevenshteinDistance(string $str1, string $str2): int
    {
        return levenshtein($str1, $str2);
    }

    /**
     * Calculate the similarity between strings (0-1, where 1 is an exact match)
     *
     * @param string $str1 First string
     * @param string $str2 Second string
     * @return float Similarity value (0-1)
     */
    public static function getSimilarity(string $str1, string $str2): float
    {
        if ($str1 === $str2) {
            return 1.0;
        }

        $len1 = strlen($str1);
        $len2 = strlen($str2);

        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        $distance = levenshtein($str1, $str2);
        $maxLength = max($len1, $len2);

        return round(1 - ($distance / $maxLength), 2);
    }

    /**
     * Check if the string is a palindrome
     *
     * @param string $string String to check
     * @param bool $caseSensitive Whether to perform case-sensitive check
     * @param bool $ignoreSpaces Whether to ignore spaces
     * @return bool True if the string is a palindrome
     */
    public static function isPalindrome(string $string, bool $caseSensitive = false, bool $ignoreSpaces = true): bool
    {
        if ($ignoreSpaces) {
            $string = preg_replace('/\s+/u', '', $string);
        }

        if (!$caseSensitive) {
            $string = mb_strtolower($string, 'UTF-8');
        }

        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        return $chars === array_reverse($chars);
    }

    /**
     * Check if the string is an anagram of another string
     *
     * @param string $str1 First string
     * @param string $str2 Second string
     * @param bool $caseSensitive Whether to perform case-sensitive check
     * @param bool $ignoreSpaces Whether to ignore spaces
     * @return bool True if the strings are anagrams
     */
    public static function isAnagram(string $str1, string $str2, bool $caseSensitive = false, bool $ignoreSpaces = true): bool
    {
        if ($ignoreSpaces) {
            $str1 = preg_replace('/\s+/u', '', $str1);
            $str2 = preg_replace('/\s+/u', '', $str2);
        }

        if (!$caseSensitive) {
            $str1 = mb_strtolower($str1, 'UTF-8');
            $str2 = mb_strtolower($str2, 'UTF-8');
        }

        if (mb_strlen($str1, 'UTF-8') !== mb_strlen($str2, 'UTF-8')) {
            return false;
        }

        $chars1 = preg_split('//u', $str1, -1, PREG_SPLIT_NO_EMPTY);
        $chars2 = preg_split('//u', $str2, -1, PREG_SPLIT_NO_EMPTY);

        sort($chars1);
        sort($chars2);

        return $chars1 === $chars2;
    }

    /**
     * Convert a string with the specified delimiter
     *
     * @param string $string The input string
     * @param string $delimiter Delimiter to use
     * @param string $encoding Character encoding
     * @return string String converted with the specified delimiter
     */
    public static function delimit(string $string, string $delimiter, string $encoding = 'UTF-8'): string
    {
        // Сначала удаляем пробелы по краям строки
        $string = self::trim($string, " \t\n\r\0\x0B", $encoding);

        // Шаг 1: Вставляем разделитель перед заглавными буквами, не находящимися в начале слова
        $pattern = '/\B([A-Z])/u';
        $replacement = '-$1';
        $string = self::replace($string, $pattern, $replacement, -1, $count, $encoding);

        // Шаг 2: Переводим строку в нижний регистр
        $string = mb_strtolower($string, $encoding);

        // Шаг 3: Заменяем последовательности дефисов, подчеркиваний и пробелов на указанный разделитель
        $pattern = '/[-_\s]+/u';
        $string = self::replace($string, $pattern, $delimiter, -1, $count, $encoding);

        return $string;
    }

    /**
     * Remove whitespace characters from the beginning and end of the string
     *
     * @param string $string The input string
     * @param string $characters Characters to remove
     * @param string $encoding Character encoding
     * @return string Trimmed string
     */
    public static function trim(string $string, string $characters = " \t\n\r\0\x0B", string $encoding = 'UTF-8'): string
    {
        if (self::isMultibyte($string)) {
            $characters = preg_quote($characters, '/');
            $result = preg_replace('/^[' . $characters . ']+|[' . $characters . ']+$/u', '', $string);
            return $result ?? $string;
        } else {
            return trim($string, $characters);
        }
    }

    /**
     * Remove whitespace characters from the beginning of the string
     *
     * @param string $string The input string
     * @param string $characters Characters to remove
     * @param string $encoding Character encoding
     * @return string String with whitespace removed from the beginning
     */
    public static function trimStart(string $string, string $characters = " \t\n\r\0\x0B", string $encoding = 'UTF-8'): string
    {
        if (self::isMultibyte($string)) {
            $characters = preg_quote($characters, '/');
            $result = preg_replace('/^[' . $characters . ']+/u', '', $string);
            return $result ?? $string;
        } else {
            return ltrim($string, $characters);
        }
    }

    /**
     * Remove whitespace characters from the end of the string
     *
     * @param string $string The input string
     * @param string $characters Characters to remove
     * @param string $encoding Character encoding
     * @return string String with whitespace removed from the end
     */
    public static function trimEnd(string $string, string $characters = " \t\n\r\0\x0B", string $encoding = 'UTF-8'): string
    {
        if (self::isMultibyte($string)) {
            $characters = preg_quote($characters, '/');
            $result = preg_replace('/[' . $characters . ']+$/u', '', $string);
            return $result ?? $string;
        } else {
            return rtrim($string, $characters);
        }
    }
}