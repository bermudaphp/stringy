<?php

declare(strict_types=1);

namespace Bermuda\Stringy;

/**
 * Helper class for working with Unicode characters
 *
 * Provides utility methods for character transliteration, classification,
 * and conversion between Unicode and ASCII.
 */
final class Unicode
{
    /**
     * Convert a string to ASCII
     *
     * @param string $string String to convert
     * @param string $language Source text language (for specific transliteration rules)
     * @param bool $strict Strict mode (without preserving special characters)
     * @return string ASCII representation of the string
     */
    public static function toAscii(string $string, string $language = '', bool $strict = false): string
    {
        if ($string === '') {
            return '';
        }

        // Use transliterator if available
        if (function_exists('transliterator_transliterate')) {
            $result = transliterator_transliterate('Any-Latin; Latin-ASCII', $string);

            // Language-specific replacements if language is specified
            $result = self::applyLanguageSpecificReplacements($result, $language);

            // If strict mode is needed, remove all non-ASCII characters
            if ($strict) {
                $result = preg_replace('/[^\x00-\x7F]/u', '', $result);
            }

            return $result;
        }

        // Fallback method if transliterator is not available
        return self::fallbackToAscii($string, $language, $strict);
    }

    /**
     * Fallback method for converting string to ASCII
     *
     * @param string $string String to convert
     * @param string $language Source language identifier
     * @param bool $strict Whether to remove all non-ASCII characters
     * @return string ASCII representation of the string
     */
    private static function fallbackToAscii(string $string, string $language, bool $strict): string
    {
        // Character table for conversion
        $chars = [
            // Cyrillic (Russian, Ukrainian, Belarusian)
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',
            'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

            // Ukrainian specific
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

            // Belarusian specific
            'ў' => 'u',

            // Western European diacritical marks
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
            'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i',
            'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o',
            'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y', 'œ' => 'oe', 'ß' => 'ss',

            // Capital letters - Cyrillic
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
            'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
            'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '',
            'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',

            // Ukrainian capitals
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',

            // Belarusian capitals
            'Ў' => 'U',

            // Capital Latin letters with diacritical marks
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE',
            'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I',
            'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Þ' => 'TH', 'Ÿ' => 'Y', 'Œ' => 'OE',
        ];

        // Apply language-specific rules if language is specified
        if ($language !== '') {
            $chars = self::mergeWithLanguageChars($chars, $language);
        }

        // Replace characters
        $result = strtr($string, $chars);

        // Remove remaining non-ASCII characters in strict mode
        if ($strict) {
            $result = preg_replace('/[^\x00-\x7F]/u', '', $result);
        }

        return $result;
    }

    /**
     * Apply language-specific replacements
     *
     * @param string $string String to process
     * @param string $language Language identifier
     * @return string String with language-specific replacements
     */
    private static function applyLanguageSpecificReplacements(string $string, string $language): string
    {
        if ($language === '') {
            return $string;
        }

        $language = strtolower($language);

        switch ($language) {
            case 'ru':
            case 'rus':
            case 'russian':
                // Russian-specific replacements
                return strtr($string, [
                    'Щ' => 'Shch', 'щ' => 'shch',
                    'Ё' => 'Yo', 'ё' => 'yo',
                ]);

            case 'uk':
            case 'ukr':
            case 'ukrainian':
                // Ukrainian-specific replacements
                return strtr($string, [
                    'Г' => 'H', 'г' => 'h',
                    'И' => 'Y', 'и' => 'y',
                ]);

            case 'de':
            case 'deu':
            case 'german':
                // German-specific replacements
                return strtr($string, [
                    'Ä' => 'Ae', 'ä' => 'ae',
                    'Ö' => 'Oe', 'ö' => 'oe',
                    'Ü' => 'Ue', 'ü' => 'ue',
                    'ß' => 'ss',
                ]);

            case 'fr':
            case 'fra':
            case 'french':
                // French-specific replacements
                return strtr($string, [
                    'Œ' => 'Oe', 'œ' => 'oe',
                ]);

            default:
                return $string;
        }
    }

    /**
     * Merge the base replacement table with language-specific one
     *
     * @param array<string, string> $chars Base character replacements
     * @param string $language Language identifier
     * @return array<string, string> Merged character replacements
     */
    private static function mergeWithLanguageChars(array $chars, string $language): array
    {
        $language = strtolower($language);

        switch ($language) {
            case 'ru':
            case 'rus':
            case 'russian':
                return array_merge($chars, [
                    'щ' => 'shch', 'Щ' => 'Shch',
                    'ё' => 'yo', 'Ё' => 'Yo',
                ]);

            case 'uk':
            case 'ukr':
            case 'ukrainian':
                return array_merge($chars, [
                    'г' => 'h', 'Г' => 'H',
                    'и' => 'y', 'И' => 'Y',
                ]);

            case 'de':
            case 'deu':
            case 'german':
                return array_merge($chars, [
                    'ä' => 'ae', 'Ä' => 'Ae',
                    'ö' => 'oe', 'Ö' => 'Oe',
                    'ü' => 'ue', 'Ü' => 'Ue',
                ]);

            case 'fr':
            case 'fra':
            case 'french':
                return array_merge($chars, [
                    'œ' => 'oe', 'Œ' => 'Oe',
                ]);

            default:
                return $chars;
        }
    }

