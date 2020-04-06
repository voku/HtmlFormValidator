[![Build Status](https://travis-ci.org/voku/HtmlFormValidator.svg?branch=master)](https://travis-ci.org/voku/HtmlFormValidator)
[![Coverage Status](https://coveralls.io/repos/github/voku/HtmlFormValidator/badge.svg?branch=master)](https://coveralls.io/github/voku/HtmlFormValidator?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/7527a5ffd2b945d38c0b580bbe3dfd93)](https://www.codacy.com/app/voku/HtmlFormValidator?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=voku/HtmlFormValidator&amp;utm_campaign=Badge_Grade)
[![Latest Stable Version](https://poser.pugx.org/voku/html-form-validator/v/stable)](https://packagist.org/packages/voku/html-form-validator) 
[![Total Downloads](https://poser.pugx.org/voku/html-form-validator/downloads)](https://packagist.org/packages/voku/html-form-validator) 
[![License](https://poser.pugx.org/voku/html-form-validator/license)](https://packagist.org/packages/voku/html-form-validator)
[![Donate to this project using Paypal](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.me/moelleken)
[![Donate to this project using Patreon](https://img.shields.io/badge/patreon-donate-yellow.svg)](https://www.patreon.com/voku)

# :flashlight: HTMLFormValidation 

### Description

HtmlFormValidator is a very easy to use PHP library that will help you 
to validate your ```<form>``` data and you can use this independent from your framework of choice.


### Install via "composer require"

```shell
composer require voku/html-form-validator
```


### How does this work?

1. First you need to generate a html form, that's completely your part. You can write it by hand or you can generate with a framework or a library, it doesn't matter.

2. Then we use DOM Parsing via [voku/simple_html_dom](https://github.com/voku/simple_html_dom), to detect the current validator and filter rules directly from the html.

3. And finaly we use [Respect/Validation](https://github.com/Respect/Validation) to validate the form.


### Simple Example

```php
use voku\HtmlFormValidator\Validator;

require_once 'composer/autoload.php';

$html = '
<form id="music" method="post">
  <label>Artist:
    <select name="top5" required="required">
      <option>Heino</option>
      <option>Michael Jackson</option>
      <option>Tom Waits</option>
      <option>Nina Hagen</option>
      <option>Marianne Rosenberg</option>
    </select>
  </label>
</form>
';

$rules = $formValidator->getAllRules();
static::assertSame(
    [
        'music' => [
            'top5' => 'in(' . \serialize(['Heino','Michael Jackson','Tom Waits','Nina Hagen','Marianne Rosenberg',]) . ')',
        ],
    ],
    $rules
);

// --- valid

// fake some data
$_POST = [
    'top5' => 'Heino',
];
$formValidatorResult = $formValidator->validate($_POST);
static::assertSame([], $formValidatorResult->getErrorMessages());

// --- invalid

// fake some data
$_POST = [
    'top5' => 'fooooo',
];
$formValidatorResult = $formValidator->validate($_POST);
static::assertSame(
    [
        'top5' => [
            '"fooooo" must be in { "Heino", "Michael Jackson", "Tom Waits", "Nina Hagen", "Marianne Rosenberg" }',
        ],
    ],
    $formValidatorResult->getErrorMessages()
);
```

### Extended Example

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
        data-error-class="error-foo-bar"
        data-error-message--email="Your email [%s] address is not correct."
        data-error-template-selector="span#email-error-message-template"
        required="required"
    >
    <span style="color: red;" id="email-error-message-template"></span>
    
    <label for="username">Name:</label>
    <input
        type="text"
        id="username"
        name="user[name]"
        value=""
        data-validator="notEmpty|maxLength(100)"
        data-filter="strip_tags(<p>)|trim|escape"
        data-error-class="error-foo-bar"
        data-error-template-selector="span#username-error-message-template"
        required="required"
    >
    <span style="color: red;" id="username-error-message-template"></span>
    
    <label for="date">Date:</label>
    <input 
        type="text"
        id="date"
        name="user[date]"
        value=""
        data-validator="dateGerman|notEmpty"
        data-filter="trim"
        data-error-class="error-foo-bar"
        data-error-message--dateGerman="Date is not correct."
        data-error-message--notEmpty="Date is empty."
        data-error-template-selector="span#date-error-message-template"
        required="required"
    >
    <span style="color: red;" id="date-error-message-template"></span>
    
    <button type="submit">submit</button>
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
static::assertFalse($formValidatorResult->isSuccess());

// get the error messages
static::assertSame(
    [
        'user[email]' => ['Your email [foo@isanemail] address is not correct.'],
        'user[date]'  => [
            'Date is not correct.',
            'Date is empty.',
        ],
    ],
    $formValidatorResult->getErrorMessages()
);

// get the new html
static::assertSame(
    '
    <form id="register" method="post">
        <label for="email">Email:</label>
        <input 
                type="email" 
                id="email" 
                name="user[email]" 
                value="" 
                data-validator="email" 
                data-filter="trim" 
                data-error-class="error-foo-bar" 
                data-error-message--email="Your email [%s] address is not correct." 
                data-error-template-selector="span#email-error-message-template" 
                required="required" 
                aria-invalid="true"
        >            
        <span style="color: red;" id="email-error-message-template">Your email [foo@isanemail] address is not correct.</span>
                                
        <label for="username">Name:</label>
        <input 
                type="text" 
                id="username" 
                name="user[name]" 
                value="bar" 
                data-validator="notEmpty|maxLength(100)" 
                data-filter="strip_tags(<p>)|trim|escape" 
                data-error-class="error-foo-bar" 
                data-error-template-selector="span#username-error-message-template" 
                required="required" 
                aria-invalid="false"
        >            
        <span style="color: red;" id="username-error-message-template"></span>
                                
        <label for="date">Date:</label>
        <input 
                type="text" 
                id="date" 
                name="user[date]" 
                value="" 
                data-validator="dateGerman|notEmpty" 
                data-filter="trim" 
                data-error-class="error-foo-bar" 
                data-error-message--dategerman="Date is not correct." 
                data-error-message--notempty="Date is empty." 
                data-error-template-selector="span#date-error-message-template"
                required="required" 
                aria-invalid="true"
        >            
        <span style="color: red;" id="date-error-message-template">Date is not correct. Date is empty.</span>
                                
        <button type="submit">submit</button>
    </form>
    ',
    $formValidatorResult->getHtml()
);
```

### Validator

You can use all validators from [here](https://github.com/Respect/Validation/blob/1.1/docs/VALIDATORS.md).

e.g.: ```data-validator="date"``` || ```data-validator="' . \Respect\Validation\Rules\Date::class . '"```  (you need to lowercase the first letter from the class or you can use the class name itself)

You can combine validators simply via "|" ...

e.g.: ```data-validator="notEmpty|maxLength(100)"```

PS: you can add arguments comma separated or you can use serialize -> something like that -> ```in(' . serialize($selectableValues) . ')```

If you want to use the HTML5 validation e.g. for min or max values, or for e.g. email then you can use "auto".

e.g.: ```data-validator="auto"```

By default we limit the submitted values to the values from the form e.g. for checkboxes, radios or select boxes. If you need to disable this,
you can use "non-strict". (not recommended)

e.g.: ```data-validator="non-strict"```

By default we use the error messages from the validation exception class, but you can use your own error messages via:
"data-error-message--RULE_NAME_HERE" in the html.

e.g.: ```data-error-message--email="Email [%s] is not correct"```

By default we don't add error messages into html output, but you can add the error messages with a css selector:

e.g.: ```data-error-template-selector="span#email-error-message-template"```

By default we also don't add error classes, but you can add a new error class via:

e.g. ```data-error-class="error-foo-bar"```

And if you need a more complex validation, then you can add simple-custom validations.

```php
$formValidator->addCustomRule(
    'foobar',
    \Respect\Validation\Validator::allOf(
        \Respect\Validation\Validator::intVal(),
        \Respect\Validation\Validator::positive()
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
          self::STANDARD => 'Invalid input... \'foobar\' is only allowed here... ',
      ],
      self::MODE_NEGATIVE => [
          self::STANDARD => 'Invalid input... \'foobar\' is not allowed here... ',
      ],
  ];
}
```

```php
$formValidator->addCustomRule('foobar', \Respect\Validation\Rules\CustomRule::class);
```

e.g.: ```data-validator="foobar"```


### Filter

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


### Unit Test

1) [Composer](https://getcomposer.org) is a prerequisite for running the tests.

```
composer install voku/HtmlFormValidator
```

2) The tests can be executed by running this command from the root directory:

```bash
./vendor/bin/phpunit
```

### Support

For support and donations please visit [Github](https://github.com/voku/HtmlFormValidator/) | [Issues](https://github.com/voku/HtmlFormValidator/issues) | [PayPal](https://paypal.me/moelleken) | [Patreon](https://www.patreon.com/voku).

For status updates and release announcements please visit [Releases](https://github.com/voku/HtmlFormValidator/releases) | [Twitter](https://twitter.com/suckup_de) | [Patreon](https://www.patreon.com/voku/posts).

For professional support please contact [me](https://about.me/voku).

### Thanks

- Thanks to [GitHub](https://github.com) (Microsoft) for hosting the code and a good infrastructure including Issues-Managment, etc.
- Thanks to [IntelliJ](https://www.jetbrains.com) as they make the best IDEs for PHP and they gave me an open source license for PhpStorm!
- Thanks to [Travis CI](https://travis-ci.com/) for being the most awesome, easiest continous integration tool out there!
- Thanks to [StyleCI](https://styleci.io/) for the simple but powerfull code style check.
- Thanks to [PHPStan](https://github.com/phpstan/phpstan) && [Psalm](https://github.com/vimeo/psalm) for relly great Static analysis tools and for discover bugs in the code!
