<?php

namespace voku\HtmlFormValidator\Rules;

use Respect\Validation\Rules\Date;

class DateGerman extends Date
{
    public function __construct($format = 'd-m-Y')
    {
        parent::__construct($format);
    }
}
