<?php


namespace Bermuda\String;


/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function str_contains(string $haystack, string $needle, int $offset = 0, bool $case_sensitive = false): bool
{
    return str_pos($needle, $offset, $case_sensitive) !== null;
}

/**
 * @param string $haystack
 * @return $needle
 */
function str_shuffle(string $haystack): string
{
    $chars = \str_split($haystack, 1);
    
    \usort($chars, static function (): int
    {
        if(($left = \random_int(0, 100)) == ($right = \random_int(0, 100)))
        {
            return 0;
        }

        return  $left > $right ? 1 : -1 ;
    });

    return \implode('', $chars);
}

/**
 * @param string $haystack
 * @param string $needle
 * @param int $offset
 * @param bool $caseSensitive
 * @return int|null
 */
function str_pos(string $haystack, string $needle, int $offset = 0, bool $case_sensitive = false):? int
{
    if((bool) $case_sensitive)
    {
        return @($i = \mb_stripos($haystack, $needle, $offset)) !== false ? $i : null ;
    }

    return @($i = \mb_strpos($haystack, $needle, $offset)) !== false ? $i : null ;
}

/**
 * @param string $subject
 * @param bool $caseSensitive
 * @return bool
 */
function str_equals(string $left, string $right, bool $case_sensitive = false): bool
{
    if($case_sensitive)
    {
        return \strcasecmp($left, $right) == 0 ;
    }

    return \strcmp($left, $right) == 0 ;
}

/**
 * @param string $x
 * @param string[] $y
 * @param bool $case_sensitive
 * @return bool
 */
public function equals_any(string $x, array $y, bool $case_sensitive = false): bool
{
    foreach ($y as $string)
    {
        if(str_equals((string) $string, $case_sensitive))
        {
            return true;
        }
    }
    
    return false;
}

/**
 * @param string $char
 * @param string $haystack
 * @return bool
 */
public function str_wrap(string $char, string $haystack): string
{
    return $char . $haystack . $char;
}

/**
 * @param string $char
 * @param string $haystack
 * @return bool
 */
public function str_rand(string $haystack, int $length): string
{
    $string = '';
    $pos = \mb_strlen($haystack);
    
    while($length--)
    {
        $string .= $haystack[\random_int(0, $pos)]
    }
    
    return $string;
}

/**
 * @param string $haystack
 * @param int $pos
 * @param int|null $length
 * @return string
 */
public function substring(string $haystack, int $pos, int $length = null): string
{
    return \mb_substr($haystack, $pos, $length);
}

/**
 * @param int $start
 * @param int $end
 * @return string
 */
function str_interval(string $haystack, int $start, int $end): string 
{
    $string = '';
    
    for ($string = ''; $start <= $end; $start++)
    {
        $string .= $haystack[$start];
    }

    return $string;
}
