<?php

namespace Bermuda\String;

use Exception;
use finfo;
use Throwable;

final class Str
{
    private const numbers = '0123456789';
    private const symbols = '[~`!@#$%^&*()}{?<>/|_=+-]';
    private const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * @param string $content
     * @return string
     */
    public static function mimeType(string $content): string
    {
        return self::finfoBuffer(FILEINFO_MIME_TYPE, $content);
    }

    private static function finfoBuffer(string $flags, string $content): string
    {
        return (new finfo($flags))->buffer($content);
    }

    /**
     * @param string $subject
     * @param string ...$segments
     */
    public static function prepend(string &$subject, string ...$segments): void
    {
        $subject = self::implode($segments, '') . $subject;
    }

    /**
     * @param string $haystack
     * @param string $glue
     * @return string
     */
    public static function implode(array $haystack, string $glue = ','): string
    {
        return implode($glue, $haystack);
    }

    /**
     * @param string $subject
     * @param string ...$segments
     */
    public static function append(string &$subject, string ...$segments): void
    {
        $subject .= self::implode($segments, '');
    }

    /**
     * @param string $content
     * @return string
     */
    public static function ext(string $content): string
    {
        return self::finfoBuffer(FILEINFO_EXTENSION, $content);
    }

    /**
     * @param string $string
     * @return bool
     */
    public static function isHTML(string $string): bool
    {
        return self::match("/<[^<]+>/", $string);
    }

    /**
     * @param string $regexp
     * @param string $subject
     * @return bool
     */
    public static function match(string $regexp, string $subject): bool
    {
        return preg_match($regexp, $subject) == 1;
    }

    /**
     * @param string $classname
     * @return array
     */
    public static function classname(string $classname): string
    {
        return self::classnameSplit($classname)[1];
    }

    /**
     * @param string $classname
     * @return array
     */
    public static function classnameSplit(string $classname): array
    {
        $result = explode('\\', $classname);
        $classname = array_pop($result);

        return [implode('\\', $result), $classname];
    }

    /**
     * Generate random filename
     * @param string|null $ext
     * @param string|null $prefix
     * @return string
     */
    public static function filename(?string $ext = null, ?string $prefix = null): string
    {
        return static::uID(7, $prefix) . ($ext == null ? '' : '.' . ltrim($ext, '.'));
    }

    /**
     * @param int $num
     * @param bool $useSymbols
     * @return string
     */
    public static function uID(int $num = 6, ?string $prefix = null): string
    {
        return ($prefix ?? '') . substr(bin2hex(random_bytes(ceil($num))), 0, $num);
    }

    /**
     * @param int $num
     * @param bool $useSymbols
     * @return string
     */
    public static function pswd(int $num = 8, bool $useSymbols = true): string
    {
        if ($useSymbols) {
            if ($num < 3) {
                return static::random($num);
            }

            if ($num % 3 == 0) {
                return static::random($num = $num / 3, static::numbers) .
                    static::random($num, static::chars) .
                    static::random($num, static::symbols);
            }

            $pswd = static::random($multi = ($round = ceil($num / 3)) * 2, static::numbers . static::chars)
                . static::random($num - $multi, static::symbols);

            return static::shuffle($pswd);
        }

        return static::random($num, static::numbers . static::chars);
    }

    /**
     * @param int $num
     * @param string|null $chars
     * @return string
     */
    public static function random(int $num, ?string $chars = null): string
    {
        $chars = $chars ?? static::numbers . static::chars . static::symbols;
        $max = strlen($chars) - 1;

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
        $chars = str_split($string, 1);

        usort($chars, static fn(): int => ($left = random_int(0, 100)) == ($right = random_int(0, 100))
            ? 0 : ($left > $right ? 1 : -1)
        );

        return implode('', $chars);
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
     * @param string $haystack
     * @param string[] $needle
     * @param bool $caseInsensitive
     * @param int $offset
     * @return bool
     */
    public static function containsAny(string $haystack, array $needle, bool $caseInsensitive = true, int $offset = 0): bool
    {
        foreach ($needle as $item) {
            if (self::contains($haystack, $item, $caseInsensitive, $offset)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $caseInsensitive
     * @param int $offset
     * @return bool
     */
    public static function contains(string $haystack, string $needle, bool $caseInsensitive = true, int $offset = 0): bool
    {
        try {
            return ($caseInsensitive ? mb_stripos($haystack, $needle, $offset) :
                    mb_strpos($haystack, $needle, $offset)) !== false;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param string $haystack
     * @param string $separator
     * @param int $limit
     * @return array
     */
    public static function explode(string $haystack, string $separator, int $limit = PHP_INT_MAX): array
    {
        return explode($separator, $haystack, $limit);
    }

    /**
     * @param string $x
     * @param string[] $any
     * @param bool $caseInsensitive
     * @return bool
     */
    public static function equalsAny(string $x, array $any, bool $caseInsensitive = true): bool
    {
        foreach ($any as $string) {
            if (static::equals($x, (string)$string, $caseInsensitive)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $x
     * @param string $y
     * @param bool $caseInsensitive
     * @return bool
     */
    public static function equals(string $x, string $y, bool $caseInsensitive = true): bool
    {
        return $caseInsensitive ? strcasecmp($x, $y) == 0 : strcmp($x, $y) == 0;
    }

    /**
     * @param string $subject
     * @param bool $multibyte
     * @param string|null $encoding
     * @return int
     */
    public static function length(string $subject, bool $multibyte = false, ?string $encoding = null): int
    {
        return $multibyte ? mb_strlen($subject, $encoding ?? mb_internal_encoding()) : strlen($subject);
    }

    /**
     * @param string $input
     * @param int $start
     * @param int $end
     * @return string
     */
    public static function interval(string $input, int $start, int $end): string
    {
        for ($string = ''; $end >= $start; $start++) {
            $string .= $input[$start];
        }

        return $string;
    }

    /**
     * @param int $length
     * @return IString
     */
    public function slice(int $length): IString
    {
        return $this->substring($length);
    }
}
