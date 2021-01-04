<?php

namespace voku\HtmlFormValidator;

use Respect\Validation\Rules\AbstractRule;

class ValidatorRulesManager
{
    /**
     * @var AbstractRule[]|class-string<AbstractRule>[]
     */
    private $rules = [];

    /**
     * @param string                     $name
     * @param AbstractRule|class-string<AbstractRule> $validatorClassName
     *
     * @return void
     */
    public function addCustomRule(string $name, $validatorClassName)
    {
        $this->rules[$name] = $validatorClassName;
    }

    /**
     * @param string $rule
     *
     * @return array{
     *     class: null|string,
     *     classArgs: null|array<array-key, mixed>,
     *     object: null|AbstractRule
     * }
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
        [$class, $classArgs] = ValidatorHelpers::getArgsFromString($class);
        \assert(\is_string($class));

        /** @noinspection UnnecessaryEmptinessCheckInspection */
        return [
            'class'     => $class,
            'classArgs' => (\is_array($classArgs) && \count($classArgs) !== 0 ? $classArgs : null),
            'object'    => null,
        ];
    }
}
