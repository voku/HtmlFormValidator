<?php

namespace voku\HtmlFormValidator;

use Respect\Validation\Rules\AbstractRule;
use voku\helper\UTF8;

class ValidatorRulesManager
{

  /**
   * @var string[]|AbstractRule[]
   */
  private $rules = [];

  /**
   * @param string              $name
   * @param string|AbstractRule $validatorClassName
   */
  public function addCustomRule(string $name, $validatorClassName)
  {
    $this->rules[$name] = $validatorClassName;
  }

  /**
   * @param string $rule
   *
   * @return array <p>keys: 'class', 'classArgs', 'object'</p>
   */
  public function getClassViaAlias(string $rule): array
  {
    if (!$rule) {
      return [
          'class'     => null,
          'classArgs' => null,
          'object'    => null,
      ];
    }

    if (isset($this->rules[$rule])) {
      $classWithNamespace = $this->rules[$rule];
    } else {
      $classWithNamespace = $rule;
    }

    if ($classWithNamespace instanceof AbstractRule) {

      return [
          'class'     => null,
          'classArgs' => null,
          'object'    => $classWithNamespace,
      ];
    }

    if (\strpos($classWithNamespace, "\\") !== false) {
      $class =
          \substr(
              \strrchr(
                  $classWithNamespace,
                  "\\"
              ),
              1

          );
    } else {
      $class = $classWithNamespace;
    }

    $class = \lcfirst(\trim($class));

    $classArgsMatches = [];
    \preg_match('/\((?<args>.*?)\)$/', $class, $classArgsMatches);
    $class = \preg_replace('/\((.*?)\)$/', '', $class);

    $classArgs = [];
    if (isset($classArgsMatches['args'])) {
      $classArgsTmp = $classArgsMatches['args'];

      if ($this->is_serialized($classArgsTmp, $classArgsTmpUnserialized)) {

        $classArgs = [$classArgsTmpUnserialized];

      } else {

        $classArgsTmpArray = explode(',', $classArgsTmp);
        foreach ($classArgsTmpArray as $classArgsTmp) {
          $classArg = trim($classArgsTmp);

          if ($classArg === 'true' || $classArg === 'false') {
            $classArg = (bool)$classArg;
          } else if ($classArgs === 'null') {
            $classArg = null;
          }

          $classArgs[] = $classArg;
        }

      }
    }

    return [
        'class'     => $class,
        'classArgs' => (\count($classArgs) !== 0 ? $classArgs : null),
        'object'    => null,
    ];
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
   * @author     Chris Smith <code+php@chris.cs278.org>
   * @copyright  Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
   * @license    http://sam.zoy.org/wtfpl/ WTFPL
   *
   * @param    string $value  Value to test for serialized form
   * @param    mixed  $result Result of unserialize() of the $value
   *
   * @return    boolean      True if $value is serialized data, otherwise false
   */
  private function is_serialized($value, &$result = null): bool
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
      case 'b':
      case 'i':
      case 'd':
        // This looks odd but it is quicker than isset()ing
        $end .= ';';
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
    if (($result = @unserialize($value, [])) === false) {
      $result = null;

      return false;
    }

    return true;
  }

}
