<?php

namespace voku\HtmlFormValidator\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Dummy - Class (used by default)
 */
class NonStrict extends AbstractRule
{
    public function validate($input)
    {
        return true;
    }
}
