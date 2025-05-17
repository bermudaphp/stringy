# bermudaphp\stringy

[![PHP Version Require](https://img.shields.io/badge/php-%3E%3D8.4-brightgreen.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/bermudaphp/stringy/blob/master/LICENSE)
[![GitHub Tests](https://img.shields.io/github/actions/workflow/status/bermudaphp/stringy/tests.yml?branch=master&label=tests)](https://github.com/bermudaphp/stringy/actions/workflows/tests.yml)

*Read this documentation in [Russian](README.ru.md).*

## Overview

bermudaphp/stringy is a comprehensive PHP string manipulation library with full Unicode support. It provides both immutable and mutable string classes with a consistent API, making string operations more reliable and convenient compared to native PHP functions.

## Table of Contents

- [Installation](#installation)
- [Basic Concepts](#basic-concepts)
- [Class Examples](#class-examples)
  - [Str (Immutable)](#str-immutable)
  - [StrMutable (Mutable)](#strmutable-mutable)
  - [StringIterator](#stringiterator)
  - [Stringy (Static Utility)](#stringy-static-utility)
  - [ClsHelper (Class Name Utility)](#clshelper-class-name-utility)
- [Working with Multibyte Strings](#working-with-multibyte-strings)
- [License](#license)
- [Contributing](#contributing)

## Installation

```bash
composer require bermudaphp/stringy
```

## Basic Concepts

The library provides two main string classes:

- **Str**: Immutable string class. Operations return new string instances without modifying the original.
- **StrMutable**: Mutable string class. Operations modify the string in place and return the same instance.

Both implement the **StringInterface** contract, providing a consistent API for string manipulation.

## Class Examples

### Str (Immutable)

The immutable string class returns a new instance for every operation that would change the string.

#### Creating Immutable Strings

```php
use Bermuda\Stdlib\Str;

// Create from string
$str = Str::from('Hello World');

// Create with specific encoding
$russianStr = Str::from('Привет мир', 'UTF-8');

// Create through constructor
$str = new Str('Hello World');

// alternative
$str = Stringy::of('Hello World');
```
## Lazy Initialization with createLazy()

The `createLazy()` static method allows you to create lazily initialized string objects that are only constructed when actually accessed. This can significantly improve performance by delaying expensive operations until they're truly needed.

```php
// Create a lazy-initialized string object
$lazy = \Bermuda\Stdlib\Str::createLazy(static function (\Bermuda\Stdlib\Str $str) {
    $str->__construct('construct call');
});

// No initialization occurs until the object is accessed
echo $lazy->value; // Triggers initialization: "construct call"
```

### How It Works
Unlike traditional objects that are initialized immediately, lazy ghost objects:

Create a minimal placeholder object initially.  
Only execute your initializer function when a property or method is accessed.  
Initialize the object just-in-time with the values you specify.

### When to Use
Lazy initialization is particularly valuable when:

Resource-heavy initialization: Loading data from files, databases, or APIs.  
Conditional usage: When objects may not be used in all code paths.  
Performance optimization: Delaying expensive operations until absolutely necessary.  
Memory management: Reducing memory usage by not initializing unused objects.

### Advanced Example
```php
// Creating multiple lazy string objects for a report
$reportFields = [
    'title' => Str::createLazy(function($str) use ($reportId) {
        $data = $database->fetchReportTitle($reportId);
        $str->__construct($data);
    }),
    'summary' => Str::createLazy(function($str) use ($reportId) {
        $data = $database->fetchReportSummary($reportId);
        $str->__construct($data);
    }),
    'content' => Str::createLazy(function($str) use ($reportId) {
        // This potentially large content is only loaded if actually displayed
        $data = $database->fetchReportContent($reportId);
        $str->__construct($data);
    })
];

// Only the title and summary are initialized - content remains lazy
echo $reportFields['title']->toUpperCase();
echo $reportFields['summary']->truncate(100);

// If this condition is false, content is never loaded from the database
if ($showFullReport) {
    echo $reportFields['content'];
}
```

#### Basic Properties

```php
$str = Str::from('Hello');

// Get the underlying string value
echo $str->value;                // "Hello"

// Get the current encoding
echo $str->encoding;             // "UTF-8" (default)

// Check if string contains multibyte characters
var_dump($str->isMultibyte);     // bool(false)

$russianStr = Str::from('Привет');
var_dump($russianStr->isMultibyte); // bool(true)
```

#### String Conversion and Basic Operations

```php
$str = Str::from('Hello World');

// Convert to string
echo $str->toString();           // "Hello World"
echo $str;                       // "Hello World" (uses __toString())

// Create a copy
$copy = $str->copy();           // Creates new Str instance with same value
$copy === $str;                 // false

// Encode to different character set
$latin1 = $str->encode('ISO-8859-1');

// Get byte count (different from character count for multibyte strings)
echo $str->getBytes();          // 11

// Get string length in characters
echo $str->length();            // 11
echo count($str);               // 11 (using Countable interface)

// Check if string is empty
var_dump($str->isEmpty());      // bool(false)
var_dump(Str::from('')->isEmpty()); // bool(true)

// Check if string is blank (empty or only whitespace)
var_dump(Str::from(' ')->isBlank()); // bool(true)
```

#### Character Access

```php
$str = Str::from('Hello World');

// Get character at position
echo $str->charAt(0);           // "H"
echo $str->charAt(6);           // "W"
echo $str->charAt(-1);          // "d" (negative indices count from end)

// Get character as a new string object
$firstChar = $str->at(0);       // Returns Str('H')
$lastChar = $str->at(-1);       // Returns Str('d')

// Get first and last characters
$first = $str->first();         // Returns Str('H')
$last = $str->last();           // Returns Str('d')

// Check if position exists
var_dump($str->has(5));         // bool(true)
var_dump($str->has(20));        // bool(false)

// Get index bounds
echo $str->firstIndex();        // 0
echo $str->lastIndex();         // 10

// Array access (for reading only in Str)
echo $str[0];                   // "H"
echo $str[-1];                  // "d"
```

#### Substring Operations

```php
$str = Str::from('Hello World');

// Extract substring
echo $str->substring(0, 5);     // "Hello"
echo $str->substring(6);        // "World" (to the end)
echo $str->substring(-5);       // "World" (from 5th last character to end)

// Get start/end of string
echo $str->start(5);            // "Hello"
echo $str->end(5);              // "World"

// Remove from start/end
echo $str->removeStart(6);      // "World"
echo $str->removeEnd(6);        // "Hello"

// Get substring between delimiters
echo $str->between('H', 'o');   // "ell"

// Get substring before/after
echo $str->before('World');     // "Hello "
echo $str->after('Hello');      // " World"

// Include delimiter in before/after
echo $str->before('World', true);  // "Hello W"
echo $str->after('Hello', true);   // "Hello World"

// Split into two parts
list($first, $second) = $str->split(' ');
echo $first;                    // "Hello"
echo $second;                   // "World"

// Split by delimiter
$parts = $str->explode(' ');    // Returns array of Str objects
$parts = $str->explode(' ', PHP_INT_MAX, true); // Returns array of strings
```

#### String Comparison

```php
$str = Str::from('Hello World');

// Compare with another string
var_dump($str->equals('Hello World'));  // bool(true)
var_dump($str->equals('hello world'));  // bool(false)
var_dump($str->equals('hello world', false)); // bool(true) - case insensitive

// Compare with any of multiple strings
var_dump($str->equalsAny(['Hello', 'World', 'Hello World'])); // bool(true)

// Check if starts with
var_dump($str->startsWith('Hello'));    // bool(true)
var_dump($str->startsWith(['Hi', 'Hello'])); // bool(true)
var_dump($str->startsWith('hello', false)); // bool(true) - case insensitive

// Check if ends with
var_dump($str->endsWith('World'));      // bool(true)
var_dump($str->endsWith(['Earth', 'World'])); // bool(true)

// Check if contains
var_dump($str->contains('lo Wo'));      // bool(true)
var_dump($str->contains(['lo', 'Wo'])); // bool(true)

// Check if contains all
var_dump($str->containsAll(['Hello', 'World'])); // bool(true)
```

#### Search Operations

```php
$str = Str::from('Hello World, Hello Earth');

// Find position of substring
echo $str->indexOf('Hello');       // 0
echo $str->indexOf('Hello', 1);    // 13 (starting from position 1)
echo $str->indexOf('hello', 0, false); // 0 (case insensitive)
var_dump($str->indexOf('Bye'));    // NULL (not found)

// Find last position of substring
echo $str->lastIndexOf('Hello');   // 13

// Count occurrences
echo $str->countSubstr('Hello');   // 2
echo $str->countSubstr('hello', false); // 2 (case insensitive)
```

#### Case Conversion

```php
$str = Str::from('hello world');

// Convert to uppercase/lowercase
echo $str->toUpperCase();         // "HELLO WORLD"
echo $str->toLowerCase();         // "hello world"

// Capitalize first character
echo $str->capitalize();          // "Hello world"

// Uncapitalize first character
echo Str::from('Hello World')->uncapitalize(); // "hello World"

// Capitalize each word
echo $str->capitalizeWords();      // "Hello World"

// Swap case of each character
echo Str::from('Hello')->swapCase(); // "hELLO"

// Title case (smarter capitalization)
echo $str->titleize();            // "Hello World"
echo $str->titleize(['of', 'the']); // "Hello World" (with ignored words)
```

#### Format Conversion

```php
$str = Str::from('hello_world-example');

// Convert to different formats
echo $str->toCamelCase();        // "helloWorldExample"
echo $str->toPascalCase();       // "HelloWorldExample"
echo $str->toSnakeCase();        // "hello_world_example"
echo $str->toKebabCase();        // "hello-world-example"

// Custom delimiter
echo $str->delimit('.');         // "hello.world.example"
```

#### Whitespace Handling

```php
$str = Str::from('  Hello World  ');

// Trim whitespace
echo $str->trim();               // "Hello World"
echo $str->trimStart();          // "Hello World  "
echo $str->trimEnd();            // "  Hello World"

// Custom characters to trim
echo Str::from('__Hello__')->trim('_'); // "Hello"

// Remove all whitespace
echo $str->stripWhitespace();    // "HelloWorld"

// Collapse multiple whitespace to single space
echo Str::from('Hello  World')->collapseWhitespace(); // "Hello World"
```

#### String Modification

```php
$str = Str::from('Hello World');

// Insert at position
echo $str->insert(' Dear', 5);   // "Hello Dear World"

// Pad string
echo $str->pad('*', 15);         // "**Hello World**"
echo $str->padStart('-', 15);    // "----Hello World"
echo $str->padEnd('=', 15);      // "Hello World===="

// Wrap with character
echo $str->wrap('"');            // "\"Hello World\""

// Check if wrapped
var_dump(Str::from('"Hello"')->isWrapped('"')); // bool(true)

// Add to start/end
echo $str->prepend('Dear ');     // "Dear Hello World"
echo $str->append('!');          // "Hello World!"

// Add prefix/suffix if not exists
echo $str->ensurePrefix('Hello '); // "Hello World" (unchanged)
echo $str->ensureSuffix('!');    // "Hello World!"

// Remove prefix/suffix
echo Str::from('HelloWorld')->removeSuffix('World'); // "Hello"
echo Str::from('HelloWorld')->removePrefix('Hello'); // "World"
```

#### Search and Replace

```php
$str = Str::from('Hello World');

// Replace text
echo $str->replace('World', 'Earth'); // "Hello Earth"
echo $str->replace(['Hello', 'World'], ['Hi', 'Earth']); // "Hi Earth"
echo $str->replace('world', 'Earth', false); // "Hello Earth" (case insensitive)

// Replace first/last occurrence
echo Str::from('Hello Hello')->replaceFirst('Hello', 'Hi'); // "Hi Hello"
echo Str::from('Hello Hello')->replaceLast('Hello', 'Hi');  // "Hello Hi"

// Replace with pattern
echo $str->replaceBy('/[aeiou]/i', '*'); // "H*ll* W*rld"

// Replace with callback
$result = $str->replaceCallback('/[A-Z]/u', function($match) {
    return '_' . strtolower($match[0]);
}); // "_hello _world"
```

#### String Transformation

```php
$str = Str::from('Hello World');

// Reverse
echo $str->reverse();           // "dlroW olleH"

// Shuffle characters
echo $str->shuffle();           // Random order, e.g. "ldWroHl eol"

// Repeat
echo Str::from('Hi ')->repeat(3); // "Hi Hi Hi "

// Truncate
echo Str::from('This is a long sentence')->truncate(10); // "This is..."
echo Str::from('This is a long sentence')->truncate(10, '---'); // "This is---"
echo Str::from('This is a long sentence')->truncate(10, '...', true); // "This..."

// Transform with callback
echo $str->transform(function($s) {
    return strtoupper($s) . '!';
}); // "HELLO WORLD!"

// Tabs and spaces
echo Str::from("Hello\tWorld")->tabsToSpaces(2); // "Hello  World"
echo Str::from("Hello  World")->spacesToTabs(2); // "Hello\tWorld"

// Format with sprintf
echo Str::from('Hello, %s!')->format('John'); // "Hello, John!"
```

#### Type Validation and Conversion

```php
$str = Str::from('123');

// Check types
var_dump($str->isNumeric());     // bool(true)
var_dump($str->isAlpha());       // bool(false)
var_dump($str->isAlphanumeric()); // bool(true)
var_dump($str->isHex());         // bool(true)
var_dump($str->isUpperCase());   // bool(false)
var_dump($str->isLowerCase());   // bool(false)
var_dump($str->hasUpperCase());  // bool(false)
var_dump($str->hasLowerCase());  // bool(false)
var_dump($str->hasDigits());     // bool(true)
var_dump($str->hasSymbols());    // bool(false)

// Convert to types
echo $str->toNumber();           // int(123)
var_dump(Str::from('true')->toBoolean()); // bool(true)
var_dump(Str::from('true')->isBoolean()); // bool(true)

// JSON operations
var_dump(Str::from('{"a":1}')->isJson()); // bool(true)
echo Str::from('Hello')->toJson(); // "\"Hello\""

// Other type checks
var_dump(Str::from('a:1:{s:1:"a";i:1;}')->isSerialized()); // bool(true)
var_dump(Str::from('SGVsbG8=')->isBase64()); // bool(true)
var_dump(Str::from('2023-05-17')->isDate()); // bool(true)

// Convert to date
$date = Str::from('2023-05-17')->toDate();
```

#### String Segmentation

```php
$str = Str::from("Hello\nWorld\nExample");

// Split into lines
$lines = $str->lines();          // Array of Str objects, one per line

// Split into words
$words = Str::from('Hello World Example')->words(); // ["Hello", "World", "Example"]

// Convert to array of characters
$chars = $str->toArray();        // ["H", "e", "l", "l", "o", ... ]
```

#### Regular Expressions

```php
$str = Str::from('Hello 123 World');

// Match pattern
$found = $str->match('/\d+/', $matches);
var_dump($found);               // bool(true)
echo $matches[0];               // "123"

// Match all occurrences
$found = $str->matchAll('/\w+/', $matches);
var_dump($matches[0]);          // Array with all words
```

#### Other Operations

```php
$str = Str::from('Hello World');

// Hash the string
echo $str->hash();              // SHA-256 hash
echo $str->hash('md5');         // MD5 hash

// ASCII conversion
echo Str::from('Café')->toAscii(); // "Cafe"

// Output
$str->print();                  // Outputs "Hello World"

// Iterate through characters
$str->each(function($char, $index) {
    echo "$index: $char\n";
    return true; // Continue iteration
});
```

#### Iterator Usage

```php
$str = Str::from('Hello');

// Get iterator
$iterator = $str->getIterator();

// Use in foreach
foreach ($iterator as $index => $char) {
    echo "$index: $char\n";
}
```

### StrMutable (Mutable)

The mutable string class modifies the string in place and returns the same instance for method chaining.

#### Creating Mutable Strings

```php
use Bermuda\Stdlib\StrMutable;

// Create from string
$str = StrMutable::create('Hello World');

// Create with specific encoding
$russianStr = StrMutable::create('Привет мир', 'UTF-8');

// Create through constructor
$str = new StrMutable('Hello World');

// alternative
$str = Stringy::mutable('Hello World');

// Set string value directly
$str = StrMutable::create('Hello');
$str->setString('New Value');
```

Most methods in StrMutable have the same API as Str, but modify the string in place:

```php
$str = StrMutable::create('Hello World');

// Chain operations
$str->toUpperCase()
    ->trim()
    ->replace('WORLD', 'EARTH');

echo $str; // "HELLO EARTH"

// Substring modifies in place
$str->substring(0, 5);
echo $str; // "HELLO"
```

#### Array Access (Mutable)

```php
$str = StrMutable::create('Hello');

// Read
echo $str[1];  // "e"

// Write
$str[0] = 'J';
echo $str;     // "Jello"

// Remove character
unset($str[4]);
echo $str;     // "Jell"
```

### StringIterator

The StringIterator class allows character-by-character iteration with various navigation methods.

#### Creating and Basic Usage

```php
use Bermuda\Stdlib\StringIterator;

// Create directly
$iterator = new StringIterator('Hello');

// Create through string object
$str = Str::from('World');
$iterator = $str->getIterator();

// Basic iteration
while ($iterator->valid()) {
    echo $iterator->current();
    $iterator->next();
}
// Outputs: "World"

// Get the string
echo $iterator->__toString(); // "World"

// Create new iterator with different string
$newIterator = $iterator->withString('Hello');
```

#### Navigation

```php
$iterator = new StringIterator('Hello World');

// Get current state
echo $iterator->current();   // "H" (initial position is 0)
echo $iterator->key();       // 0 (current position)

// Move forward/backward
$iterator->next();
echo $iterator->current();   // "e"

$iterator->forward(2);       // Move 2 steps forward
echo $iterator->current();   // "l"

$iterator->backward();       // Move 1 step back
echo $iterator->current();   // "e"

// Jump to position
$iterator->moveTo(6);
echo $iterator->current();   // "W"

// Reset position
$iterator->rewind();
echo $iterator->key();       // 0

// Check position
var_dump($iterator->isStart()); // bool(true)
var_dump($iterator->isEnd());   // bool(false)

$iterator->moveTo($iterator->lastIndex());
var_dump($iterator->isEnd());   // bool(false)

$iterator->next();
var_dump($iterator->isEnd());   // bool(true)
var_dump($iterator->valid());   // bool(false)
```

#### Reading

```php
$iterator = new StringIterator('Hello World');

// Read next characters
$iterator->moveTo(6);
echo $iterator->readNext(5);    // "World"

// Read to the end (when length is null)
$iterator->moveTo(6);
echo $iterator->readNext();     // "World"
```

### Stringy (Static Utility)

The Stringy class provides static utility methods for string manipulation.

```php
use Bermuda\Stdlib\Stringy;

// Convert string format
echo Stringy::delimit('HelloWorld', '-');           // "hello-world"
echo Stringy::delimit('hello_world-example', '.'); // "hello.world.example"

// Trim whitespace
echo Stringy::trim('  Hello  ');                    // "Hello"
echo Stringy::trimStart('  Hello  ');               // "Hello  "
echo Stringy::trimEnd('  Hello  ');                 // "  Hello"

// With custom characters
echo Stringy::trim('__Hello__', '_');               // "Hello"

// Multibyte support
echo Stringy::trim('  Привет  ');                   // "Привет"
```

### ClsHelper (Class Name Utility)

The ClsHelper class provides utilities for working with class names and namespaces.

```php
use Bermuda\Stdlib\ClsHelper;

$className = 'Bermuda\\Stringy\\StrMutable';

// Get namespace part
echo ClsHelper::namespace($className);    // "Bermuda\\Stringy"

// Get basename (class without namespace)
echo ClsHelper::basename($className);     // "StrMutable"

// Split into namespace and class name
$parts = ClsHelper::split($className);
// Returns: [0 => 'Bermuda\\Stringy', 1 => 'StrMutable']

// Validate class names
var_dump(ClsHelper::isValidName('MyClass'));          // bool(true)
var_dump(ClsHelper::isValidName('Vendor\\MyClass'));  // bool(true)
var_dump(ClsHelper::isValidName('Vendor\\MyClass', false)); // bool(false) - no namespace allowed
var_dump(ClsHelper::isValidName('0InvalidClass'));    // bool(false)
```

## Working with Multibyte Strings

All methods in Bermuda\Stringy properly handle multibyte strings, ensuring correct character handling for non-Latin alphabets and special characters:

### Basic Multibyte Operations

```php
// Create with specific encoding
$russian = Str::from('Привет, мир!', 'UTF-8');
$chinese = Str::from('你好，世界！', 'UTF-8');
$arabic = Str::from('مرحبا بالعالم!', 'UTF-8');

// Character count vs byte count
echo $russian->length();      // 12 (character count)
echo $russian->getBytes();    // More than 12 (byte count)

// Character access
echo $russian->charAt(0);     // "П"
echo $chinese->charAt(0);     // "你"

// Substring extraction
echo $russian->substring(0, 6);  // "Привет"
echo $chinese->substring(0, 2);  // "你好"

// Case conversion (where applicable)
echo $russian->toUpperCase();    // "ПРИВЕТ, МИР!"
echo $russian->toLowerCase();    // "привет, мир!"
```

### Text Transformation

```php
// Reverse (correctly handles multibyte)
echo $russian->reverse();     // "!рим ,тевирП"

// Replace
echo $russian->replace('мир', 'свет');  // "Привет, свет!"

// Trim
echo Str::from('  Привет  ')->trim();  // "Привет"

// Character iterations
$iterator = $russian->getIterator();
foreach ($iterator as $char) {
    echo $char . ' ';  // "П р и в е т ,   м и р !"
}
```

### Array Access with Multibyte

```php
$mutable = StrMutable::create('Привет');
echo $mutable[0];      // "П"

$mutable[0] = 'К';
echo $mutable;         // "Кривет"

unset($mutable[5]);
echo $mutable;         // "Криве"
```

### Working with Different Scripts

```php
// Japanese
$japanese = Str::from('こんにちは世界', 'UTF-8');
echo $japanese->length();        // 7 characters
echo $japanese->substring(0, 5); // "こんにちは"

// Thai (with combining diacritical marks)
$thai = Str::from('สวัสดีโลก', 'UTF-8');
echo $thai->length();            // Correctly counts Thai characters with marks

// Emoji
$emoji = Str::from('Hello 👋 World 🌍', 'UTF-8');
echo $emoji->length();           // Correctly counts emoji characters
echo $emoji->substring(6, 1);    // "👋"

// Mixed script text
$mixed = Str::from('Hello Привет 你好 مرحبا', 'UTF-8');
foreach ($mixed->words() as $word) {
    echo $word . "\n";  // Correctly splits words across scripts
}
```

## License

This library is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please create a pull request on the GitHub repository.
