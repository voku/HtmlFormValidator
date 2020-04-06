<?php

namespace voku\HtmlFormValidator\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Rules\Length;

class MinLength extends AbstractRule
{
    protected $minLength;

    public function __construct($minLength = null)
    {
        $this->minLength = $minLength;
    }

    public function validate($input): bool
    {
        $internValidate = new Length($this->minLength, null);

        return $internValidate->validate($input);
    }
}
