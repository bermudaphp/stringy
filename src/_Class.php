<?php

namespace Bermuda\String;

final class _Class
{
    private const separator = '\\';
    
    /**
     * @param string $cls
     * @return array
     */
    public static function basename(string $cls): string
    {
        return self::split($cls)[1];
    }
    
    /**
     * @param string $cls
     * @return array
     */
    public static function namespace(string $cls): string
    {
        return self::split($cls)[0];
    }

    /**
     * @param string $cls
     * @return array
     */
    public static function split(string $cls): array
    {
        $segments = explode(self::separator, $cls);
        return [1 => array_pop($segments), 0 => implode(self::separator, $segments)];
    }
}
