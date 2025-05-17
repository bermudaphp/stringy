# bermudaphp/stringy

[![PHP Version Require](https://img.shields.io/badge/php-%3E%3D8.4-brightgreen.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/bermudaphp/stringy/blob/master/LICENSE)
[![GitHub Tests](https://img.shields.io/github/actions/workflow/status/bermudaphp/stringy/tests.yml?branch=master&label=tests)](https://github.com/bermudaphp/stringy/actions/workflows/tests.yml)

*Читайте эту документацию на [английском](README.md).*

## Обзор

bermudaphp/stringy - это полнофункциональная PHP библиотека для манипуляции со строками с полной поддержкой Unicode. Она предоставляет как неизменяемые, так и изменяемые строковые классы с единым API, делая строковые операции более надежными и удобными по сравнению с встроенными PHP функциями.

## Содержание

- [Установка](#установка)
- [Основные концепции](#основные-концепции)
- [Примеры классов](#примеры-классов)
  - [Str (Неизменяемый)](#str-неизменяемый)
  - [StrMutable (Изменяемый)](#strmutable-изменяемый)
  - [StringIterator](#stringiterator)
  - [Stringy (Статические утилиты)](#stringy-статические-утилиты)
  - [ClsHelper (Утилиты для работы с именами классов)](#clshelper-утилиты-для-работы-с-именами-классов)
- [Работа с многобайтовыми строками](#работа-с-многобайтовыми-строками)
- [Лицензия](#лицензия)
- [Содействие](#содействие)

## Установка

```bash
composer require bermudaphp/stringy
```

## Основные концепции

Библиотека предоставляет два основных строковых класса:

- **Str**: Неизменяемый класс строки. Операции возвращают новые экземпляры строк, не изменяя оригинал.
- **StrMutable**: Изменяемый класс строки. Операции изменяют строку на месте и возвращают тот же экземпляр.

Оба класса реализуют контракт **StringInterface**, обеспечивая единый API для манипуляции со строками.

## Примеры классов

### Str (Неизменяемый)

Неизменяемый класс строки возвращает новый экземпляр для каждой операции, которая изменила бы строку.

#### Создание неизменяемых строк

```php
use Bermuda\Stdlib\Str;

// Создать из строки
$str = Str::from('Hello World');

// Создать с указанием кодировки
$russianStr = Str::from('Привет мир', 'UTF-8');

// альтернатива
$str = Stringy::of('Hello World');

// Создать через конструктор
$str = new Str('Hello World');
```
## Ленивая инициализация с createLazy()

Статический метод `createLazy()` позволяет создавать объекты строк с отложенной инициализацией, которые конструируются только при фактическом обращении к ним. Это может значительно повысить производительность, откладывая выполнение ресурсоемких операций до момента, когда они действительно необходимы.

### Базовое использование

```php
// Создание строкового объекта с отложенной инициализацией
$lazy = \Bermuda\Stdlib\Str::createLazy(static function (\Bermuda\Stdlib\Str $str) {
    $str->__construct('construct call');
});

// Инициализация не происходит, пока не обратились к объекту
echo $lazy->value; // Вызывает инициализацию: "construct call"
```
### Как это работает

В отличие от традиционных объектов, которые инициализируются сразу, ленивые ghost-объекты:

Создают минимальный объект-заместитель изначально.  
Выполняют функцию инициализации только при обращении к свойству или методу.  
Инициализируют объект в момент обращения с указанными вами значениями.

### Когда использовать  
Ленивая инициализация особенно полезна, когда:

Ресурсоемкая инициализация: Загрузка данных из файлов, баз данных или API.  
Условное использование: Когда объекты могут не использоваться во всех путях выполнения кода.  
Оптимизация производительности: Откладывание затратных операций до абсолютной необходимости.  
Управление памятью: Снижение использования памяти за счет неинициализации неиспользуемых объектов

### Расширенный пример

```php
// Создание нескольких ленивых строковых объектов для отчета
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
        // Этот потенциально большой контент загружается только если он действительно отображается
        $data = $database->fetchReportContent($reportId);
        $str->__construct($data);
    })
];

// Инициализируются только заголовок и аннотация - контент остается ленивым
echo $reportFields['title']->toUpperCase();
echo $reportFields['summary']->truncate(100);

// Если это условие ложно, контент никогда не загружается из базы данных
if ($showFullReport) {
    echo $reportFields['content'];
}
```

#### Базовые свойства

```php
$str = Str::from('Hello');

// Получить базовое значение строки
echo $str->value;                // "Hello"

// Получить текущую кодировку
echo $str->encoding;             // "UTF-8" (по умолчанию)

// Проверить, содержит ли строка многобайтовые символы
var_dump($str->isMultibyte);     // bool(false)

$russianStr = Str::from('Привет');
var_dump($russianStr->isMultibyte); // bool(true)
```

#### Преобразование строк и базовые операции

```php
$str = Str::from('Hello World');

// Преобразовать в строку
echo $str->toString();           // "Hello World"
echo $str;                       // "Hello World" (использует __toString())

// Создать копию
$copy = $str->copy();           // Создает новый экземпляр Str с тем же значением
$copy === $str;                 // false

// Закодировать в другую кодировку
$latin1 = $str->encode('ISO-8859-1');

// Получить количество байт (отличается от количества символов для многобайтовых строк)
echo $str->getBytes();          // 11

// Получить длину строки в символах
echo $str->length();            // 11
echo count($str);               // 11 (использует интерфейс Countable)

// Проверить, пуста ли строка
var_dump($str->isEmpty());      // bool(false)
var_dump(Str::from('')->isEmpty()); // bool(true)

// Проверить, пуста ли строка или содержит только пробелы
var_dump(Str::from(' ')->isBlank()); // bool(true)
```

#### Доступ к символам

```php
$str = Str::from('Hello World');

// Получить символ на позиции
echo $str->charAt(0);           // "H"
echo $str->charAt(6);           // "W"
echo $str->charAt(-1);          // "d" (отрицательные индексы считаются с конца)

// Получить символ как новый строковый объект
$firstChar = $str->at(0);       // Возвращает Str('H')
$lastChar = $str->at(-1);       // Возвращает Str('d')

// Получить первый и последний символы
$first = $str->first();         // Возвращает Str('H')
$last = $str->last();           // Возвращает Str('d')

// Проверить, существует ли позиция
var_dump($str->has(5));         // bool(true)
var_dump($str->has(20));        // bool(false)

// Получить границы индекса
echo $str->firstIndex();        // 0
echo $str->lastIndex();         // 10

// Доступ как к массиву (только для чтения в Str)
echo $str[0];                   // "H"
echo $str[-1];                  // "d"
```

#### Операции с подстроками

```php
$str = Str::from('Hello World');

// Извлечь подстроку
echo $str->substring(0, 5);     // "Hello"
echo $str->substring(6);        // "World" (до конца)
echo $str->substring(-5);       // "World" (от 5-го символа с конца до конца)

// Получить начало/конец строки
echo $str->start(5);            // "Hello"
echo $str->end(5);              // "World"

// Удалить с начала/конца
echo $str->removeStart(6);      // "World"
echo $str->removeEnd(6);        // "Hello"

// Получить подстроку между разделителями
echo $str->between('H', 'o');   // "ell"

// Получить подстроку до/после
echo $str->before('World');     // "Hello "
echo $str->after('Hello');      // " World"

// Включить разделитель в результат до/после
echo $str->before('World', true);  // "Hello W"
echo $str->after('Hello', true);   // "Hello World"

// Разделить на две части
list($first, $second) = $str->split(' ');
echo $first;                    // "Hello"
echo $second;                   // "World"

// Разделить по разделителю
$parts = $str->explode(' ');    // Возвращает массив объектов Str
$parts = $str->explode(' ', PHP_INT_MAX, true); // Возвращает массив строк
```

#### Сравнение строк

```php
$str = Str::from('Hello World');

// Сравнить с другой строкой
var_dump($str->equals('Hello World'));  // bool(true)
var_dump($str->equals('hello world'));  // bool(false)
var_dump($str->equals('hello world', false)); // bool(true) - без учета регистра

// Сравнить с любой из нескольких строк
var_dump($str->equalsAny(['Hello', 'World', 'Hello World'])); // bool(true)

// Проверить, начинается ли с
var_dump($str->startsWith('Hello'));    // bool(true)
var_dump($str->startsWith(['Hi', 'Hello'])); // bool(true)
var_dump($str->startsWith('hello', false)); // bool(true) - без учета регистра

// Проверить, заканчивается ли
var_dump($str->endsWith('World'));      // bool(true)
var_dump($str->endsWith(['Earth', 'World'])); // bool(true)

// Проверить, содержит ли
var_dump($str->contains('lo Wo'));      // bool(true)
var_dump($str->contains(['lo', 'Wo'])); // bool(true)

// Проверить, содержит ли все
var_dump($str->containsAll(['Hello', 'World'])); // bool(true)
```

#### Операции поиска

```php
$str = Str::from('Hello World, Hello Earth');

// Найти позицию подстроки
echo $str->indexOf('Hello');       // 0
echo $str->indexOf('Hello', 1);    // 13 (начиная с позиции 1)
echo $str->indexOf('hello', 0, false); // 0 (без учета регистра)
var_dump($str->indexOf('Bye'));    // NULL (не найдено)

// Найти последнюю позицию подстроки
echo $str->lastIndexOf('Hello');   // 13

// Подсчитать вхождения
echo $str->countSubstr('Hello');   // 2
echo $str->countSubstr('hello', false); // 2 (без учета регистра)
```

#### Преобразование регистра

```php
$str = Str::from('hello world');

// Преобразовать в верхний/нижний регистр
echo $str->toUpperCase();         // "HELLO WORLD"
echo $str->toLowerCase();         // "hello world"

// Сделать первый символ заглавным
echo $str->capitalize();          // "Hello world"

// Сделать первый символ строчным
echo Str::from('Hello World')->uncapitalize(); // "hello World"

// Сделать первый символ каждого слова заглавным
echo $str->capitalizeWords();      // "Hello World"

// Поменять регистр каждого символа
echo Str::from('Hello')->swapCase(); // "hELLO"

// Формат заголовка (более умное преобразование)
echo $str->titleize();            // "Hello World"
echo $str->titleize(['of', 'the']); // "Hello World" (с игнорируемыми словами)
```

#### Преобразование формата

```php
$str = Str::from('hello_world-example');

// Преобразовать в разные форматы
echo $str->toCamelCase();        // "helloWorldExample"
echo $str->toPascalCase();       // "HelloWorldExample"
echo $str->toSnakeCase();        // "hello_world_example"
echo $str->toKebabCase();        // "hello-world-example"

// Пользовательский разделитель
echo $str->delimit('.');         // "hello.world.example"
```

#### Обработка пробелов

```php
$str = Str::from('  Hello World  ');

// Обрезать пробелы
echo $str->trim();               // "Hello World"
echo $str->trimStart();          // "Hello World  "
echo $str->trimEnd();            // "  Hello World"

// Пользовательские символы для обрезки
echo Str::from('__Hello__')->trim('_'); // "Hello"

// Удалить все пробелы
echo $str->stripWhitespace();    // "HelloWorld"

// Свернуть повторяющиеся пробелы в один
echo Str::from('Hello  World')->collapseWhitespace(); // "Hello World"
```

#### Модификация строк

```php
$str = Str::from('Hello World');

// Вставить в позицию
echo $str->insert(' Dear', 5);   // "Hello Dear World"

// Дополнить строку
echo $str->pad('*', 15);         // "**Hello World**"
echo $str->padStart('-', 15);    // "----Hello World"
echo $str->padEnd('=', 15);      // "Hello World===="

// Обернуть символом
echo $str->wrap('"');            // "\"Hello World\""

// Проверить, обернута ли
var_dump(Str::from('"Hello"')->isWrapped('"')); // bool(true)

// Добавить в начало/конец
echo $str->prepend('Dear ');     // "Dear Hello World"
echo $str->append('!');          // "Hello World!"

// Добавить префикс/суффикс, если не существует
echo $str->ensurePrefix('Hello '); // "Hello World" (без изменений)
echo $str->ensureSuffix('!');    // "Hello World!"

// Удалить префикс/суффикс
echo Str::from('HelloWorld')->removeSuffix('World'); // "Hello"
echo Str::from('HelloWorld')->removePrefix('Hello'); // "World"
```

#### Поиск и замена

```php
$str = Str::from('Hello World');

// Заменить текст
echo $str->replace('World', 'Earth'); // "Hello Earth"
echo $str->replace(['Hello', 'World'], ['Hi', 'Earth']); // "Hi Earth"
echo $str->replace('world', 'Earth', false); // "Hello Earth" (без учета регистра)

// Заменить первое/последнее вхождение
echo Str::from('Hello Hello')->replaceFirst('Hello', 'Hi'); // "Hi Hello"
echo Str::from('Hello Hello')->replaceLast('Hello', 'Hi');  // "Hello Hi"

// Заменить по шаблону
echo $str->replacePattern('/[аеиоу]/i', '*'); // "H*ll* W*rld"

// Заменить с использованием функции обратного вызова
$result = $str->replaceCallback('/[A-Z]/u', function($match) {
    return '_' . strtolower($match[0]);
}); // "_hello _world"
```

#### Трансформация строк

```php
$str = Str::from('Hello World');

// Развернуть
echo $str->reverse();           // "dlroW olleH"

// Перемешать символы
echo $str->shuffle();           // Случайный порядок, например "ldWroHl eol"

// Повторить
echo Str::from('Hi ')->repeat(3); // "Hi Hi Hi "

// Усечь
echo Str::from('Это длинное предложение')->truncate(10); // "Это длинн..."
echo Str::from('Это длинное предложение')->truncate(10, '---'); // "Это длин---"
echo Str::from('Это длинное предложение')->truncate(10, '...', true); // "Это..."

// Трансформировать с функцией обратного вызова
echo $str->transform(function($s) {
    return strtoupper($s) . '!';
}); // "HELLO WORLD!"

// Табуляция и пробелы
echo Str::from("Hello\tWorld")->tabsToSpaces(2); // "Hello  World"
echo Str::from("Hello  World")->spacesToTabs(2); // "Hello\tWorld"

// Форматирование с sprintf
echo Str::from('Привет, %s!')->format('Иван'); // "Привет, Иван!"
```

#### Валидация и преобразование типов

```php
$str = Str::from('123');

// Проверка типов
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

// Преобразование в типы
echo $str->toNumber();           // int(123)
var_dump(Str::from('true')->toBoolean()); // bool(true)
var_dump(Str::from('true')->isBoolean()); // bool(true)

// Операции с JSON
var_dump(Str::from('{"a":1}')->isJson()); // bool(true)
echo Str::from('Hello')->toJson(); // "\"Hello\""

// Прочие проверки типов
var_dump(Str::from('a:1:{s:1:"a";i:1;}')->isSerialized()); // bool(true)
var_dump(Str::from('SGVsbG8=')->isBase64()); // bool(true)
var_dump(Str::from('2023-05-17')->isDate()); // bool(true)

// Преобразование в дату
$date = Str::from('2023-05-17')->toDate();
```

#### Сегментация строк

```php
$str = Str::from("Hello\nWorld\nExample");

// Разделить на строки
$lines = $str->lines();          // Массив объектов Str, по одному на строку

// Разделить на слова
$words = Str::from('Hello World Example')->words(); // ["Hello", "World", "Example"]

// Преобразовать в массив символов
$chars = $str->toArray();        // ["H", "e", "l", "l", "o", ... ]
```

#### Регулярные выражения

```php
$str = Str::from('Hello 123 World');

// Поиск по шаблону
$found = $str->match('/\d+/', $matches);
var_dump($found);               // bool(true)
echo $matches[0];               // "123"

// Поиск всех вхождений
$found = $str->matchAll('/\w+/', $matches);
var_dump($matches[0]);          // Массив со всеми словами
```

#### Прочие операции

```php
$str = Str::from('Hello World');

// Хеширование строки
echo $str->hash();              // SHA-256 хеш
echo $str->hash('md5');         // MD5 хеш

// Преобразование в ASCII
echo Str::from('Café')->toAscii(); // "Cafe"

// Вывод
$str->print();                  // Выводит "Hello World"

// Итерация по символам
$str->each(function($char, $index) {
    echo "$index: $char\n";
    return true; // Продолжить итерацию
});
```

#### Использование итератора

```php
$str = Str::from('Hello');

// Получить итератор
$iterator = $str->getIterator();

// Использовать в foreach
foreach ($iterator as $index => $char) {
    echo "$index: $char\n";
}
```

### StrMutable (Изменяемый)

Изменяемый класс строки модифицирует строку на месте и возвращает тот же экземпляр для цепочки методов.

#### Создание изменяемых строк

```php
use Bermuda\Stdlib\StrMutable;

// Создать из строки
$str = StrMutable::create('Hello World');

// Создать с указанием кодировки
$russianStr = StrMutable::create('Привет мир', 'UTF-8');

// Создать через конструктор
$str = new StrMutable('Hello World');

// альтернатива
$str = Stringy::mutable('Hello World');

// Установить значение строки напрямую
$str = StrMutable::create('Hello');
$str->setString('New Value');
```

Большинство методов в StrMutable имеют тот же API, что и Str, но изменяют строку на месте:

```php
$str = StrMutable::create('Hello World');

// Цепочка операций
$str->toUpperCase()
    ->trim()
    ->replace('WORLD', 'EARTH');

echo $str; // "HELLO EARTH"

// Подстрока изменяет на месте
$str->substring(0, 5);
echo $str; // "HELLO"
```

#### Доступ к массиву (изменяемый)

```php
$str = StrMutable::create('Hello');

// Чтение
echo $str[1];  // "e"

// Запись
$str[0] = 'J';
echo $str;     // "Jello"

// Удаление символа
unset($str[4]);
echo $str;     // "Jell"
```

### StringIterator

Класс StringIterator позволяет выполнять посимвольную итерацию с различными методами навигации.

#### Создание и базовое использование

```php
use Bermuda\Stdlib\StringIterator;

// Создать напрямую
$iterator = new StringIterator('Hello');

// Создать через строковый объект
$str = Str::from('World');
$iterator = $str->getIterator();

// Базовая итерация
while ($iterator->valid()) {
    echo $iterator->current();
    $iterator->next();
}
// Выводит: "World"

// Получить строку
echo $iterator->__toString(); // "World"

// Создать новый итератор с другой строкой
$newIterator = $iterator->withString('Hello');
```

#### Навигация

```php
$iterator = new StringIterator('Hello World');

// Получить текущее состояние
echo $iterator->current();   // "H" (начальная позиция равна 0)
echo $iterator->key();       // 0 (текущая позиция)

// Перемещение вперед/назад
$iterator->next();
echo $iterator->current();   // "e"

$iterator->forward(2);       // Двигаться на 2 шага вперед
echo $iterator->current();   // "l"

$iterator->backward();       // Двигаться на 1 шаг назад
echo $iterator->current();   // "e"

// Перейти к позиции
$iterator->moveTo(6);
echo $iterator->current();   // "W"

// Сбросить позицию
$iterator->rewind();
echo $iterator->key();       // 0

// Проверить позицию
var_dump($iterator->isStart()); // bool(true)
var_dump($iterator->isEnd());   // bool(false)

$iterator->moveTo($iterator->lastIndex());
var_dump($iterator->isEnd());   // bool(false)

$iterator->next();
var_dump($iterator->isEnd());   // bool(true)
var_dump($iterator->valid());   // bool(false)
```

#### Чтение

```php
$iterator = new StringIterator('Hello World');

// Прочитать следующие символы
$iterator->moveTo(6);
echo $iterator->readNext(5);    // "World"

// Прочитать до конца (когда длина равна null)
$iterator->moveTo(6);
echo $iterator->readNext();     // "World"
```

### Stringy (Статические утилиты)

Класс Stringy предоставляет статические методы утилит для манипуляции со строками.

```php
use Bermuda\Stdlib\Stringy;

// Преобразовать формат строки
echo Stringy::delimit('HelloWorld', '-');           // "hello-world"
echo Stringy::delimit('hello_world-example', '.'); // "hello.world.example"

// Обрезать пробелы
echo Stringy::trim('  Hello  ');                    // "Hello"
echo Stringy::trimStart('  Hello  ');               // "Hello  "
echo Stringy::trimEnd('  Hello  ');                 // "  Hello"

// С пользовательскими символами
echo Stringy::trim('__Hello__', '_');               // "Hello"

// Поддержка многобайтовых строк
echo Stringy::trim('  Привет  ');                   // "Привет"
```

### ClsHelper (Утилиты для работы с именами классов)

Класс ClsHelper предоставляет утилиты для работы с именами классов и пространствами имен.

```php
use Bermuda\Stdlib\ClsHelper;

$className = 'Bermuda\\Stringy\\StrMutable';

// Получить часть пространства имен
echo ClsHelper::namespace($className);    // "Bermuda\\Stringy"

// Получить базовое имя (класс без пространства имен)
echo ClsHelper::basename($className);     // "StrMutable"

// Разделить на пространство имен и имя класса
$parts = ClsHelper::split($className);
// Возвращает: [0 => 'Bermuda\\Stringy', 1 => 'StrMutable']

// Проверить имена классов
var_dump(ClsHelper::isValidName('MyClass'));          // bool(true)
var_dump(ClsHelper::isValidName('Vendor\\MyClass'));  // bool(true)
var_dump(ClsHelper::isValidName('Vendor\\MyClass', false)); // bool(false) - без пространства имен
var_dump(ClsHelper::isValidName('0InvalidClass'));    // bool(false)
```

## Работа с многобайтовыми строками

Все методы в Bermuda\Stringy правильно обрабатывают многобайтовые строки, обеспечивая корректную обработку символов для нелатинских алфавитов и специальных символов:

### Базовые операции с многобайтовыми строками

```php
// Создать с указанием кодировки
$russian = Str::from('Привет, мир!', 'UTF-8');
$chinese = Str::from('你好，世界！', 'UTF-8');
$arabic = Str::from('مرحبا بالعالم!', 'UTF-8');

// Количество символов vs количество байтов
echo $russian->length();      // 12 (количество символов)
echo $russian->getBytes();    // Больше 12 (количество байтов)

// Доступ к символам
echo $russian->charAt(0);     // "П"
echo $chinese->charAt(0);     // "你"

// Извлечение подстроки
echo $russian->substring(0, 6);  // "Привет"
echo $chinese->substring(0, 2);  // "你好"

// Преобразование регистра (где применимо)
echo $russian->toUpperCase();    // "ПРИВЕТ, МИР!"
echo $russian->toLowerCase();    // "привет, мир!"
```

### Трансформация текста

```php
// Разворот (корректно обрабатывает многобайтовые символы)
echo $russian->reverse();     // "!рим ,тевирП"

// Замена
echo $russian->replace('мир', 'свет');  // "Привет, свет!"

// Обрезка
echo Str::from('  Привет  ')->trim();  // "Привет"

// Итерация по символам
$iterator = $russian->getIterator();
foreach ($iterator as $char) {
    echo $char . ' ';  // "П р и в е т ,   м и р !"
}
```

### Доступ к массиву с многобайтовыми строками

```php
$mutable = StrMutable::create('Привет');
echo $mutable[0];      // "П"

$mutable[0] = 'К';
echo $mutable;         // "Кривет"

unset($mutable[5]);
echo $mutable;         // "Криве"
```

### Работа с разными системами письма

```php
// Японский
$japanese = Str::from('こんにちは世界', 'UTF-8');
echo $japanese->length();        // 7 символов
echo $japanese->substring(0, 5); // "こんにちは"

// Тайский (с комбинированными диакритическими знаками)
$thai = Str::from('สวัสดีโลก', 'UTF-8');
echo $thai->length();            // Корректно считает тайские символы со знаками

// Эмодзи
$emoji = Str::from('Hello 👋 World 🌍', 'UTF-8');
echo $emoji->length();           // Корректно считает символы эмодзи
echo $emoji->substring(6, 1);    // "👋"

// Смешанный текст на разных языках
$mixed = Str::from('Hello Привет 你好 مرحبا', 'UTF-8');
foreach ($mixed->words() as $word) {
    echo $word . "\n";  // Корректно разделяет слова на разных системах письма
}
```

## Лицензия

Эта библиотека лицензирована под лицензией MIT. Для получения подробностей см. файл [LICENSE](LICENSE).

## Содействие

Контрибуции приветствуются! Пожалуйста, создайте pull request в GitHub репозитории.
