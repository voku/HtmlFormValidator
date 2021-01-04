<?php

namespace voku\HtmlFormValidator\Exceptions;

class EmailException extends \Respect\Validation\Exceptions\ValidationException
{
    public $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be valid email',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be an email',
        ],
    ];
}
