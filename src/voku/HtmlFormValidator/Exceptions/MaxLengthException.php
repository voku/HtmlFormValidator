<?php

namespace voku\HtmlFormValidator\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class MaxLengthException extends ValidationException
{
  public static $defaultTemplates = [
      self::MODE_DEFAULT  => [
          self::STANDARD => '{{name}} is to long.',
      ],
      self::MODE_NEGATIVE => [
          self::STANDARD => '{{name}} is to short.',
      ],
  ];
}
