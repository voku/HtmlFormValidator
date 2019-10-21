<?php

declare(strict_types=1);

namespace voku\HtmlFormValidator;

use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Factory;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Rules\Date;
use Respect\Validation\Rules\Email;
use Respect\Validation\Rules\HexRgbColor;
use Respect\Validation\Rules\Numeric;
use Respect\Validation\Rules\Phone;
use Respect\Validation\Rules\Url;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;
use voku\helper\UTF8;
use voku\HtmlFormValidator\Exceptions\NoValidationRule;
use voku\HtmlFormValidator\Exceptions\UnknownFilter;
use voku\HtmlFormValidator\Exceptions\UnknownValidationRule;

class Validator
{
    /**
     * @var HtmlDomParser
     */
    private $htmlElementDocument;

    /**
     * @var string[][]
     */
    private $rules = [];

    /**
     * @var string[][]
     */
    private $required_rules = [];

    /**
     * @var string[][]
     */
    private $rules_namespaces;

    /**
     * @var string[][]
     */
    private $filters = [];

    /**
     * @var callable[]
     */
    private $filters_custom = [];

    /**
     * @var callable|null
     */
    private $translator;

    /**
     * @var ValidatorRulesManager
     */
    private $validatorRulesManager;

    /**
     * @var string
     */
    private $selector;

    /**
     * @param string $html
     * @param string $selector
     */
    public function __construct($html, $selector = '')
    {
        $this->rules_namespaces['append'] = [];
        $this->rules_namespaces['prepend'] = [];
        $this->prependRulesNamespace('voku\\HtmlFormValidator\\Rules');

        $this->validatorRulesManager = new ValidatorRulesManager();

        $this->htmlElementDocument = HtmlDomParser::str_get_html($html);
        $this->selector = $selector;

        $this->parseHtmlDomForRules();
    }

    /**
     * @param string   $name   <p>A name for the "data-filter"-attribute in the dom.</p>
     * @param callable $filter <p>A custom filter.</p>
     */
    public function addCustomFilter(string $name, callable $filter)
    {
        $this->filters_custom[$name] = $filter;
    }

    /**
     * @param string              $name      <p>A name for the "data-validator"-attribute in the dom.</p>
     * @param AbstractRule|string $validator <p>A custom validation class.</p>
     */
    public function addCustomRule(string $name, $validator)
    {
        $this->validatorRulesManager->addCustomRule($name, $validator);
    }

