<?php

namespace Bermuda\String;

use Traversable;
use ForceUTF8\Encoding;
use Bermuda\Iterator\StringIterator;

/**
 * Class Stringy
 * @package Bermuda\Stringy
 */
final class Stringy implements StringInterface
{
    private string $string;
    private string $encoding;

    public function __construct(string $string = '', string $encoding = null)
    {
        $this->string = $string;
        $this->encoding = $encoding ?? mb_internal_encoding();
    }

    public function copy(): StringInterface
    {
        // TODO: Implement copy() method.
    }

    /**
     * @return string
     */
    public function __toString(): string 
    {
        return $this->string;
    }

    /**
     * dump string and die
     */
    public function dd(): void
    {
        dd($this->string);
    }

    /**
     * @return array
     */
    public function toArray(): array 
    {
        return [$this];
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(): StringIterator
    {
        return new StringIterator($this->string);
    }

    /**
     * @return string
     */
    public function encoding(): string 
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     * @return Stringy
     */
    public function encode(string $encoding): StringInterface
    {
        if (Str::equals($encoding, 'UTF-8', true))
        {
            $copy = clone $this;
            $copy->string = Encoding::toUTF8($this->string);
            $copy->encoding = 'UTF-8';
            
            return $copy;
        }

        if (Str::equalsAny($encoding, ['ISO-8859-1', 'Windows-1251'], true))
        {
            $copy = clone $this;
            $copy->string = Encoding::toWin1252($this->string);
            $copy->encoding = 'ISO-8859-1';
            
            return $copy;
        }

        if (!Str::equalsAny($encoding, mb_list_encodings()))
        {
            throw new \RuntimeException('Invalid encoding: ' . $encoding);
        }
        
        $copy = clone $this;
        $copy->string = mb_convert_encoding($this->string, $encoding, $this->encoding);
        $copy->encoding = $encoding;
        
        return $copy;
    }

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseInsensitive
     * @return int|null
     */
    public function indexOf(string $needle, int $offset = 0, bool $caseInsensitive = false):? int
    {
        if ($caseInsensitive)
        {
            return @($i = mb_stripos($this->string, $needle, $offset)) !== false ? $i : null ;
        }

        return @($i = mb_strpos($this->string, $needle, $offset)) !== false ? $i : null ;
    }

    /**
     * @param string $delim
     * @return StringInterface[]|string[]
     */
    public function explode(string $delim = '/', int $limit = PHP_INT_MAX, bool $asString = false): array
    {
        if ($asString)
        {
            return explode($delim, $this->string, $limit);
        }
        
        return array_map(static function ($string)
        {
            return new self($string, $this->encoding);
        }, explode($delim, $this->string, $limit));
    }

    /**
     * @return StringInterface
     */
    public function ucFirst(): StringInterface
    {
        $copy = clone $this;
        $copy->string = ucfirst($this->string);

        return $copy;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->length();
    }

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseInsensitive
     * @return bool
     */
    public function contains(string $needle, int $offset = 0, bool $caseInsensitive = false): bool
    {
       return $this->indexOf($needle, $offset, $caseInsensitive) !== null;
    }

    /**
     * @param int $length
     * @param string $substring
     * @return Stringy
     */
    public function truncate(int $length = 200, string $substring = '...'): StringInterface
    {
        return $this->start($length)->string .= $substring;
    }

    /**
     * @return int
     */
    public function length(): int 
    {
        return mb_strlen($this->string);
    }

    /**
     * @return int
     */
    public function getBytes(): int 
    {
        return strlen($this->string);
    }

    /**
     * @param string $needle
     * @param bool $requireNeedle
     * @param bool $caseInsensitive
     * @return StringInterface|null
     */
    public function before(string $needle, bool $requireNeedle = true, bool $caseInsensitive = false):? StringInterface
    {
        if (($index = $this->indexOf($needle, 0, $caseInsensitive)) !== null)
        {
            return $this->start($requireNeedle ? $index + 1 : $index);
        }

        return null ;
    }

    /**
     * @param string $needle
     * @param bool $requireNeedle
     * @param bool $caseInsensitive
     * @return Stringy|null
     */
    public function after(string $needle, bool $requireNeedle = true, bool $caseInsensitive = false):? StringInterface
    {
        if (($index = $this->indexOf($needle, 0, $caseInsensitive)) !== null)
        {
            return $this->substring($requireNeedle ? $index : $index + (new self($needle))->length());
        }

        return null ;
    }

    /**
     * @param string $algorithm
     * @return static
     */
    public function hash(string $algorithm = 'sha512'): string 
    {
        return hash($algorithm, $this->string);
    }

    /**
     * @param string $charlist
     * @return StringInterface
     */
    public function trim(string $charlist = ' '): StringInterface
    {
        $copy = clone $this;
        $copy->string = trim($this->string, $charlist);

        return $copy;
    }

    /**
     * @param string $charlist
     * @return StringInterface
     */
    public function ltrim(string $charlist = ' '): StringInterface
    {
        $copy = clone $this;
        $copy->string = ltrim($this->string, $charlist);

        return $copy;
    }

    /**
     * @param string $charlist
     * @return StringInterface
     */
    public function rtrim(string $charlist = ' '): StringInterface
    {
        $copy = clone $this;
        $copy->string = rtrim($this->string, $charlist);

        return $copy;
    }

    /**
     * @param string|array $search
     * @param string|array $replace
     * @return StringInterface
     */
    public function replace($search, $replace): StringInterface
    {
        $copy = clone $this;
        $copy->string = str_replace($search, $replace, $this);

        return $copy;
    }

    /**
     * @param string $string
     * @return StringInterface
     */
    public function prepend(string $string): StringInterface
    {
        $copy = clone $this;
        $copy->string = $string . $this->string;

        return $copy;
    }

    /**
     * @param string $string
     * @return StringInterface
     */
    public function append(string $string): StringInterface
    {
        $copy = clone $this;
        $copy->string .= $string;

        return $copy;
    }

    /**
     * @param string $subject
     * @param bool $caseInsensitive
     * @return bool
     */
    public function equals(string $subject, bool $caseInsensitive = false): bool
    {
        return Str::equals($this->string, $subject, $caseInsensitive);
    }

    /**
     * @param string[] $subject
     * @param bool $caseInsensitive
     * @return bool
     */
    public function equalsAny(array $subject, bool $caseInsensitive = false): bool
    {
        return Str::equalsAny($this->string, $subject, $caseInsensitive);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->string);
    }

