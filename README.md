[![Build Status](https://travis-ci.org/voku/HtmlFormValidator.svg?branch=master)](https://travis-ci.org/voku/HtmlFormValidator)
[![Coverage Status](https://coveralls.io/repos/github/voku/HtmlFormValidator/badge.svg?branch=master)](https://coveralls.io/github/voku/HtmlFormValidator?branch=master)[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/voku/HtmlFormValidator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/voku/HtmlFormValidator/?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/7527a5ffd2b945d38c0b580bbe3dfd93)](https://www.codacy.com/app/voku/HtmlFormValidator?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=voku/HtmlFormValidator&amp;utm_campaign=Badge_Grade)
[![Latest Stable Version](https://poser.pugx.org/voku/html-form-validator/v/stable)](https://packagist.org/packages/voku/html-form-validator) 
[![Total Downloads](https://poser.pugx.org/voku/html-form-validator/downloads)](https://packagist.org/packages/voku/html-form-validator) 
[![Latest Unstable Version](https://poser.pugx.org/voku/html-form-validator/v/unstable)](https://packagist.org/packages/voku/html-form-validator)
[![License](https://poser.pugx.org/voku/html-form-validator/license)](https://packagist.org/packages/voku/html-form-validator)

# HTMLFormValidation 

## Description

HtmlFormValidator is a very easy to use PHP library that will help you 
to validate your <form> data.

We will use [Respect/Validation](https://github.com/Respect/Validation) in the 
background, so you can use this independent from your framework of choice.

## Install via "composer require"

```shell
composer require voku/html-form-validator
```

## Quick Start

```php
use voku\HtmlFormValidator\Validator;

require_once 'composer/autoload.php';

$html = "
<form action="%s" id="register" method="post">
    <label for="email">Email:</label>
    <input
        type="email"
        id="email"
        name="user[email]"
        value=""
        data-validator="email"
        data-filter="trim"
        required="required"
    />
    
    <label for="username">Name:</label>
    <input
        type="text"
        id="username"
        name="user[name]"
        value=""
        data-validator="notEmpty|stringType"
        data-filter="strip_tags|trim|escape"
        required="required"
    />
    
    <input type="submit"/>
</form>
";

$formValidator = new Validator($formHTML);

$formData = [
        'user' => [
            'email' => 'foo@isanemail',
            'name'  => 'bar',
        ],
    ];


// validate the form
$formValidatorResult = $formValidator->validate($formData);

// check the result
$formValidatorResult->isSuccess(); // false

// get the error messages
$formValidatorResult->getErrorMessages(); // ['user[email]' => ['"foo@isanemail" must be valid email']]    
```

## Unit Test

1) [Composer](https://getcomposer.org) is a prerequisite for running the tests.

```
composer install voku/HtmlFormValidator
```

2) The tests can be executed by running this command from the root directory:

```bash
./vendor/bin/phpunit
```
