<?php

declare(strict_types=1);

namespace voku\HtmlFormValidator;

use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Factory;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Rules\Email;
use Respect\Validation\Rules\Url;
use Respect\Validation\Validator as RespectValidator;
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
  private $formDocument;

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
  private $filters = [];

  /**
   * @var callable[]
   */
  private $filters_custom = [];

  /**
   * @var ValidatorRulesManager
   */
  private $validatorRulesManager;

  /**
   * @var string
   */
  private $selector;

  /**
   * @param string $formHTML
   * @param string $selector
   */
  public function __construct($formHTML, $selector = '')
  {
    $this->validatorRulesManager = new ValidatorRulesManager();

    $this->formDocument = HtmlDomParser::str_get_html($formHTML);
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
   * @param string|AbstractRule $validator <p>A custom validation class.</p>
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

    if (isset($this->filters_custom[$fieldFilter])) {
      return \call_user_func($this->filters_custom[$fieldFilter], $currentFieldData);
    }

    switch ($fieldFilter) {
      case 'trim':
        return \trim($currentFieldData);
      case 'escape':
        return \htmlentities($currentFieldData, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    if (method_exists(UTF8::class, $fieldFilter)) {
      $currentFieldData = \call_user_func([UTF8::class, $fieldFilter], $currentFieldData);
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
        'email' => Email::class,
        'url'   => Url::class,

        //
        // TODO@me -> take a look here
        // -> https://github.com/xtreamwayz/html-form-validator/blob/master/src/FormElement/Number.php
        //
    ];

    return $matchingArray[$type] ?? null;
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
   * @param array  $formValues
   * @param string $field
   *
   * @return mixed|null
   */
  private function getCurrentFieldValue(array $formValues, string $field)
  {
    $fieldArrayPos = UTF8::strpos($field, '[');
    if ($fieldArrayPos !== false) {
      $fieldStart = UTF8::substr($field, 0, $fieldArrayPos);
      $fieldArray = UTF8::substr($field, $fieldArrayPos);
      $fieldHelperChar = '';
      $fieldArrayTmp = preg_replace_callback(
          '/\[([^\]]+)\]/',
          function ($match) use ($fieldHelperChar) {
            return $match[1] . $fieldHelperChar;
          },
          $fieldArray
      );
      $fieldArrayTmp = explode($fieldHelperChar, trim($fieldArrayTmp, $fieldHelperChar));

      $i = 0;
      $fieldHelper = [];
      foreach ($fieldArrayTmp as $fieldArrayTmpInner) {
        $fieldHelper[$i] = $fieldArrayTmpInner;

        $i++;
      }

      $currentFieldValue = null;

      switch ($i) {
        case 4:
          if (isset($formValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]][$fieldHelper[2]][$fieldHelper[3]])) {
            $currentFieldValue = $formValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]][$fieldHelper[2]][$fieldHelper[3]];
          }
          break;
        case 3:
          if (isset($formValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]][$fieldHelper[2]])) {
            $currentFieldValue = $formValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]][$fieldHelper[2]];
          }
          break;
        case 2:
          if (isset($formValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]])) {
            $currentFieldValue = $formValues[$fieldStart][$fieldHelper[0]][$fieldHelper[1]];
          }
          break;
        case 1:
          if (isset($formValues[$fieldStart][$fieldHelper[0]])) {
            $currentFieldValue = $formValues[$fieldStart][$fieldHelper[0]];
          }
          break;
      }
    } else {
      $currentFieldValue = $formValues[$field] ?? null;
    }

    return $currentFieldValue;
  }

  /**
   * @return string
   */
  public function getHtml(): string
  {
    return $this->formDocument->html();
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
   * Find the first form on page or via css-selector, and parse <input>-elements.
   *
   * @return bool
   */
  public function parseHtmlDomForRules(): bool
  {
    // init
    $this->rules = [];
    $inputForm = [];

    if ($this->selector) {
      $forms = $this->formDocument->find($this->selector);
    } else {
      $forms = $this->formDocument->find('form');
    }

    if (\count($forms) === 0) {
      return false;
    }

    // get the first form
    $form = $forms[0];

    // get the from-id
    if ($form->id) {
      $formHelperId = $form->id;
    } else {
      $formHelperId = \uniqid('html-form-validator-tmp', true);
    }

    // get the <input>-elements from the form
    $inputFromFields = $form->getElementsByTagName('input');
    foreach ($inputFromFields as $inputFormField) {
      $this->parseInputForRules($inputFormField, $formHelperId);
      $this->parseInputForFilter($inputFormField, $formHelperId);
    }

    // get the <input>-elements with a matching form="id"
    if (\strpos($formHelperId, 'html-form-validator-tmp') !== 0) {
      $inputFromFieldsTmpAll = $this->formDocument->find('input');
      foreach ($inputFromFieldsTmpAll as $inputFromFieldTmp) {
        if ($inputFromFieldTmp->form == $formHelperId) {
          $this->parseInputForRules($inputFromFieldTmp, $formHelperId);
          $this->parseInputForFilter($inputFromFieldTmp, $formHelperId);
        }
      }
    }

    return (\count($inputForm) >= 0);
  }

  /**
   * Determine if element has filter attributes, and save the given filter.
   *
   * @param SimpleHtmlDom $inputField
   * @param string        $formHelperId
   */
  private function parseInputForFilter(SimpleHtmlDom $inputField, string $formHelperId)
  {
    if (!$inputField->hasAttribute('data-filter')) {
      return;
    }

    $inputName = $inputField->getAttribute('name');
    $inputFilter = $inputField->getAttribute('data-filter');

    if ($inputFilter === 'auto') {
      $inputFilter = 'htmlentities';
    }

    $this->filters[$formHelperId][$inputName] = $inputFilter;
  }

  /**
   * Determine if element has validator attributes, and save the given rule.
   *
   * @param SimpleHtmlDom $inputField
   * @param string        $formHelperId
   */
  private function parseInputForRules(SimpleHtmlDom $inputField, string $formHelperId)
  {
    if (!$inputField->hasAttribute('data-validator')) {
      return;
    }

    $inputName = $inputField->getAttribute('name');
    $inputRule = $inputField->getAttribute('data-validator');

    if ($inputRule === 'auto') {
      $inputType = $inputField->getAttribute('type');
      $inputRule = $this->autoSelectRuleByInputType($inputType);
    }

    if ($inputField->hasAttribute('required')) {
      $this->required_rules[$formHelperId][$inputName] = $inputRule;
    }

    $this->rules[$formHelperId][$inputName] = $inputRule;
  }

  /**
   * Loop the form data through form rules.
   *
   * @param array $formValues
   * @param bool  $checkForRules
   *
   * @throws UnknownValidationRule
   *
   * @return ValidatorResult
   */
  public function validate(array $formValues, $checkForRules = true): ValidatorResult
  {
    if (
        $checkForRules === true
        &&
        \count($this->rules) === 0
    ) {
      throw new NoValidationRule(
          'No rules defined in the html.'
      );
    }

    // init
    $validatorResult = new ValidatorResult($this->formDocument);

    foreach ($this->rules as $formHelperId => $formFields) {
      foreach ($formFields as $field => $fieldRuleOuter) {

        $currentFieldValue = $this->getCurrentFieldValue($formValues, $field);

        //
        // use the filter
        //

        if (isset($this->filters[$formHelperId][$field])) {
          $filtersOuter = $this->filters[$formHelperId][$field];
          if (\strpos($filtersOuter, '|') !== false) {
            $fieldFilters = \explode('|', $filtersOuter);
          } else {
            $fieldFilters = (array)$filtersOuter;
          }

          foreach ($fieldFilters as $fieldFilter) {
            $currentFieldValue = $this->applyFilter($currentFieldValue, $fieldFilter);
          }
        }

        //
        // save the new values into the result-object
        //

        $validatorResult->setValues($field, $currentFieldValue);

        //
        // skip validation, if there was no value and validation is not required
        //

        if (
            $currentFieldValue === null
            &&
            !isset($this->required_rules[$formHelperId][$field])
        ) {
          continue;
        }

        //
        // use the validation rules from the dom
        //

        if (\strpos($fieldRuleOuter, '|') !== false) {
          $fieldRules = \explode('|', $fieldRuleOuter);
        } else {
          $fieldRules = (array)$fieldRuleOuter;
        }

        foreach ($fieldRules as $fieldRule) {

          $validationClass = $this->validatorRulesManager->getClassViaAlias($fieldRule);

          if (!$validationClass instanceof AbstractRule) {
            try {
              $respectValidatorFactory = new Factory();
              $respectValidatorFactory->rule($validationClass);
            } catch (ComponentException $componentException) {
              throw new UnknownValidationRule(
                  'No rule defined for: ' . $field . ' (rule: ' . $fieldRule . ')',
                  500,
                  $componentException
              );
            }
          }

          try {

            if (!$validationClass instanceof AbstractRule) {
              $respectValidator = \call_user_func([RespectValidator::class, $validationClass]);
            } else {
              $respectValidator = $validationClass;
            }

            /* @var $respectValidator RespectValidator */
            $hasPassed = $respectValidator->assert($currentFieldValue);

            if ($hasPassed === true) {
              continue;
            }

          } catch (NestedValidationException $nestedValidationException) {
            $validatorResult->setError($field, $nestedValidationException->getMessages());
          } catch (ValidationException $validationException) {
            $validatorResult->setError($field, $validationException->getMainMessage());
          }

        }
      }
    }

    return $validatorResult;
  }

}
