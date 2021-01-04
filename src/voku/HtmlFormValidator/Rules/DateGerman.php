<?php

namespace voku\HtmlFormValidator\Rules;

class DateGerman extends \Respect\Validation\Rules\AbstractRule
{
    use \Respect\Validation\Helpers\CanValidateDateTime;

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $sample;

    /**
     * Initializes the rule.
     *
     * @param string $format
     *
     * @throws \Respect\Validation\Exceptions\ComponentException
     */
    public function __construct(string $format = 'd-m-Y')
    {
        if (!\preg_match('/^[djSFmMnYy\W]+$/', $format)) {
            throw new \Respect\Validation\Exceptions\ComponentException(\sprintf('"%s" is not a valid date format', $format));
        }

        $this->format = $format;
        $this->sample = \date($format, \strtotime('2005-12-30'));
    }

    /**
     * {@inheritdoc}
     */
    public function validate($input): bool
    {
        if (!\is_scalar($input)) {
            return false;
        }

        return $this->isDateTime($this->format, (string) $input);
    }
}
