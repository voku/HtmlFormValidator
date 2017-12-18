<?php

namespace voku\HtmlFormValidator;

use Respect\Validation\Rules\AbstractRule;

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

    // remove the namespace
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

    list($class, $classArgs) = ValidatorHelpers::getArgsFromString($class);

    return [
        'class'     => $class,
        'classArgs' => (\count($classArgs) !== 0 ? $classArgs : null),
        'object'    => null,
    ];
  }

}