    /**
     * @param int $index
     * @return StringInterface
     * @throws \RuntimeException
     */
    public function index(int $index): StringInterface 
    {
        if (!$this->has($index))
        {
            throw new \RuntimeException('Invalid offset');
        }

        $copy = clone $this;
        $copy->string = $this->string[$index];

        return $copy;
    }

    /**
     * @param int $start
     * @param int $end
     * @return StringInterface
     */
    public function interval(int $start, int $end): StringInterface 
    {
        $copy = clone $this;
        $copy->string = Str::interval($this->string, $start, $end);

        return $copy;
    }

    /**
     * @param int $index
     * @return bool
     */
    public function has(int $index): bool 
    {
        return abs($index) <= $this->lastIndex();
    }

    /**
     * @return StringInterface|null
     */
    public function first():? StringInterface 
    {
        return $this->index(0);
    }

    /**
     * @return StringInterface|null
     */
    public function last():? StringInterface
    {
        return $this->index($this->lastIndex());
    }

    /**
     * @param string $char
     * @return StringInterface
     */
    public function wrap(string $char): StringInterface
    {
        return $this->prepend($char)->string .= $char;
    }

    /**
     * @param string|null $char
     * @return bool
     */
    public function isWrapped(string $char): bool
    {
        return ($char = new self($char))->first()->equals($char) 
            && $char->last()->equals($char);
    }

    /**
     * @param int $pos
     * @return self[]
     */
    public function break(int $pos): array 
    {
        return [$this->start($pos), $this->substring($pos)];
    }

    /**
     * @param int $pos
     * @param int|null $length
     * @return StringInterface
     */
    public function substring(int $pos, int $length = null): StringInterface
    {
        $copy = clone $this;
        $copy->string = mb_substr($this->string, $pos, $length);

        return $copy;
    }

    /**
     * @param int $length
     * @return StringInterface
     */
    public function start(int $length): StringInterface 
    {
        return $this->substring(0, $length);
    }

