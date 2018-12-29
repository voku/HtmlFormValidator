<?php

namespace Respect\Validation\Rules;

class CustomRule extends AbstractRule
{
    /**
     * @param string $value
     *
     * @return bool
     */
    public function validate($value)
    {
        return $value === 'foobar';
    }
}
