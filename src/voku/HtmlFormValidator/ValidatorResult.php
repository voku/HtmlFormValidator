<?php

declare(strict_types=1);

namespace voku\HtmlFormValidator;

use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use voku\helper\HtmlDomParser;

class ValidatorResult
{
  /**
   * List of errors from validation
   *
   * @var string[]
   */
  private $errors = [];

  /**
   * @var array
   */
  private $values = [];

  /**
   * @var HtmlDomParser
   */
  private $formDocument;

  /**
   * ValidatorResult constructor.
   *
   * @param HtmlDomParser $formDocument
   */
  public function __construct($formDocument)
  {
    $this->formDocument = clone $formDocument;
  }

  /**
   * @return int
   */
  public function countErrors(): int
  {
    return \count($this->errors);
  }

  /**
   * @return array
   */
  public function getErrorMessages(): array
  {
    return $this->errors;
  }

  /**
   * @return string
   */
  public function getHtml(): string
  {
    return $this->formDocument->html();
  }

  /**
   * @return array
   */
  public function getValues(): array
  {
    return $this->values;
  }

  /**
   * @return bool
   */
  public function isSuccess(): bool
  {
    return ($this->countErrors() === 0);
  }

  /**
   * @param string          $field
   * @param string|string[] $errorMsg
   *
   * @return ValidatorResult
   */
  public function setError(string $field, $errorMsg): self
  {
    try {
      $inputTag = $this->formDocument->find('[name=' . $field . ']', 0);
    } catch (SyntaxErrorException $syntaxErrorException) {
      $inputTag = null;
      // TODO@me -> can the symfony CssSelectorConverter use array-name-attributes?
    }

    if ($inputTag) {
      $inputTag->setAttribute('aria-invalid', 'true');
    }

    if (\is_array($errorMsg) === true) {
      foreach ($errorMsg as $errorMsgSingle) {
        $this->errors[$field][] = $errorMsgSingle;
      }
    } else {
      $this->errors[$field][] = $errorMsg;
    }

    return $this;
  }

  public function setValues(string $field, $value): self
  {
    $this->values[$field] = $value;

    return $this;
  }
}