    /**
     * @param int $length
     * @return StringInterface
     */
    public function end(int $length): StringInterface
    {
        return $this->substring(- $length = abs($length), $length);
    }

    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param int $limit
     * @param int|null $count
     * @return StringInterface
     */
    public function pregReplace($pattern, $replacement, int $limit = -1, int &$count = null): StringInterface 
    {
        $copy = clone $this;
        $copy->string = preg_replace($pattern, $replacement, $this->string, $limit, $count);

        return $copy;
    }

    /**
     * @param string $pattern
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return bool
     */
    public function match(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0): bool 
    {
        $match = @preg_match($pattern, $this->string, $matches, $flags, $offset) === 1;

        if ($error = error_get_last())
        {
            throw new \RuntimeException($error['message']);
        }

        return $match;
    }

    /**
     * @param string $pattern
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return bool
     */
    public function matchAll(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0): bool 
    {
        return preg_match_all($pattern, $this->string, $matches, $flags, $offset) === 1;
    }

    /**
     * @return Stringy
     */
    public function revers(): StringInterface
    {
        $copy = clone $this;
        $copy->string = strrev($this->string);
        
        return $copy;
    }

    /**
     * @return Stringy
     */
    public function lcFirst(): StringInterface
    {
        $copy = clone $this;
        $copy->string = lcfirst($this->string);
        
        return $copy;
    }

    /**
     * @param int $num
     * @return Stringy
     */
    public function rand(int $num): StringInterface
    {
        $copy = clone $this;
        $copy->string = Str::random($num, $this->string);
        
        return $copy;
    }

    /**
     * Write string
     */
    public function write(): void 
    {
        echo $this->string;
    }

    /**
     * @return int
     */
    public function lastIndex():? int 
    {
        if (($count = $this->length()) === 0)
        {
            return null ;
        }

        return $count - 1 ;
    }

    /**
     * @return int|null
     */
    public function firstIndex():? int 
    {
        return $this->length() === 0 ? null : 0 ;
    }

    /**
     * @return static
     */
    public function shuffle(): StringInterface
    {
        $copy = clone $this;
        $copy->string = Str::shuffle($this->string);
        
        return $copy;
    }

    /**
     * @return static
     */
    public function toUpper(): StringInterface
    {
        $copy = clone $this;
        $copy->string = strtoupper($this->string);
        
        return $copy;
    }

    /**
     * @return static
     */
    public function toLower(): StringInterface 
    {
        $copy = clone $this;
        $copy->string = strtolower($this->string);
        
        return $copy;
    }

    /**
     * @param int $length
     * @return StringInterface[]
     */
    public function split(int $length = 1): array
    {
        if ($length < 1)
        {
            throw new \LogicException('Argument [length] must be larger by zero');
        }

        $split = [];

        for ($count = $this->count(); $count > 0 ; $count -= $length)
        {
            $split[] = $this->substring(-$count, $length);
        }

        return $split;
    }

    /**
     * @param mixed ... $args
     * @return StringInterface
     * @throws \RuntimeException
     */
    public function format(... $args): StringInterface
    {
        $subject = @sprintf($this->string, ... $args);

        if ($subject === false)
        {
            throw new \RuntimeException(error_get_last()['message']);
        }

        $copy = clone $this;
        $copy->string = $subject;
        
        return $copy;
    }

    /**
     * @return bool
     */
    public function isJson(): bool
    {
        try
        {
            json_decode($this->string, true, 512, JSON_THROW_ON_ERROR);
        } 
        
        catch (\Throwable $e)
        {
            return false;
        }

        return true;
    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string 
    {
        return json_encode($this->string, $options|JSON_THROW_ON_ERROR);
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return (int) $offset > 0 && (int) $offset <= $this->count();
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param int|string $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset): StringInterface 
    {
        if (is_string($offset) && mb_strpos($offset, ':') !== false)
        {
            list($start, $end) = explode(':', $offset, 2);
            return $this->interval((int) $start, (int) $end);
        }

        return $this->index((int) $offset);
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Object: Bermuda\String\Stringy is immutable');
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('Object: Bermuda\String\Stringy is immutable');
    }
}
