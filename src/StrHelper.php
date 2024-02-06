<?php

namespace Bermuda\Stdlib;

use Exception;
use ForceUTF8\Encoding;
use RuntimeException;
use Throwable;

final class StrHelper
{
    private const numbers = '0123456789';
    private const symbols = '[~!@#$%^&*()_+{}/|\\<>?=]';
    private const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

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
     * @param string $var
     * @return bool
     */
    public static function isBool(string $var): bool
    {
        return self::isFalse($var) || self::isTrue($var);
    }

    /**
     * @param string $var
     * @return bool
     */
    public static function isFalse(string $var): bool
    {
        return self::equals($var, ['0', 'off', 'false', 'no', 'n']);
    }

    /**
     * @param string $var
     * @return bool
     */
    public static function isTrue(string $var): bool
    {
        return self::equals($var, ['1', 'on', 'true', 'yes', 'y']);
    }

    /**
     * @param string $var
     * @param int $length
     * @param bool $remove
     * @return string
     */
    public static function start(string $var, int $length, bool $remove = false): string
    {
        if ($remove) return mb_substr($var, $length);
        return mb_substr($var, 0, $length);
    }

    /**
     * @param string $var
     * @param int $length
     * @return string
     */
    public static function slice(string $var, int $length): string
    {
        return mb_substr($var, $length);
    }

    /**
     * @param string $var
     * @param int $length
     * @param bool $remove
     * @return string
     */
    public static function end(string $var, int $length, bool $remove = false): string
    {
        if ($remove) return static::start($var, mb_strlen($var) - abs($length));
        return mb_substr($var, -$length = abs($length), $length);
    }

    /**
     * @param string $var
     * @return bool
     */
    public static function toBool(string $var):? bool
    {
        if (!static::isBool($var)) return null;
        if (static::isTrue($var)) return true;
        if (static::isFalse($var)) return false;
    }

    /**
     * @param string $haystack
     * @param string[] $needle
     * @param bool $ignoreCase
     * @return bool
     */
    public static function equals(string $haystack, string|array $needle, bool $ignoreCase = true): bool
    {
        if (is_string($needle)) {
            return $ignoreCase ? strtolower($needle) === strtolower($haystack) : $needle === $haystack ;
        }
        
        foreach ($needle as $value) {
            if (self::equals($haystack, (string) $value, $ignoreCase)) return true;
        }
        
        return false;
    }

