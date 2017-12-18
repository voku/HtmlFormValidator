<?php

declare(strict_types=1);

namespace voku\HtmlFormValidator;

use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Factory;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Rules\Email;
use Respect\Validation\Rules\HexRgbColor;
use Respect\Validation\Rules\Numeric;
use Respect\Validation\Rules\Url;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
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
        'email'  => Email::class,
        'url'    => Url::class,
        'color'  => HexRgbColor::class,
        'number' => Numeric::class,

        //
        // TODO@me -> take a look here
        // -> https://github.com/xtreamwayz/html-form-validator/blob/master/src/FormElement/Number.php
        //
    ];

    return $matchingArray[$type] ?? null;
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
   * @return callable|null
   */
  public function getTranslator()
  {
    return $this->translator;
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

    $formTagSelector = 'input, textarea, select';

    // get the <input>-elements from the form
    $inputFromFields = $form->find($formTagSelector);
    foreach ($inputFromFields as $inputFormField) {
      $this->parseInputForRules($inputFormField, $formHelperId, $form);
      $this->parseInputForFilter($inputFormField, $formHelperId);
    }

    // get the <input>-elements with a matching form="id"
    if (\strpos($formHelperId, 'html-form-validator-tmp') !== 0) {
      $inputFromFieldsTmpAll = $this->formDocument->find($formTagSelector);
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

    if (!$inputFilter) {
      $inputFilter = 'htmlentities';
    }

    $this->filters[$formHelperId][$inputName] = $inputFilter;
  }


  /**
   * Determine if element has validator attributes, and save the given rule.
   *
   * @param SimpleHtmlDom      $formField
   * @param string             $formHelperId
   * @param SimpleHtmlDom|null $form
   */
  private function parseInputForRules(SimpleHtmlDom $formField, string $formHelperId, SimpleHtmlDom $form = null)
  {
    if (!$formField->hasAttribute('data-validator')) {
      return;
    }

    $inputName = $formField->getAttribute('name');
    $inputType = $formField->getAttribute('type');
    $inputPattern = $formField->getAttribute('pattern');
    $inputRule = $formField->getAttribute('data-validator');

    $inputMinLength = $formField->getAttribute('minlength');
    $inputMaxLength = $formField->getAttribute('maxlength');

    $inputMin = $formField->getAttribute('min');
    $inputMax = $formField->getAttribute('max');

    if (strpos($inputRule, 'auto') !== false) {

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

      if ($inputPattern) {
        $inputRule .= '|regex(/' . $inputPattern . '/)';
      }

      //
      // min- / max values
      //

      if ($inputMinLength) {
        $inputRule .= '|minLength(' . serialize($inputMinLength) . ')';
      }

      if ($inputMaxLength) {
        $inputRule .= '|maxLength(' . serialize($inputMaxLength) . ')';
      }

      if ($inputMin) {
        $inputRule .= '|min(' . serialize($inputMin) . ')';
      }

      if ($inputMax) {
        $inputRule .= '|max(' . serialize($inputMax) . ')';
      }

    }

    if (strpos($inputRule, 'strict') !== false) {

      if ($formField->tag === 'select') {

        $selectableValues = [];
        foreach ($formField->getElementsByTagName('option') as $option) {
          $selectableValues[] = $option->getNode()->nodeValue;
        }
        $inputRule .= '|in(' . serialize($selectableValues) . ')';

      } else if (
          (
              $inputType == 'checkbox'
              ||
              $inputType == 'radio'
          )
          &&
          $form) {

        $selectableValues = [];

        try {
          $formFieldNames = $form->find('[name=' . $formField->name . ']');
        } catch (SyntaxErrorException $syntaxErrorException) {
          $formFieldNames = null;
          // TODO@me -> can the symfony CssSelectorConverter use array-name-attributes?
        }

        if ($formFieldNames) {
          foreach ($formFieldNames as $formFieldName) {
            $selectableValues[] = $formFieldName->value;
          }
        }

        $inputRule .= '|in(' . serialize($selectableValues) . ')';

      }
    }

    if ($formField->hasAttribute('required')) {
      $this->required_rules[$formHelperId][$inputName] = $inputRule;
    }

    $this->rules[$formHelperId][$inputName] = $inputRule;
  }

  /**
   * @param callable $translator
   *
   * @return Validator
   */
  public function setTranslator(callable $translator): Validator
  {
    $this->translator = $translator;

    return $this;
  }

  /**
   * Loop the form data through form rules.
   *
   * @param array $formValues
   * @param bool  $useNoValidationRuleException
   *
   * @throws UnknownValidationRule
   *
   * @return ValidatorResult
   */
  public function validate(array $formValues, $useNoValidationRuleException = false): ValidatorResult
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
    $validatorResult = new ValidatorResult($this->formDocument);

    foreach ($this->rules as $formHelperId => $formFields) {
      foreach ($formFields as $field => $fieldRuleOuter) {

        $currentFieldValue = $this->getCurrentFieldValue($formValues, $field);

        //
        // use the filter
        //

        if (isset($this->filters[$formHelperId][$field])) {
          $filtersOuter = $this->filters[$formHelperId][$field];
          $fieldFilters = preg_split("/\|+(?![^\(]*\))/", $filtersOuter);

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

        $fieldRules = preg_split("/\|+(?![^\(?:]*\))/", $fieldRuleOuter);

        foreach ($fieldRules as $fieldRule) {

          if (!$fieldRule) {
            continue;
          }

          $validationClassArray = $this->validatorRulesManager->getClassViaAlias($fieldRule);

          if ($validationClassArray['object']) {
            $validationClass = $validationClassArray['object'];
          } else if ($validationClassArray['class']) {
            $validationClass = $validationClassArray['class'];
          } else {
            $validationClass = null;
          }

          $validationClassArgs = $validationClassArray['classArgs'] ?? null;

          if ($validationClass instanceof AbstractRule) {

            $respectValidator = $validationClass;

          } else {

            try {
              $respectValidatorFactory = new Factory();
              $respectValidatorFactory->prependRulePrefix('voku\\HtmlFormValidator\\Rules');

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

          $hasPassed = false;
          $translator = $this->getTranslator();

          try {
            $hasPassed = $respectValidator->assert($currentFieldValue);
          } catch (NestedValidationException $nestedValidationException) {

            if ($translator) {
              $nestedValidationException->setParam('translator', $translator);
            }

            $validatorResult->setError($field, $nestedValidationException->getFullMessage());
          } catch (ValidationException $validationException) {

            if ($translator) {
              $validationException->setParam('translator', $translator);
            }

            $validatorResult->setError($field, $validationException->getMainMessage());
          }

          if ($hasPassed === true) {
            continue;
          }

        }
      }
    }

    return $validatorResult;
  }

}
