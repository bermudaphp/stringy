<?php

namespace Bermuda\Stringy;

/**
 * Helper class for manipulating class names and namespaces
 */
final class ClsHelper
{
    /**
     * Namespace separator character
     */
    public const NAMESPACE_SEPARATOR = '\\';
    
    /**
     * Regular expression for validating a simple class name without namespace
     */
    public const CLASS_NAME_REGEX = '^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$';
    
    /**
     * Regular expression for validating a class name with optional namespace
     */
    public const CLASS_NAME_WITH_NAMESPACE_REGEX = '(^$)|(^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$)';
    
    /**
     * Get the class name without namespace
     *
     * @param string $cls Fully qualified class name
     * @return string Class name without namespace
     */
    public static function basename(string $cls): string
    {
        $result = self::split($cls);
        return is_array($result) ? $result[1] : $result;
    }
    
    /**
     * Get the namespace part of a fully qualified class name
     *
     * @param string $cls Fully qualified class name
     * @return ?string Namespace or null if class has no namespace
     */
    public static function namespace(string $cls): ?string
    {
        $result = self::split($cls);
        return is_array($result) ? $result[0] : null;
    }
    
    /**
     * Check if a string is a valid class name
     *
     * @param string $name String to check
     * @param bool $withNamespace Whether to allow namespace in the class name
     * @return bool True if the string is a valid class name
     */
    public static function isValidName(string $name, bool $withNamespace = true): bool
    {
        $pattern = $withNamespace 
            ? '`' . self::CLASS_NAME_WITH_NAMESPACE_REGEX . '`' 
            : '`' . self::CLASS_NAME_REGEX . '`';
            
        return preg_match($pattern, $name) === 1;
    }
    
    /**
     * Split a fully qualified class name into namespace and class name
     *
     * @param string $cls Fully qualified class name
     * @return array|string Array with keys [0 => namespace, 1 => class name] or string if no namespace
     */
    public static function split(string $cls): array|string
    {
        $segments = explode(self::NAMESPACE_SEPARATOR, $cls);
        if (count($segments) > 1) {
            return [0 => implode(self::NAMESPACE_SEPARATOR, array_slice($segments, 0, -1)), 1 => end($segments)];
        }
        return $cls;
    }
}
