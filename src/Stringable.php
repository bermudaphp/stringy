<?php


namespace Bermuda\String;


/**
 * interface Stringable
 * @package Bermuda\String
 */
interface Stringable
{
    /**
     * @param int $options
     * @return string
     * @throws \JsonException
     */
    public function toJson(int $options = 0): string ;
}
