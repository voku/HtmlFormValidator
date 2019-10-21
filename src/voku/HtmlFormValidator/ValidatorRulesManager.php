<?php

namespace voku\HtmlFormValidator;

use Respect\Validation\Rules\AbstractRule;

class ValidatorRulesManager
{
    /**
     * @var AbstractRule[]|string[]
     */
    private $rules = [];

    /**
     * @param string              $name
     * @param AbstractRule|string $validatorClassName
     */
    public function addCustomRule(string $name, $validatorClassName)
    {
        $this->rules[$name] = $validatorClassName;
    }

    /**
     * @param string $rule
     *
     * @return array
     *               <p>keys: 'class', 'classArgs', 'object'</p>
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
        if (\strpos($classWithNamespace, '\\') !== false) {
            $class =
                \substr(
                    (string) \strrchr(
                        $classWithNamespace,
                        '\\'
                    ),
                    1
                );
        } else {
            $class = $classWithNamespace;
        }

        $class = \ucfirst(\trim(\str_replace(['-', '_'], '', $class)));
        list($class, $classArgs) = ValidatorHelpers::getArgsFromString($class);

        return [
            'class'     => $class,
            'classArgs' => (\count($classArgs) !== 0 ? $classArgs : null),
            'object'    => null,
        ];
    }
}
