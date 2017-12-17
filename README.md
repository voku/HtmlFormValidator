[![Build Status](https://travis-ci.org/voku/HtmlFormValidator.svg?branch=master)](https://travis-ci.org/voku/HtmlFormValidator)
[![Coverage Status](https://coveralls.io/repos/github/voku/HtmlFormValidator/badge.svg?branch=master)](https://coveralls.io/github/voku/HtmlFormValidator?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/voku/HtmlFormValidator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/voku/HtmlFormValidator/?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/)](https://www.codacy.com/app/voku/HtmlFormValidator?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=voku/HtmlFormValidator&amp;utm_campaign=Badge_Grade)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/)](https://insight.sensiolabs.com/projects/)
[![Latest Stable Version](https://poser.pugx.org/voku/HtmlFormValidator/v/stable)](https://packagist.org/packages/voku/HtmlFormValidator) 
[![Total Downloads](https://poser.pugx.org/voku/HtmlFormValidator/downloads)](https://packagist.org/packages/voku/HtmlFormValidator) 
[![Latest Unstable Version](https://poser.pugx.org/voku/HtmlFormValidator/v/unstable)](https://packagist.org/packages/voku/HtmlFormValidator)
[![License](https://poser.pugx.org/voku/HtmlFormValidator/license)](https://packagist.org/packages/voku/HtmlFormValidator)

# HTMLFormValidation 

## Description

HtmlFormValidator is a very easy to use PHP library that will help you 
to validate your <form> data. :)


## Install via "composer require"

```shell
composer require voku/HtmlFormValidator
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
$formValidatorResult = $formValidator->validate($formData);

$formValidatorResult->isSuccess(); // bool
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
