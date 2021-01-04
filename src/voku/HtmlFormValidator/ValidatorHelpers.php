<?php

declare(strict_types=1);

namespace voku\HtmlFormValidator;

use voku\helper\UTF8;

/**
 * Class ValidatorHelpers
 *
 * @internal
 */
final class ValidatorHelpers
{
    /**
     * @param string $string
     *
     * @return array<int, array<bool|string|null>|string>
     */
    public static function getArgsFromString($string): array
    {
        $argsMatches = [];
        \preg_match('/\((?<args>.*?)\)$/', $string, $argsMatches);
        $string = (string) \preg_replace('/\((.*?)\)$/', '', $string);

        $args = [];
        if (isset($argsMatches['args'])) {
            $argTmpString = $argsMatches['args'];

            if (self::is_serialized($argTmpString, $argsTmpUnserialized)) {
                $args = [$argsTmpUnserialized];
            } else {
                $argsTmpArray = \explode(',', $argTmpString);
                foreach ($argsTmpArray as $argTmp) {
                    $arg = \trim($argTmp);

                    if ($arg === 'true' || $arg === 'false') {
                        $arg = (bool) $arg;
                    } elseif ($arg === 'null') {
                        $arg = null;
                    }

                    $args[] = $arg;
                }
            }
        }

        return [$string, $args];
    }

    /**
     * Tests if an input is valid PHP serialized string.
     *
     * Checks if a string is serialized using quick string manipulation
     * to throw out obviously incorrect strings. Unserialize is then run
     * on the string to perform the final verification.
     *
     * Valid serialized forms are the following:
     * <ul>
     * <li>boolean: <code>b:1;</code></li>
     * <li>integer: <code>i:1;</code></li>
     * <li>double: <code>d:0.2;</code></li>
     * <li>string: <code>s:4:"test";</code></li>
     * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
     * <li>object: <code>O:8:"stdClass":0:{}</code></li>
     * <li>null: <code>N;</code></li>
     * </ul>
     *
     * @copyright  Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
     * @license    http://sam.zoy.org/wtfpl/ WTFPL
     *
     * @param    string $value  Value to test for serialized form
     * @param    mixed  $result Result of unserialize() of the $value
     *
     * @return    bool      True if $value is serialized data, otherwise false
     */
    public static function is_serialized($value, &$result = null): bool
    {
        // Bit of a give away this one
        if (!\is_string($value)) {
            return false;
        }

        // Serialized false, return true. unserialize() returns false on an
        // invalid string or it could return false if the string is serialized
        // false, eliminate that possibility.
        if ($value === 'b:0;') {
            $result = false;

            return true;
        }

        $length = UTF8::strlen($value);
        $end = '';
        switch ($value[0]) {
            case 's':
                if ($value[$length - 2] !== '"') {
                    return false;
                }
            // no break
            case 'b':
            case 'i':
            case 'd':
                // This looks odd but it is quicker than isset()ing
                $end .= ';';
            // no break
            case 'a':
            case 'O':
                $end .= '}';
                if ($value[1] !== ':') {
                    return false;
                }
                switch ($value[2]) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                        break;
                    default:
                        return false;
                }
            // no break
            case 'N':
                $end .= ';';
                if ($value[$length - 1] !== $end[0]) {
                    return false;
                }

                break;
            default:
                return false;
        }

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        if (($result = @\unserialize($value, [])) === false) {
            $result = null;

            return false;
        }

        return true;
    }
}