    /**
     * Check if a character is a letter (including unicode characters)
     *
     * @param string $char Character to check
     * @return bool True if the character is a letter
     */
    public static function isLetter(string $char): bool
    {
        if (mb_strlen($char) !== 1) {
            return false;
        }

        return preg_match('/^\p{L}$/u', $char) === 1;
    }

    /**
     * Check if a character is a digit
     *
     * @param string $char Character to check
     * @return bool True if the character is a digit
     */
    public static function isDigit(string $char): bool
    {
        if (mb_strlen($char) !== 1) {
            return false;
        }

        return preg_match('/^\p{N}$/u', $char) === 1;
    }

    /**
     * Check if a character is whitespace
     *
     * @param string $char Character to check
     * @return bool True if the character is whitespace
     */
    public static function isWhitespace(string $char): bool
    {
        if (mb_strlen($char) !== 1) {
            return false;
        }

        return preg_match('/^\s$/u', $char) === 1;
    }

    /**
     * Check if a character is a punctuation mark
     *
     * @param string $char Character to check
     * @return bool True if the character is punctuation
     */
    public static function isPunctuation(string $char): bool
    {
        if (mb_strlen($char) !== 1) {
            return false;
        }

        return preg_match('/^\p{P}$/u', $char) === 1;
    }

    /**
     * Check if a character is an uppercase character
     *
     * @param string $char Character to check
     * @return bool True if the character is uppercase
     */
    public static function isUpperCase(string $char): bool
    {
        if (mb_strlen($char) !== 1) {
            return false;
        }

        return mb_strtoupper($char) === $char && mb_strtolower($char) !== $char;
    }

    /**
     * Check if a character is a lowercase character
     *
     * @param string $char Character to check
     * @return bool True if the character is lowercase
     */
    public static function isLowerCase(string $char): bool
    {
        if (mb_strlen($char) !== 1) {
            return false;
        }

        return mb_strtolower($char) === $char && mb_strtoupper($char) !== $char;
    }

    /**
     * Get the Unicode category of a character
     *
     * @param string $char Character to analyze
     * @return string|null Unicode category or null if the category could not be determined
     */
    public static function getCategory(string $char): ?string
    {
        if (mb_strlen($char) !== 1) {
            return null;
        }

        // Unicode categories
        $categories = [
            'Lu' => '/^\p{Lu}$/u',  // Uppercase letter
            'Ll' => '/^\p{Ll}$/u',  // Lowercase letter
            'Lt' => '/^\p{Lt}$/u',  // Titlecase letter
            'Lm' => '/^\p{Lm}$/u',  // Modifier letter
            'Lo' => '/^\p{Lo}$/u',  // Other letter
            'Mn' => '/^\p{Mn}$/u',  // Non-spacing mark
            'Mc' => '/^\p{Mc}$/u',  // Spacing combining mark
            'Me' => '/^\p{Me}$/u',  // Enclosing mark
            'Nd' => '/^\p{Nd}$/u',  // Decimal digit
            'Nl' => '/^\p{Nl}$/u',  // Letter number
            'No' => '/^\p{No}$/u',  // Other number
            'Pc' => '/^\p{Pc}$/u',  // Connector punctuation
            'Pd' => '/^\p{Pd}$/u',  // Dash punctuation
            'Ps' => '/^\p{Ps}$/u',  // Open punctuation
            'Pe' => '/^\p{Pe}$/u',  // Close punctuation
            'Pi' => '/^\p{Pi}$/u',  // Initial quote punctuation
            'Pf' => '/^\p{Pf}$/u',  // Final quote punctuation
            'Po' => '/^\p{Po}$/u',  // Other punctuation
            'Sm' => '/^\p{Sm}$/u',  // Math symbol
            'Sc' => '/^\p{Sc}$/u',  // Currency symbol
            'Sk' => '/^\p{Sk}$/u',  // Modifier symbol
            'So' => '/^\p{So}$/u',  // Other symbol
            'Zs' => '/^\p{Zs}$/u',  // Space separator
            'Zl' => '/^\p{Zl}$/u',  // Line separator
            'Zp' => '/^\p{Zp}$/u',  // Paragraph separator
            'Cc' => '/^\p{Cc}$/u',  // Control character
            'Cf' => '/^\p{Cf}$/u',  // Format character
            'Cs' => '/^\p{Cs}$/u',  // Surrogate
            'Co' => '/^\p{Co}$/u',  // Private use
            'Cn' => '/^\p{Cn}$/u',  // Unassigned
        ];

        foreach ($categories as $category => $pattern) {
            if (preg_match($pattern, $char) === 1) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Get the unicode code point of a character
     *
     * @param string $char Character
     * @return int|null Code point or null if the string is not a single character
     */
    public static function ord(string $char): ?int
    {
        if (mb_strlen($char) !== 1) {
            return null;
        }

        $code = mb_ord($char);
        return $code !== false ? $code : null;
    }

    /**
     * Convert a unicode code point to a character
     *
     * @param int $code Code point
     * @return string Character
     */
    public static function chr(int $code): string
    {
        return mb_chr($code);
    }
}