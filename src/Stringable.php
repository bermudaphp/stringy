<?php

namespace Bermuda\String;

/**
 * interface Stringable
 * @package Bermuda\String
 */
interface Stringable extends \Stringable
{
    /**
     * @return string
     */
    public function __toString(): string ;
}
