<?php

namespace Bermuda\String;

use Exception;
use ForceUTF8\Encoding;
use RuntimeException;
use Throwable;

final class StringHelper
{
    private const numbers = '0123456789';
    private const symbols = '[~!@#$%^&*()_+{}/|\\<>?=]';
    private const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct()
    {
        throw new RuntimeException(sprintf('Class: %s not instantiable', __CLASS__));
    }

    /**
     * @param string $subject
     * @return bool
     */
    public static function isMultibyte(string $subject): bool
    {
        return strlen($subject) !== mb_strlen($subject);
    }
    
    public static function bytesTo(int $bytes, int $precision = 2): string
    {
        return round(pow(1024, ($base = log($bytes, 1024)) - floor($base)), $precision) .' '. ['', 'KB', 'MB', 'GB', 'TB'][floor($base)];
    }
    
    /**
     * @param int $length
     * @return string
     * @throws Exception
     */
    public static function alphanumeric(int $length): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     * @param string $subject
     * @return bool
     */
    public static function containsSymbols(string $subject): bool
    {
        return self::contains($subject, str_split(self::symbols));
    }
    
    /**
     * @param string $subject
     * @return bool
     */
    public static function containsNumbers(string $subject): bool
    {
        return self::contains($subject, str_split(self::numbers));
    }
    
    /**
     * @param $var
     * @return bool
     */
    public static function isStringable($var): bool
    {
        return is_string($var) || is_numeric($var) || $var instanceof \Stringable;
    }

    /**
     * @param string $subject
     * @return bool
     */
    public static function isBool(string $subject): bool
    {
        return self::equals($subject, [
                '1', '0', 'on', 'off', 'true', 'false', 'yes', 'no', 'y', 'n'
            ]) !== false;
    }
    
    /**
     * @param string $subject
     * @return bool
     */
    public static function toBool(string $subject): bool
    {
        return self::equals($subject, ['1', 'on', 'true', 'yes', 'y']);
    }

    /**
     * @param string $subject
     * @param string[] $any
     * @param bool $insensitive
     * @return bool
     */
    public static function equals(string $subject, string|array $any, bool $insensitive = true): bool
    {
        return str_equals($subject, $any, $insensitive);
    }

    /**
     * @param string $subject
     * @param string $char
     * @param bool $insensitive
     * @return bool
     */
    public static function isWrapped(string $subject, string $char, bool $insensitive = true): bool
    {
        if (empty($subject)) {
            return false;
        }

        return self::equals($subject[0], $char, $insensitive) &&
            self::equals($subject[mb_strlen($subject) - 1], $char, $insensitive);
    }

    /**
     * @param string $unwrapped
     * @param string $char
     * @return bool
     */
    public static function wrap(string $unwrapped, string $char): bool
    {
        return $char . $unwrapped . $char;
    }

    /**
     * @param string $encoding
     * @param string $subject
     * @return string
     */
    public static function encode(string $encoding, string $subject): string
    {
        return Encoding::encode($encoding, $subject);
    }

    /**
     * Generate random filename
     * @param string|null $ext
     * @param string|null $prefix
     * @return string
     */
    public static function filename(?string $ext = null, ?string $prefix = null): string
    {
        return self::uID(7, $prefix) . ($ext == null ? '' : '.' . ltrim($ext, '.'));
    }

    /**
     * @param int $length
     * @return string
     */
    public static function uID(int $length = 6, ?string $prefix = null): string
    {
        return ($prefix ?? '') . substr(bin2hex(random_bytes(ceil($length/2))), 0, $length);
    }

    /**
     * @param int $length
     * @param bool $useSymbols
     * @return string
     * @throws Exception
     */
    public static function password(int $length = 8, bool $useSymbols = true): string
    {
        if ($useSymbols) {
            if ($length < 3) {
                return self::random($length);
            }

            if ($length % 3 == 0) {
                return self::random($length = $length / 3, self::numbers) .
                    self::random($length, self::chars) .
                    self::random($length, self::symbols);
            }

            $pswd = self::random($c = ceil($length / 3), self::numbers). self::random($c, self::chars)
                . self::random($length - $c*2, self::symbols);

            return self::shuffle($pswd);
        }

        return self::random($length, self::numbers . self::chars);
    }

    /**
     * @param int $num
     * @param string|null $chars
     * @return string
     */
    public static function random(int $num, ?string $chars = null): string
    {
        $chars = $chars ?? self::numbers . self::chars . self::symbols;
        $max = mb_strlen($chars) - 1;

        $string = '';

        while ($num--) {
            $string .= $chars[random_int(0, $max)];
        }

        return $string;
    }

    /**
     * @param string $string
     * @return string
     * @throws Exception
     */
    public static function shuffle(string $string): string
    {
        $chars = str_split($string);

        usort($chars, static fn(): int => ($left = random_int(0, 100)) == ($right = random_int(0, 100))
            ? 0 : ($left > $right ? 1 : -1)
        );

        return implode('', $chars);
    }

