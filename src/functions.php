<?php


namespace Bermuda\String;


/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function str_contains(string $haystack, string $needle): bool
{
    return mb_strpos($haystack, $needle) !== false;
}