    /**
     * @param string $subject
     * @param string $char
     * @param bool $ignoreCase
     * @return bool
     */
    public static function isWrapped(string $subject, string $char, bool $ignoreCase = true): bool
    {
        if (empty($subject)) {
            return false;
        }

        return self::equals($subject[0], $char, $ignoreCase) &&
            self::equals($subject[mb_strlen($subject) - 1], $char, $ignoreCase);
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
     * @param string $ext
     * @param string $prefix
     * @return string
     * @throws Exception
     */
    public static function filename(string $ext = '', string $prefix = ''): string
    {
        return sprintf('%s%s.%s', $prefix, self::hex(7), ltrim($ext, '.'));
    }

    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function hex(int $length): string
    {
        return substr(bin2hex(random_bytes(ceil($length/2))), 0, $length);
    }

    /**
     * @param int $length
     * @param bool $useSymbols
     * @param bool $useNumbers
     * @return string
     * @throws \Exception
     */
    public static function pswd(int $length = 8, bool $useSymbols = true, bool $useNumbers = true): string
    {
        if ($useSymbols) {
            if ($length < 3) return self::random($length);

            if ($length % 3 == 0) {
                if ($useNumbers) {
                    $pswd = self::random($length = $length / 3, self::symbols) .
                        self::random($length*2, self::chars);

                    return self::shuffle($pswd);
                }

                $pswd = self::random($length = $length / 3, self::numbers) .
                    self::random($length, self::chars) .
                    self::random($length, self::symbols);

                return self::shuffle($pswd);
            }

            if ($useNumbers) {
                $pswd = self::random($c = ceil($length / 3), self::numbers). self::random($c, self::chars)
                    . self::random($length - $c*2, self::symbols);

                return self::shuffle($pswd);
            }

            $pswd = self::random(($c = ceil($length / 3))*2, self::chars)
                . self::random($length - $c*2, self::symbols);

            return self::shuffle($pswd);
        }

        return self::random($length, self::numbers . self::chars);
    }

    /**
     * @param int $length
     * @param string|null $chars
     * @return string
     * @throws Exception
     */
    public static function random(int $length, ?string $chars = null): string
    {
        $chars = $chars ?? self::numbers . self::chars . self::symbols;
        $chars = self::shuffle($chars);

        return self::slice($chars, $length);
    }

    /**
     * @param string $string
     * @return string
     * @throws Exception
     */
    public static function shuffle(string $string): string
    {
        return str_shuffle($string);
    }

    /**
     * @param string $haystack
     * @param string|string[] $needle
     * @param bool $ignoreCase
     * @return bool
     */
    public static function endsWith(string $haystack, string|array $needle, bool $ignoreCase = false): bool
    {
        if (is_string($needle)) {
            return $ignoreCase ? str_ends_with(strtolower($haystack), strtolower($needle))
                : str_ends_with($haystack, $needle);
        }

        foreach ($needle as $value) {
            if (self::endsWith($haystack, (string) $value, $ignoreCase)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $haystack
     * @param string|string[] $needle
     * @param bool $ignoreCase
     * @return bool
     */
    public static function startsWith(string $haystack, string|array $needle, bool $ignoreCase = false): bool
    {
        if (is_string($needle)) {
            return $ignoreCase ? str_starts_with(strtolower($haystack), strtolower($needle))
                : str_starts_with($haystack, $needle);
        }

        foreach ($needle as $value) {
            if (self::startsWith($haystack, (string) $value, $ignoreCase)) {
                return true;
            }
        }

        return false;
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
        try {
            json_decode($content, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        return true;
    }

    /**
     * @param string $var
     * @return bool
     */
    public static function isDate(string $var): bool
    {
        try {
            new \DateTime($var);
        } catch (\Throwable) {
            return false;
        }

        return true;
    }

    /**
     * @param string $haystack
     * @param string[]|string $needle
     * @param bool $ignoreCase
     * @return bool
     */
    public static function contains(string $haystack, array|string $needle, bool $ignoreCase = true): bool
    {
        if (is_string($needle)) {
            return $ignoreCase ? str_contains(strtolower($haystack), strtolower($needle))
                : str_contains($haystack, $needle);
        }

        foreach ($needle as $value) {
            if (self::contains($haystack, (string) $value, $ignoreCase)) return true;
        }

        return false;
    }

    /**
     * @param string $haystack
     * @param string[] $needle
     * @param bool $ignoreCase
     * @return bool
     */
    public static function containsAll(string $haystack, array $needle, bool $ignoreCase = true): bool
    {
        foreach ($needle as $value) {
            if (!self::contains($haystack, (string) $value, $ignoreCase)) {
                return false;
            }
        }

        return true;
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
     * @param array $segments
     * @param string $glue
     * @return string
     */
    public static function implode(array $segments, string $glue = ','): string
    {
        return implode($glue, $segments);
    }

    /**
     * @param string $subject
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
     * @param string $subject
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
        return preg_match($pattern, $subject, $matches, $flags, $offset) === 1;
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

    public static function swapCase(string $var): string
    {
        return mb_strtolower($var) ^ mb_strtoupper($var) ^ $var;
    }

    public static function titleize(string $var, string|array $ignore = []): string
    {
        $ignore = is_string($ignore) ? [$ignore] : $ignore;

        return preg_replace_callback(
            '/([\S]+)/u',
            static function ($match) use ($ignore) {
                if ($ignore !== [] && in_array($match[0], $ignore)) {
                    return $match[0];
                }

                return ucfirst(strtolower($match[0]));
            },
            $var
        );
    }
}
