<?php

namespace Bermuda\String;

interface Jsonable
{
    /**
     * @param int $options
     * @return string
     * @throws \JsonException
     */
    public function toJson(int $options = 0): string ;
}
