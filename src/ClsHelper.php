<?php

namespace Bermuda\Stdlib;

final class ClsHelper
{
    private const separator = '\\';
    public const class_name_regex = '^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$';
    public const class_name_with_namespace_regex = '(^$)|(^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$)';
    
    /**
     * @param string $cls
     * @return string
     */
    public static function basename(string $cls): string
    {
        $result = self::split($cls);
        return is_array($result) ? $result[1] : $result;
    }
    
    /**
     * @param string $cls
     * @return ?string
     */
    public static function namespace(string $cls):? string
    {
        $result = self::split($cls);
        return is_array($result) ? $result[0] : null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function isValidName(string $name, bool $withNamespace = true): bool
    {
        if ($withNamespace) {
            return StrHelper::match('`'.self::class_name_with_namespace_regex.'`', $name);
        }

        return StrHelper::match('`'.self::class_name_regex.'`', $name);
    }

    /**
     * @param string $cls
     * @return array|string
     */
    public static function split(string $cls): array|string
    {
        $segments = explode(self::separator, $cls);

        if (count($segments) > 1) {
            return [1 => array_pop($segments), 0 => implode(self::separator, $segments)];
        }

        return $cls;
    }
}
