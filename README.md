[![Build Status](https://travis-ci.org/voku/HtmlFormValidator.svg?branch=master)](https://travis-ci.org/voku/HtmlFormValidator)
[![Coverage Status](https://coveralls.io/repos/github/voku/HtmlFormValidator/badge.svg?branch=master)](https://coveralls.io/github/voku/HtmlFormValidator?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/7527a5ffd2b945d38c0b580bbe3dfd93)](https://www.codacy.com/app/voku/HtmlFormValidator?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=voku/HtmlFormValidator&amp;utm_campaign=Badge_Grade)
[![Latest Stable Version](https://poser.pugx.org/voku/html-form-validator/v/stable)](https://packagist.org/packages/voku/html-form-validator) 
[![Total Downloads](https://poser.pugx.org/voku/html-form-validator/downloads)](https://packagist.org/packages/voku/html-form-validator) 
[![License](https://poser.pugx.org/voku/html-form-validator/license)](https://packagist.org/packages/voku/html-form-validator)

# :flashlight: HTMLFormValidation 

## Description

HtmlFormValidator is a very easy to use PHP library that will help you 
to validate your ```<form>``` data and you can use this independent from your framework of choice.


## Install via "composer require"

```shell
composer require voku/html-form-validator
```


## How does this work?

1. First you need to generate a html form, that's completely your part. You can write it by hand or you can generate with a framework or a library, it doesn't matter.

2. Then we use DOM Parsing via [voku/simple_html_dom](https://github.com/voku/simple_html_dom), to detect the current validator and filter rules directly from the html.

3. And finaly we use [Respect/Validation](https://github.com/Respect/Validation) to validate the form.


## Quick Start

```php
use voku\HtmlFormValidator\Validator;

require_once 'composer/autoload.php';

$html = '
<form id="register" method="post">
    <label for="email">Email:</label>
    <input
        type="email"
        id="email"
        name="user[email]"
        value=""
        data-validator="email"
        data-filter="trim"
        required="required"
    >

    <label for="username">Name:</label>
    <input
        type="text"
        id="username"
        name="user[name]"
        value=""
        data-validator="notEmpty|maxLength(100)"
        data-filter="strip_tags(<p>)|trim|escape"
        required="required"
    >

    <input type="submit">
</form>
';

$formValidator = new Validator($html);

// fake some data
$_POST = [
    'user' => [
        'email' => 'foo@isanemail',
        'name'  => 'bar',
    ],
];

// validate the form
$formValidatorResult = $formValidator->validate($_POST);

// check the result
$formValidatorResult->isSuccess(); // false

// get the error messages
$formValidatorResult->getErrorMessages(); // ['user[email]' => ['"foo@isanemail" must be valid email']]
```

## Validator

You can use all validators from [here](https://github.com/Respect/Validation/blob/1.1/docs/VALIDATORS.md).

e.g.: ```data-validator="date"``` (you need to lowercase the first letter from the class)

You can combine validators simply via "|" ...

e.g.: ```data-validator="notEmpty|maxLength(100)"```

PS: you can add arguments comma separated or you can use serialize -> something like that -> ```in(' . serialize($selectableValues) . ')```

If you wan't to use the HTML5 validation e.g. for min or max values, or for e.g. email then you can use "auto".

e.g.: ```data-validator="auto"```

If you wan't to limit the submitted values to the values from the form e.g. for checkboxes or radios, then you can use "strict".

e.g.: ```data-validator="strict"```

And if you need a more complex validation, then you can add simple-custom validations.

```php
$formValidator->addCustomRule(
    'foobar',
    v::allOf(
        v::intVal(),
        v::positive()
    )
);
```

e.g.: ```data-validator="foobar"```

And if you need really complex validation, then you can create your own classes.

```php
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
    return ($value === 'foobar');
  }

}
```

```php
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
```

```php
$formValidator->addCustomRule('foobar', \Respect\Validation\Rules\CustomRule::class);
```

e.g.: ```data-validator="foobar"```


## Filter

You can also use some simple filters, that will be applied on the input-data.

- trim
- escape (htmlentities with ENT_QUOTES | ENT_HTML5)
- ... and all methods from [here](https://github.com/voku/portable-utf8/blob/master/README.md)

e.g.: ```data-filter="strip_tags(<p>)"```

PS: the first argument will be the submitted value from the user

And also here you can combine some filters simply via "|" ...

e.g.: ```data-filter="strip_tags|trim|escape"```

... and you can also add custom filters by your own.

```php
$formValidator->addCustomFilter(
    'append_lall',
    function ($input) {
      return $input . 'lall';
    }
);
```

e.g.: ```data-filter="append_lall"```


## Unit Test

1) [Composer](https://getcomposer.org) is a prerequisite for running the tests.

```
composer install voku/HtmlFormValidator
```

2) The tests can be executed by running this command from the root directory:

```bash
./vendor/bin/phpunit
```
