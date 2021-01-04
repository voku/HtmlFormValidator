<?php

namespace voku\HtmlFormValidator\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Rules\Length;

class MaxLength extends AbstractRule
{
    /**
     * @var int|null
     */
    protected $maxLength;

    /**
     * @param int|null $maxLength
     */
    public function __construct($maxLength = null)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * @param mixed $input
     *
     * @return bool
     */
    public function validate($input): bool
    {
        $internValidate = new Length(null, $this->maxLength);

        return $internValidate->validate($input);
    }
}