    /**
     * @param string $subject
     * @param string|string[] $any
     * @param bool $insensitive
     * @return bool
     */
    public static function endsWith(string $subject, string|array $any, bool $insensitive = false): bool
    {
        return str_ends_with($subject, $any, $insensitive);
    }

    /**
     * @param string $subject
     * @param string|string[] $any
     * @param bool $insensitive
     * @return bool
     */
    public static function startsWith(string $subject, string|array $any, bool $insensitive = false): bool
    {
        return str_starts_with($subject, $any, $insensitive);
    }

    /**
     * @param string $subject
     * @return string
     */
    public static function detectEncoding(string $subject): string
    {
        return mb_detect_encoding($subject);
    }

    /**
     * @param string $content
     * @return bool
     */
    public static function isJson(string $content): bool
    {
        return Json::isJson($content);
    }
    
    /**
     * @param string $var
     * @return bool
     */
    public static function isDate(string $var): bool
    {
        try {
            new \DateTime($var);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param string $haystack
     * @param string[]|string $needle
     * @param bool $insensitive
     * @param int $offset
     * @return bool
     */
    public static function contains(string $haystack, array|string $needle, bool $insensitive = true, int $offset = 0): bool
    {
        return str_contains($haystack, $needle, $offset, $insensitive);
    }

    /**
     * @param string $haystack
     * @param string[] $needle
     * @param bool $insensitive
     * @param int $offset
     * @return bool
     */
    public static function containsAll(string $haystack, array $needle, bool $insensitive = true, int $offset = 0): bool
    {
        return str_contains_all($haystack, $needle, $offset, $insensitive);
    }

    /**
     * @param string $subject
     * @param string $separator
     * @param int $limit
     * @return array
     */
    public static function explode(string $subject, string $separator, int $limit = PHP_INT_MAX): array
    {
        return explode($separator, $subject, $limit);
    }

    /**
     * @param array $subject
     * @param string|array $glue
     * @return string
     */
    public static function implode(array $subject, string|array $glue = ','): string
    {
        return implode($glue, $subject);
    }

    /**
     * @return bool
     */
    public static function isAlphanumeric(string $subject): bool
    {
        return self::mbMatch('^[[:alnum:]]*$', $subject);
    }

    /**
     * @param string $pattern
     * @param string $subject
     * @return bool
     */
    public static function mbMatch(string $pattern, string $subject): bool
    {
        $old = mb_regex_encoding();
        mb_regex_encoding(self::detectEncoding($subject));
        $result = mb_ereg_match($pattern, $subject);
        mb_regex_encoding($old);

        return $result;
    }

    /**
     * @param string $subject
     * @param string $pattern
     * @param string $replacement
     * @return bool
     */
    public static function mbReplace(string $subject, string $pattern, string $replacement): bool
    {
        $old = mb_regex_encoding();
        mb_regex_encoding(self::detectEncoding($subject));
        $result = mb_ereg_replace($pattern, $replacement, $subject);
        mb_regex_encoding($old);

        return $result;
    }

    /**
     * @param string $subject
     * @return bool
     */
    public static function isBlank(string $subject): bool
    {
        return self::mbMatch('^[[:space:]]*$', $subject);
    }

    /**
     * @param string $subject
     * @return bool
     */
    public static function isHexadecimal(string $subject): bool
    {
        return self::mbMatch('^[[:xdigit:]]*$', $subject);
    }

    /**
     * @param string $subject
     * @return bool
     */
    public static function isLowerCase(string $subject): bool
    {
        return self::mbMatch('^[[:lower:]]*$', $subject);
    }

    /**
     * @param string $subject
     * @return bool
     */
    public static function isSerialized(string $subject): bool
    {
        return $subject === 'b:0;' || @unserialize($subject) !== false;
    }

    /**
     * @param string $subject
     * @return bool
     */
    public static function isBase64(string $subject): bool
    {
        return (base64_encode(base64_decode($subject, true)) === $subject);
    }

    /**
     * @return bool
     */
    public static function isUpperCase(string $subject): bool
    {
        return self::mbMatch('^[[:upper:]]*$', $subject);
    }

    /**
     * @param string $subject
     * @param string|null $encoding
     * @return int
     */
    public static function length(string $subject, ?string $encoding = null): int
    {
        return mb_strlen($subject, $encoding);
    }

    /**
     * @param string $pattern
     * @param string $subject
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return bool
     */
    public static function match(string $pattern, string $subject, array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return str_match($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * @param string $pattern
     * @param string $subject
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return int|null
     */
    public static function matchAll(string $pattern, string $subject, array &$matches = null, int $flags = PREG_PATTERN_ORDER, int $offset = 0):? int
    {
        return str_match_all($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * @param string $input
     * @param int $start
     * @param int $end
     * @return string
     */
    public static function interval(string $input, int $start, int $end): string
    {
        for ($string = ''; $end >= $start; $start++) $string .= $input[$start];
        return $string;
    }

    /**
     * @param string $subject
     * @return bool
     */
    public static function isAlpha(string $subject): bool
    {
        return self::mbMatch('^[[:alpha:]]*$', $subject);
    }
}
