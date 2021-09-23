<?php

namespace Bermuda\String;

interface Jsonable
{
    /**
     * @param int $options
     * @param int $depth
     * @return string
     * @throws \JsonException
     */
    public function toJson(int $options = 0, int $depth = 512): string ;
}
