<?php


namespace Bermuda;


/**
 * @param string $haystack
 * @param string $needle
 * @param bool $case_insensitive
 * @return bool
 */
function str_contains(string $haystack, string $needle, bool $case_insensitive = false, int $offset = 0): bool
{
    return Str::contains($haystack, $needle, $case_insensitive, $offset);
}

/**
 * @param string $string
 * @return string
 */
function str_camel_case(string $string): string 
{
        $replaced = '';

        foreach (explode('_', $string) as $i => $segment)
        {
            if ($i > 0)
            {
                $segment = ucfirst($segment);
            }

            $replaced .= $segment;
        }

        return $replaced;
    }

/**
 * @param string $string
 * @return bool
 */
function is_json(string $string): bool
{
    try
    {
        json_decode($string, true, 512, JSON_THROW_ON_ERROR);
    } 
        
    catch (\Throwable $e)
    {
        return false;
    }

    return true;
}

/**
 * @param string $string
 * @return string
 */
function str_shuffle(string $string): string
{
    return Str::shuffle($string);
}

/**
 * @param string $haystack
 * @param string $needle
 * @param int $offset
 * @param bool $case_insensitive
 * @return int|null
 */
function str_pos(string $haystack, string $needle, int $offset = 0, bool $case_insensitive = false):? int
{
    if ($case_insensitive)
    {
        return @($i = \mb_stripos($haystack, $needle, $offset)) !== false ? $i : null ;
    }

    return @($i = \mb_strpos($haystack, $needle, $offset)) !== false ? $i : null ;
}

/**
 * @param string $left
 * @param string $right
 * @param bool $case_insensitive
 * @return bool
 */
function str_equals(string $left, string $right, bool $case_insensitive = false): bool
{
    return Str::equals($left, $right, $case_insensitive);
}

/**
 * @param string $x
 * @param string[] $y
 * @param bool $case_insensitive
 * @return bool
 */
function str_equals_any(string $x, array $y, bool $case_insensitive = false): bool
{
    return Str::equalsAny($x, $y, $case_insensitive);
}

/**
 * @param string $char
 * @param string $unwrapped
 * @return string
 */
function str_wrap(string $char, string $unwrapped): string
{
    return $char . $unwrapped . $char;
}

/**
 * @param int $num
 * @param string $chars
 * @return string
 */
function str_rand(int $num, string $chars = null): string
{
    return Str::random($num, $chars);
}

/**
 * @param string $string
 * @param int $pos
 * @param int|null $length
 * @return string
 */
function substring(string $string, int $pos, int $length = null): string
{
    return \mb_substr($string, $pos, $length);
}

/**
 * @param int $start
 * @param int $end
 * @return string
 */
function str_interval(string $input, int $start, int $end): string 
{
    return Str::interval($input, $start, $end);
}
