<?php

namespace voku\HtmlFormValidator\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Dummy - Class (used by default)
 */
class Strict extends AbstractRule
{
    public function validate($input): bool
    {
        return true;
    }
}