    /**
     * @param mixed  $currentFieldData
     * @param string $fieldFilter
     *
     * @throws UnknownFilter
     *
     * @return mixed|string|null
     */
    private function applyFilter($currentFieldData, string $fieldFilter)
    {
        if ($currentFieldData === null) {
            return null;
        }

        //
        // fixed filters
        //

        switch ($fieldFilter) {
            case 'escape':
                return \htmlentities($currentFieldData, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
        }

        //
        // get arguments
        //

        list($fieldFilter, $fieldFilterArgs) = ValidatorHelpers::getArgsFromString($fieldFilter);

        $currentFieldData = (array) $currentFieldData;
        foreach ($fieldFilterArgs as $arg) {
            $currentFieldData[] = $arg;
        }

        //
        // custom filters
        //

        if (isset($this->filters_custom[$fieldFilter])) {
            return \call_user_func_array($this->filters_custom[$fieldFilter], $currentFieldData);
        }

        //
        // dynamic filters
        //

        if (\method_exists(UTF8::class, $fieldFilter)) {
            $currentFieldData = \call_user_func_array([UTF8::class, $fieldFilter], $currentFieldData);
        } else {
            throw new UnknownFilter(
                'No filter available for "' . $fieldFilter . '"'
            );
        }

        return $currentFieldData;
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function autoSelectRuleByInputType(string $type)
    {
        $matchingArray = [
            'email'  => Email::class,
            'url'    => Url::class,
            'color'  => HexRgbColor::class,
            'number' => Numeric::class,
            'range'  => Numeric::class,
            'tel'    => Phone::class,
            'date'   => Date::class,
            // -> this need localisation e.g. for german / us / etc.
            //'time'   => Time::class,
            //'month'  => Month::class,
            //'week'   => Week::class,
        ];

        return $matchingArray[$type] ?? null;
    }

    /**
     * @param string $phpNamespace
     *
     * @return string
     */
    private function filterRulesNamespace(string $phpNamespace): string
    {
        $namespaceSeparator = '\\';
        $rulePrefix = \rtrim($phpNamespace, $namespaceSeparator);

        return $rulePrefix . $namespaceSeparator;
    }

    /**
     * Get the filters that will be applied.
     *
     * @return string[][]
     */
    public function getAllFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get the rules that will be applied.
     *
     * @return string[][]
     */
    public function getAllRules(): array
    {
        return $this->rules;
    }

    /**
     * @param array  $htmlElementValues
     * @param string $field
     *
     * @return mixed|null
     */
    private function getCurrentFieldValue(array $htmlElementValues, string $field)
    {
        $fieldArrayPos = UTF8::strpos($field, '[');
        if ($fieldArrayPos !== false) {
            $fieldStart = UTF8::substr($field, 0, $fieldArrayPos);
            $fieldArray = UTF8::substr($field, $fieldArrayPos);
            $fieldHelperChar = '';
            $fieldArrayTmp = \preg_replace_callback(
                '/\[([^]]+)]/',
                static function ($match) use ($fieldHelperChar) {
                    return $match[1] . $fieldHelperChar;
                },
                $fieldArray
            );
            $fieldArrayTmp = \explode($fieldHelperChar, \trim($fieldArrayTmp, $fieldHelperChar));

            $i = 0;
            $fieldHelper = [];
            foreach ($fieldArrayTmp as $fieldArrayTmpInner) {
                $fieldHelper[$i] = $fieldArrayTmpInner;

                $i++;
            }

            $currentFieldValue = null;

            switch ($i) {
                case 4:
                    if (isset($htmlElementValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]][$fieldHelper[2]][$fieldHelper[3]])) {
                        $currentFieldValue = $htmlElementValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]][$fieldHelper[2]][$fieldHelper[3]];
                    }

                    break;
                case 3:
                    if (isset($htmlElementValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]][$fieldHelper[2]])) {
                        $currentFieldValue = $htmlElementValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]][$fieldHelper[2]];
                    }

                    break;
                case 2:
                    if (isset($htmlElementValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]])) {
                        $currentFieldValue = $htmlElementValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]];
                    }

                    break;
                case 1:
                    if (isset($htmlElementValues[$fieldStart][$fieldHelper[0]])) {
                        $currentFieldValue = $htmlElementValues[$fieldStart][$fieldHelper[0]];
                    }

                    break;
            }
        } else {
            $currentFieldValue = $htmlElementValues[$field] ?? null;
        }

        return $currentFieldValue;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->htmlElementDocument->html();
    }

    /**
     * Get the required rules that will be applied.
     *
     * @return string[][]
     */
    public function getRequiredRules(): array
    {
        return $this->required_rules;
    }

    /**
     * Find the first html-element on page or via css-selector, and parse <input>-elements.
     *
     * @return bool
     */
    public function parseHtmlDomForRules(): bool
    {
        // init
        $this->rules = [];
        $inputHtmlElement = [];

        if ($this->selector) {
            $htmlElements = $this->htmlElementDocument->find($this->selector);
        } else {
            $htmlElements = $this->htmlElementDocument->find('form');
        }

        if (\count($htmlElements) === 0) {
            return false;
        }

        // get the first found html-element
        $htmlElement = $htmlElements[0];

        // get the "<form>"-id
        if ($htmlElement->id) {
            $htmlElementHelperId = $htmlElement->id;
        } elseif ($this->selector) {
            $htmlElementHelperId = 'html-element-validator-tmp-' . $this->selector;
        } else {
            $cssClassesTmp = \str_replace(
                ' ',
                '.',
                $htmlElement->getAttribute('class')
            );
            $fakeCssSelector = $htmlElement->getNode()->getNodePath() . '/' . $cssClassesTmp;

            $htmlElementHelperId = 'html-element-validator-tmp-' . $fakeCssSelector;
        }

        $htmlElementTagSelector = 'input, textarea, select';

        // get the <input>-elements from the htmlElement
        $inputFromFields = $htmlElement->find($htmlElementTagSelector);
        foreach ($inputFromFields as $inputhtmlElementField) {
            $this->parseInputForRules($inputhtmlElementField, $htmlElementHelperId, $htmlElement);
            $this->parseInputForFilter($inputhtmlElementField, $htmlElementHelperId);
        }

        // get the <input>-elements with a matching form="id"
        if (\strpos($htmlElementHelperId, 'html-element-validator-tmp') !== 0) {
            $inputFromFieldsTmpAll = $this->htmlElementDocument->find($htmlElementTagSelector);
            foreach ($inputFromFieldsTmpAll as $inputFromFieldTmp) {
                if ($inputFromFieldTmp->form === $htmlElementHelperId) {
                    $this->parseInputForRules($inputFromFieldTmp, $htmlElementHelperId);
                    $this->parseInputForFilter($inputFromFieldTmp, $htmlElementHelperId);
                }
            }
        }

        return \count($inputHtmlElement) > 0;
    }

    /**
     * Determine if element has filter attributes, and save the given filter.
     *
     * @param SimpleHtmlDom $inputField
     * @param string        $htmlElementHelperId
     */
    private function parseInputForFilter(SimpleHtmlDom $inputField, string $htmlElementHelperId)
    {
        if (!$inputField->hasAttribute('data-filter')) {
            return;
        }

        $inputName = $inputField->getAttribute('name');
        $inputFilter = $inputField->getAttribute('data-filter');

        if (!$inputFilter) {
            $inputFilter = 'htmlentities';
        }

        $this->filters[$htmlElementHelperId][$inputName] = $inputFilter;
    }

    /**
     * Determine if element has validator attributes, and save the given rule.
     *
     * @param SimpleHtmlDom      $htmlElementField
     * @param string             $htmlElementHelperId
     * @param SimpleHtmlDom|null $htmlElement
     */
    private function parseInputForRules(SimpleHtmlDom $htmlElementField, string $htmlElementHelperId, SimpleHtmlDom $htmlElement = null)
    {
        if (!$htmlElementField->hasAttribute('data-validator')) {
            return;
        }

        $inputName = $htmlElementField->getAttribute('name');
        $inputType = $htmlElementField->getAttribute('type');
        $inputRule = $htmlElementField->getAttribute('data-validator');

        if (\stripos($inputRule, 'auto') !== false) {

            //
            // select default rule by input-type
            //

            if ($inputType) {
                $selectedRule = $this->autoSelectRuleByInputType($inputType);
                if ($selectedRule) {
                    $inputRule .= '|' . $selectedRule;
                }
            }

            //
            // html5 pattern to regex
            //

            $inputPattern = $htmlElementField->getAttribute('pattern');
            if ($inputPattern) {
                $inputRule .= '|regex(/' . $inputPattern . '/)';
            }

            //
            // min- / max values
            //

            $inputMinLength = $htmlElementField->getAttribute('minlength');
            if ($inputMinLength) {
                $inputRule .= '|minLength(' . \serialize($inputMinLength) . ')';
            }

            $inputMaxLength = $htmlElementField->getAttribute('maxlength');
            if ($inputMaxLength) {
                $inputRule .= '|maxLength(' . \serialize($inputMaxLength) . ')';
            }

            $inputMin = $htmlElementField->getAttribute('min');
            if ($inputMin) {
                $inputRule .= '|min(' . \serialize($inputMin) . ')';
            }

            $inputMax = $htmlElementField->getAttribute('max');
            if ($inputMax) {
                $inputRule .= '|max(' . \serialize($inputMax) . ')';
            }
        }

        /** @noinspection MissingOrEmptyGroupStatementInspection */
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        if (
            \stripos($inputRule, 'NonStrict') !== false
            ||
            \stripos($inputRule, 'non-strict') !== false
        ) {
            // do not check
        } else {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if ($htmlElementField->tag === 'select') {
                $selectableValues = [];
                foreach ($htmlElementField->getElementsByTagName('option') as $option) {
                    $selectableValues[] = $option->getNode()->nodeValue;
                }
                $inputRule .= '|in(' . \serialize($selectableValues) . ')';
            } elseif (
                (
                    $inputType === 'checkbox'
                    ||
                    $inputType === 'radio'
                )
                &&
                $htmlElement
            ) {
                $selectableValues = [];

                $htmlElementFieldNames = $htmlElement->find('[name=\'' . $htmlElementField->getAttribute('name') . '\']');

                if ($htmlElementFieldNames) {
                    foreach ($htmlElementFieldNames as $htmlElementFieldName) {
                        $selectableValues[] = $htmlElementFieldName->value;
                    }
                }

                $inputRule .= '|in(' . \serialize($selectableValues) . ')';
            }
        }

        if ($htmlElementField->hasAttribute('required')) {
            $this->required_rules[$htmlElementHelperId][$inputName] = $inputRule;
        }

        $this->rules[$htmlElementHelperId][$inputName] = $inputRule;
    }

    /**
     * @param string $phpNamespace <p>e.g.: "voku\\HtmlFormValidator\\Rules"</p>
     *
     * @return $this
     */
    public function appendRulesNamespace(string $phpNamespace): self
    {
        $this->rules_namespaces['append'][] = $this->filterRulesNamespace($phpNamespace);

        return $this;
    }

    /**
     * @param string $phpNamespace <p>e.g.: "voku\\HtmlFormValidator\\Rules"</p>
     *
     * @return $this
     */
    public function prependRulesNamespace(string $phpNamespace): self
    {
        \array_unshift($this->rules_namespaces['prepend'], $this->filterRulesNamespace($phpNamespace));

        return $this;
    }

    /**
     * @return callable|null
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param callable $translator
     *
     * @return Validator
     */
    public function setTranslator(callable $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * Loop the htmlElement data through htmlElement rules.
     *
     * @param array $htmlElementValues
     * @param bool  $useNoValidationRuleException
     *
     * @throws UnknownValidationRule
     *
     * @return ValidatorResult
     */
    public function validate(array $htmlElementValues, $useNoValidationRuleException = false): ValidatorResult
    {
        if (
            $useNoValidationRuleException === true
            &&
            \count($this->rules) === 0
        ) {
            throw new NoValidationRule(
                'No rules defined in the html.'
            );
        }

        // init
        $validatorResult = new ValidatorResult($this->htmlElementDocument);

        foreach ($this->rules as $htmlElementHelperId => $htmlElementFields) {
            foreach ($htmlElementFields as $field => $fieldRuleOuter) {
                $currentFieldValue = $this->getCurrentFieldValue($htmlElementValues, $field);

                //
                // use the filter
                //

                if (isset($this->filters[$htmlElementHelperId][$field])) {
                    $filtersOuter = $this->filters[$htmlElementHelperId][$field];
                    $fieldFilters = \preg_split("/\|+(?![^(]*\))/", $filtersOuter);

                    foreach ($fieldFilters as $fieldFilter) {
                        if (!$fieldFilter) {
                            continue;
                        }

                        $currentFieldValue = $this->applyFilter($currentFieldValue, $fieldFilter);
                    }
                }

                //
                // save the new values into the result-object
                //

                $validatorResult->saveValue($field, $currentFieldValue);

                //
                // skip validation, if there was no value and validation is not required
                //

                if (
                    $currentFieldValue === null
                    &&
                    !isset($this->required_rules[$htmlElementHelperId][$field])
                ) {
                    continue;
                }

                //
                // use the validation rules from the dom
                //

                $fieldRules = \preg_split("/\|+(?![^(?:]*\))/", $fieldRuleOuter);

                $hasPassed = true;
                foreach ($fieldRules as $fieldRule) {
                    if (!$fieldRule) {
                        continue;
                    }

                    $validationClassArray = $this->validatorRulesManager->getClassViaAlias($fieldRule);

                    if ($validationClassArray['object']) {
                        $validationClass = $validationClassArray['object'];
                    } elseif ($validationClassArray['class']) {
                        $validationClass = $validationClassArray['class'];
                    } else {
                        $validationClass = null;
                    }

                    $validationClassArgs = $validationClassArray['classArgs'] ?? null;

                    if ($validationClass instanceof AbstractRule) {
                        $respectValidator = $validationClass;
                    } else {
                        $respectValidatorFactory = new Factory();
                        foreach ($this->rules_namespaces['prepend'] as $rules_namespace) {
                            $respectValidatorFactory->prependRulePrefix($rules_namespace);
                        }
                        foreach ($this->rules_namespaces['append'] as $rules_namespace) {
                            $respectValidatorFactory->appendRulePrefix($rules_namespace);
                        }

                        try {
                            if ($validationClassArgs !== null) {
                                $respectValidator = $respectValidatorFactory->rule($validationClass, $validationClassArgs);
                            } else {
                                $respectValidator = $respectValidatorFactory->rule($validationClass);
                            }
                        } catch (ComponentException $componentException) {
                            throw new UnknownValidationRule(
                                'No rule defined for: ' . $field . ' (rule: ' . $fieldRule . ' | class: ' . $validationClass . ')',
                                500,
                                $componentException
                            );
                        }
                    }

                    $translator = $this->getTranslator();

                    try {
                        $respectValidator->assert($currentFieldValue);
                    } catch (NestedValidationException $nestedValidationException) {
                        if ($translator) {
                            $nestedValidationException->setParam('translator', $translator);
                        }

                        $validatorResult->setError($field, $fieldRule, $nestedValidationException->getFullMessage(), $currentFieldValue);
                        $hasPassed = false;
                    } catch (ValidationException $validationException) {
                        if ($translator) {
                            $validationException->setParam('translator', $translator);
                        }

                        $validatorResult->setError($field, $fieldRule, $validationException->getMainMessage(), $currentFieldValue);
                        $hasPassed = false;
                    }
                }

                if ($hasPassed === true) {
                    $validatorResult->setValue($field, $currentFieldValue);
                }
            }
        }

        $validatorResult->writeErrorsIntoTheDom();

        return $validatorResult;
    }
}
