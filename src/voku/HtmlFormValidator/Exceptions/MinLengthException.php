<?php

namespace voku\HtmlFormValidator\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class MinLengthException extends ValidationException
{
  public static $defaultTemplates = [
      self::MODE_DEFAULT  => [
          self::STANDARD => '{{name}} is to short.',
      ],
      self::MODE_NEGATIVE => [
          self::STANDARD => '{{name}} is to long.',
      ],
  ];
}
