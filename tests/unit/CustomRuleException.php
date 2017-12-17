<?php

namespace Respect\Validation\Exceptions;

class CustomRuleException extends ValidationException
{
  public static $defaultTemplates = [
      self::MODE_DEFAULT  => [
          self::STANDARD => 'Invalid input... \'foobar\' is only allowed here... ', // eg: must be string
      ],
      self::MODE_NEGATIVE => [
          self::STANDARD => 'Invalid input... \'foobar\' is not allowed here... ', // eg: must not be string
      ],
  ];
}