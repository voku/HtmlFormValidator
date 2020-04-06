<?php

namespace voku\HtmlFormValidator\Rules;

class Email extends \Respect\Validation\Rules\AbstractRule
{
    public function validate($input): bool
    {
        if (!\is_string($input)) {
            return false;
        }

        return \voku\helper\EmailCheck::isValid($input);
    }
}
