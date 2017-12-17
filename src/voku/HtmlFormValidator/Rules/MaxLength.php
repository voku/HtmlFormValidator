<?php

namespace voku\HtmlFormValidator\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Rules\Length;

class MaxLength extends AbstractRule
{
  protected $maxLength;

  public function __construct($maxLength = null) {
    $this->maxLength = $maxLength;
  }

  public function validate($input)
  {
    $internValidate = new Length(null, $this->maxLength);

    return $internValidate->validate($input);
  }
}
