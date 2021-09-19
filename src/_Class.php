<?php

namespace Bermuda\String;

final class _Class
{
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
    public static function split(string $cls): array
    {
        return [implode('\\', $segments = explode('\\', $classname)), array_pop($result)];
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
}
