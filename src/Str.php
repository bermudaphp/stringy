<?php


namespace Bermuda\String;


final class Str
{
    private function __construct()
    {
    }

    private static string $numbers = '0123456789';
    private static string $symbols = '[~`!@#$%^&*()}{?<>/|_=+-]';
    private static string $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

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
     * @param string $ext
     * @return string
     */
    public static function filename(string $ext = ''): string
    {
        return static::random(7, static::chars . static::numbers) . $ext;
    }
    
     /**
     * @param int $num
     * @param bool $useSymbols
     * @return string
     */
    public static function pswd(int $num, bool $useSymbols = true): string
    {
        if ($useSymbols)
        {
            if ($num < 3)
            {
                return static::random($num);
            }
            
            if ($num % 3 == 0)
            {
                return static::random($num = $num / 3, static::numbers) .
                    static::random($num, static::chars) .
                    static::random($num, static::symbols);
            }
            
            return static::random($multi = ($round = ceil($num / 3)) * 2, static::numbers . static::chars) 
                . static::random($num - $multi, static::symbols); 
        }
        
        return static::random($num, static::numbers . static::chars);
    }
    
    /**
     * @param string $content
     * @return bool
     */
    public static function isJson(string $content): bool
    {
        try
        {
            json_decode($content, null, JSON_THROW_ON_ERROR);
        }
        
        catch(\JsonException $e)
        {
            return false;
        }
        
        return true;
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
        return $caseInsensitive ? mb_stripos($needle, $haystack, $offset) : mb_strpos($needle, $haystack, $offset);
    }

    /**
     * @param int $num
     * @param string|null $chars
     * @return string
     */
    public static function random(int $num, ?string $chars = null): string
    {
        $chars = $chars ?? static::$numbers . static::$chars . static::$symbols;
        $max = strlen($chars) - 1;

        $string = '';

        while ($num--)
        {
            $string .= $chars[random_int(0, $max)];
        }

        return $string;
    }

    /**
     * @param string $string
     * @return string
     * @throws \Exception
     */
    public static function shuffle(string $string): string
    {
        $chars = str_split($string, 1);

        usort($chars, static function (): int
        {
            if (($left = random_int(0, 100)) == ($right = random_int(0, 100)))
            {
                return 0;
            }

            return  $left > $right ? 1 : -1 ;
        });

        return implode('', $chars);
    }

    /**
     * @param string $x
     * @param string[] $any
     * @param bool $caseInsensitive
     * @return bool
     */
    public function equalsAny(string $x, array $any, bool $caseInsensitive = true): bool
    {
        foreach ($any as $string)
        {
            if (static::equals($x, (string) $string, $caseInsensitive))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $input
     * @param int $start
     * @param int $end
     * @return string
     */
    public static function interval(string $input, int $start, int $end): string
    {
        for ($string = ''; $end >= $start ; $start++)
        {
            $string .= $input[$start];
        }

        return $string;
    }
}
