<?php

namespace Bermuda\String;

final class _Class
{
    private const separator = '\\';
    public const class_name_regex = '^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$';
    public const class_name_with_namespace_regex = '(^$)|(^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$)';
    
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
     * @param string $name
     * @return bool
     */
    public static function isValidName(string $name, bool $withNamespace = true): bool
    {
        if ($withNamespace) {
            return str_match('`'.self::class_name_with_namespace_regex.'`', $name);
        }

        return str_match('`'.self::class_name_regex.'`', $name);
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
