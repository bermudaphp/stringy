<?php


namespace Lobster\Stringy;


use Traversable;
use ForceUTF8\Encoding;
use Lobster\Reducible\Arrayble;
use Lobster\Reducible\Jsonable;


/**
 * Class Stringy
 * @package Lobster\Stringy
 */
class Stringy implements \IteratorAggregate, \ArrayAccess, \Countable, Arrayble, Jsonable {

    /**
     * @var string
     */
    private string $string;

    /**
     * @var string
     */
    private string $encoding;

    public const TRIM_LEFT = 1;
    public const TRIM_RIGHT = 2;

    /**
     * @param string $string
     * @param string|null $encoding
     * @return static
     */
    public static function create(string $string = '', string $encoding = null) : self {
        return new static($string, $encoding);
    }

    /**
     * Str constructor.
     * @param string $string
     * @param string|null $encoding
     */
    public function __construct(string $string = '', string $encoding = null) {
        $this->string = $string;
        $this->encoding = mb_strtoupper($encoding ?? mb_internal_encoding());
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function toString() : string {
        return $this->string;
    }

    /**
     * dump string and die
     */
    public function dd() : void {
        dd($this->string);
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return [$this];
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator() : StringIterator {
        return new StringIterator($this);
    }

    /**
     * @return string
     */
    public function encoding() : string {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     * @return Stringy
     */
    public function encode(string $encoding) : self {

        if(($encoding = stringy($encoding))->equals('UTF-8', true)){
            return static::create(Encoding::toUTF8($this->string), $encoding);
        }

        if($encoding->equalsAny(['ISO-8859-1', 'Windows-1251'], true)){
            return static::create(Encoding::toWin1252($this->string), 'ISO-8859-1');
        }

        if(!$encoding->equalsAny(mb_list_encodings())){
            throw new \RuntimeException(
                'Invalid encoding: ' . (string) $encoding
            );
        }

        return stringy(mb_convert_encoding($this->string, $encoding, $this->encoding), $encoding);
    }

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseSensitive
     * @return int|null
     */
    public function indexOf(string $needle, int $offset = 0, bool $caseSensitive = false) :? int {

        if((bool)$caseSensitive){
            return @($i = mb_stripos($this->string, $needle, $offset)) !== false ? $i : null ;
        }

        return @($i = mb_strpos($this->string, $needle, $offset)) !== false ? $i : null ;
    }

    /**
     * @return Stringy
     */
    public function copy() : self {
        return clone $this;
    }

    /**
     * @param string $delim
     * @return Stringy[]
     */
    public function explode(string $delim = '/') : array {
        return array_map(function ($string){return static::create($string, $this->encoding);}, explode($delim, $this->string));
    }

    /**
     * @return Stringy
     */
    public function ucFirst() : self {
        return static::create(ucfirst($this->string), $this->encoding);
    }

    /**
     * @return int
     */
    public function count(): int {
        return $this->length();
    }

    /**
     * @param string $needle
     * @param int $offset
     * @param bool $caseSensitive
     * @return bool
     */
    public function contains(string $needle, int $offset = 0, bool $caseSensitive = false) : bool {
       return $this->indexOf($needle, $offset, $caseSensitive) !== null;
    }

    /**
     * @param int $length
     * @param string $substring
     * @return Stringy
     */
    public function truncate(int $length = 200, string $substring = '...') : self {
        return $this->start($length)->append($substring);
    }

    /**
     * @return int
     */
    public function length() : int {
        return mb_strlen($this->string);
    }

    /**
     * @return int
     */
    public function getBytes() : int {
        return strlen($this->string);
    }

    /**
     * @param string $needle
     * @param bool $requireNeedle
     * @param bool $caseSensitive
     * @return Stringy|null
     */
    public function before(string $needle, bool $requireNeedle = true, bool $caseSensitive = false) :? self {

        if(($index = $this->indexOf($needle, 0, $caseSensitive)) !== null){
            return $this->start($requireNeedle ? $index + 1 : $index);
        }

        return null ;
    }

    /**
     * @param string $needle
     * @param bool $requireNeedle
     * @param bool $caseSensitive
     * @return Stringy|null
     */
    public function after(string $needle, bool $requireNeedle = true, bool $caseSensitive = false) :? self {

        if(($index = $this->indexOf($needle, 0, $caseSensitive)) !== null){
            return $this->substring($requireNeedle ? $index + 1 : $index);
        }

        return null ;
    }

    /**
     * @param string $algorithm
     * @return static
     */
    public function hash(string $algorithm = 'sha512') : self {
        return static::create(hash($algorithm, $this->string), $this->encoding);
    }

    /**
     * @param string $charlist
     * @return Stringy
     */
    public function trim(string $charlist = ' ') : self {
        return static::create(trim($this->string, $charlist));
    }

    /**
     * @param string $charlist
     * @return Stringy
     */
    public function ltrim(string $charlist = ' ') : self {
        return static::create(ltrim($this->string, $charlist));
    }

    /**
     * @param string $charlist
     * @return Stringy
     */
    public function rtrim(string $charlist = ' ') : self {
        return static::create(rtrim($this->string, $charlist));
    }


    /**
     * @param string|array $search
     * @param string|array $replace
     * @return Stringy
     */
    public function replace($search, $replace) : self {
        return static::create(str_replace($search, $replace, $this), $this->encoding);
    }


    /**
     * @param string $subject
     * @return Stringy
     */
    public function prepend(string $subject) : self {
        return static::create($subject . $this->string);
    }

    /**
     * @param string $subject
     * @return Stringy
     */
    public function append(string $subject) : self {
        return static::create($this->string . $subject);
    }

    /**
     * @param string $subject
     * @param bool $caseSensitive
     * @return bool
     */
    public function equals(string $subject, bool $caseSensitive = false) : bool {

        if($caseSensitive){
            return strcasecmp($this->string, $subject) == 0 ;
        }

        return strcmp($this->string, $subject) == 0 ;
    }

    /**
     * @param string[] $subject
     * @param bool $caseSensitive
     * @return bool
     */
    public function equalsAny(array $subject, bool $caseSensitive = false) : bool {

        foreach ($subject as $item){
            if($this->equals((string) $item, $caseSensitive)){
                return true;
            }
        }

        return false ;
    }

    /**
     * @return bool
     */
    public function isEmpty() : bool {
        return empty($this->string);
    }

    /**
     * @param int $index
     * @return Stringy
     * @throws \Exception
     */
    public function index(int $index) : self {

        if(!$this->has($index)){
            throw new \Exception('Invalid offset.');
        }

        return static::create($this->string[$index]);
    }

    /**
     * @param int $start
     * @param int $end
     * @return Stringy
     */
    public function interval(int $start, int $end) : self {

        for ($string = ''; $start <= $end; $start++){
            $string .= $this->string[$start];
        }

        return static::create($string);
    }

    /**
     * @param int $index
     * @return bool
     */
    public function has(int $index) : bool {
        return abs($index) <= $this->lastIndex();
    }

    /**
     * @return Stringy|null
     */
    public function first() :? self {
        return $this->index(0);
    }

    /**
     * @return Stringy|null
     */
    public function last() :? self {
        return $this->index($this->lastIndex());
    }

    /**
     * @param string $char
     * @return Stringy
     */
    public function wrap(string $char) : self {
        return $this->prepend($char)->append($char);
    }

    /**
     * @param string|null $char
     * @return bool
     */
    public function isWrapped(string $char) : bool {
        return ($char = static::create($char))->first()
                ->equals($char) && $char->last()->equals($char);
    }

    /**
     * @param int $pos
     * @return self[]
     */
    public function break(int $pos) : array {
        return [
            $this->start($pos),
            $this->substring($pos),
        ];
    }

    /**
     * @param int $pos
     * @param int|null $length
     * @return Stringy
     */
    public function substring(int $pos, int $length = null) : self {
        return static::create(mb_substr($this->string, $pos, $length), $this->encoding);
    }

    /**
     * @param int $length
     * @return Stringy
     */
    public function start(int $length) : self {
        return $this->substring(0, $length);
    }

    /**
     * @param int $length
     * @return Stringy
     */
    public function end(int $length) : self {
        return $this->substring( - $length = abs($length), $length);
    }

    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param int $limit
     * @param int|null $count
     * @return Stringy
     */
    public function pregReplace($pattern, $replacement, int $limit = -1, int &$count = null) : self {
        return static::create(preg_replace($pattern, $replacement, $this->string, $limit, $count));
    }

    /**
     * @param string $pattern
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return bool
     */
    public function match(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0) : bool {
        $match = @preg_match($pattern, $this->string, $matches, $flags, $offset) === 1;

        if(error_get_last()){
            RegexpException::fromLastError();
        }

        return $match ;
    }

    /**
     * @param string $pattern
     * @param array|null $matches
     * @param int $flags
     * @param int $offset
     * @return bool
     */
    public function matchAll(string $pattern, ?array &$matches = [], int $flags = 0, int $offset = 0) : bool {
        return preg_match_all($pattern, $this->string, $matches, $flags, $offset) === 1;
    }

    /**
     * @return Stringy
     */
    public function revers() : self {
        return static::create(strrev($this->string));
    }

    /**
     * @return Stringy
     */
    public function lcFirst() : self {
        return static::create(lcfirst($this->string));
    }

    /**
     * @param int $len
     * @return Stringy
     */
    public function rand(int $len) : self {

        $str = '';

        while ($len--){
            $str .= $this->index(random_int(0, $this->lastIndex()));
        }

        return static::create($str);
    }

    /**
     * Write string
     */
    public function write() : void {
        echo $this->string;
    }

    /**
     * @return int
     */
    public function lastIndex() :? int {

        if(($count = $this->length()) === 0){
            return null ;
        }

        return $count - 1 ;
    }

    /**
     * @return int|null
     */
    public function firstIndex() :? int {
        return $this->length() === 0 ? null : 0 ;
    }

    /**
     * @return static
     */
    public function shuffle() : self {

        $chars = $this->split();

        usort($chars, function () : int {

            if(($left = random_int(0, 100))
                == ($right = random_int(0, 100))
            ){
                return 0;
            }

            return  $left > $right ? 1 : -1 ;
        });

        return static::create(implode('', $chars));

    }

    /**
     * @return static
     */
    public function toUpper() : self {
        return static::create(strtoupper($this->string));
    }

    /**
     * @return static
     */
    public function toLower() : self {
        return static::create(strtolower($this->string));
    }

    /**
     * @param int $length
     * @return Stringy[]
     */
    public function split(int $length = 1) : array {

        if($length < 1){
            throw new InvalidArgumentException(
                'Argument [length] must be larger by zero.'
            );
        }

        $split = [];

        for ($count = $this->count(); $count > 0 ; $count -= $length){
            $split[] = $this->substring(-$count, $length);
        }

        return $split ;
    }

    /**
     * @param mixed ...$args
     * @return static
     * @throws ErrorException
     */
    public function format(...$args) : self {

        $subject = @sprintf($this->string, ...$args);

        if($subject === false){
            ErrorException::fromLastError();
        }

        return static::create($subject);
    }

    /**
     * @return bool
     */
    public function isJson() : bool {

        try{
            json_decode($this->string, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e){
            return false;
        }

        return true;

    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string {
        return json_encode($this->string, $options | JSON_THROW_ON_ERROR);
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
    public function offsetExists($offset) {
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
    public function offsetGet($offset) : self {

        if(is_string($offset) && mb_strpos($offset, ':') !== false){

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
    public function offsetSet($offset, $value) {
        throw new \Exception();
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
    public function offsetUnset($offset) {
        throw new \Exception('');
    }
}
