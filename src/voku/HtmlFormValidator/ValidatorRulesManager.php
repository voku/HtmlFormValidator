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
   * @return null|string|AbstractRule
   */
  public function getClassViaAlias($rule)
  {
    if (!$rule) {
      return null;
    }

    if (isset($this->rules[$rule])) {
      $classWithNamespace = $this->rules[$rule];
    } else {
      $classWithNamespace = $rule;
    }

    if ($classWithNamespace instanceof AbstractRule) {
      return $classWithNamespace;
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

    return $class;
  }
}
