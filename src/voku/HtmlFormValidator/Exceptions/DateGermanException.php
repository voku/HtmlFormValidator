<?php

namespace voku\HtmlFormValidator\Exceptions;

final class DateGermanException extends \Respect\Validation\Exceptions\ValidationException
{
    /**
     * {@inheritdoc}
     */
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be a valid date in the format {{sample}}',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be a valid date in the format {{sample}}',
        ],
    ];
}
