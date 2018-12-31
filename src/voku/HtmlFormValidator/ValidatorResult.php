<?php

declare(strict_types=1);

namespace voku\HtmlFormValidator;

use voku\helper\HtmlDomParser;

class ValidatorResult
{
    /**
     * List of errors from validation
     *
     * @var string[][]
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
        return $this->countErrors() === 0;
    }

    /**
     * @param string          $field
     * @param string          $fieldRule
     * @param string|string[] $errorMsg
     * @param mixed           $currentFieldValue
     *
     * @return self
     */
    public function setError(string $field, string $fieldRule, $errorMsg, $currentFieldValue): self
    {
        $inputTag = $this->formDocument->find('[name=\'' . $field . '\']', 0);
        if ($inputTag) {
            /** @noinspection UnusedFunctionResultInspection */
            $inputTag->setAttribute('aria-invalid', 'true');
        }

        // overwrite the error message if needed
        $fieldRule = (new ValidatorRulesManager)->getClassViaAlias($fieldRule)['class'];
        $errorMsgFromHtml = $inputTag->getAttribute('data-error-message--' . \strtolower($fieldRule));
        if ($errorMsgFromHtml) {
            $errorMsg = \sprintf($errorMsgFromHtml, \htmlspecialchars((string) $currentFieldValue, \ENT_COMPAT));
        }

        // save the error message per field into this object
        if (\is_array($errorMsg) === false) {
            $errorMsg = [$errorMsg];
        }
        foreach ($errorMsg as &$errorMsgSingle) {
            if (
                isset($this->errors[$field])
                &&
                \in_array($errorMsgSingle, $this->errors[$field], true)
            ) {
                continue;
            }

            $this->errors[$field][] = $errorMsgSingle;
        }
        unset($errorMsgSingle);

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return self
     */
    public function setValue(string $field, $value): self
    {
        $inputTag = $this->formDocument->find('[name=\'' . $field . '\']', 0);
        if ($inputTag) {
            /** @noinspection UnusedFunctionResultInspection */
            $inputTag->setAttribute('aria-invalid', 'false');

            /** @noinspection UnusedFunctionResultInspection */
            $inputTag->val($value);
        }

        return $this;
    }

    /**
     * Write the error messages into the dom, if needed.
     *
     * @return self
     */
    public function writeErrorsIntoTheDom(): self
    {
        foreach ($this->errors as $field => $errors) {
            $inputTag = $this->formDocument->find('[name=\'' . $field . '\']', 0);
            if ($inputTag) {
                $errorMsgTemplateSelector = $inputTag->getAttribute('data-error-template-selector');
                if ($errorMsgTemplateSelector) {
                    $errorMsgTemplate = $this->formDocument->find($errorMsgTemplateSelector, 0);
                    if ($errorMsgTemplate) {
                        foreach ($errors as $error) {
                            $errorMsgTemplate->innerText .= ' ' . $error;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return self
     */
    public function saveValue(string $field, $value): self
    {
        $this->values[$field] = $value;

        return $this;
    }
}
