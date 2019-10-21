<?php

use Respect\Validation\Validator as v;
use voku\HtmlFormValidator\Rules\MaxLength;
use voku\HtmlFormValidator\Rules\NonStrict;
use voku\HtmlFormValidator\Validator;

/**
 * @internal
 */
final class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    protected function getBasicCheckboxFormStrict()
    {
        return '
        <form id="food"> 
          <h3>Kreuzen Sie die gewünschten Zutaten an:</h3> 
          <fieldset>
            <ul>
              <li> 
                <label>
                  <input  type="checkbox" 
                          name="zutat" 
                          data-validator="strict" 
                          value="salami"
                  >
                  Salami
                </label>
              </li>
              <li> 
                <label>
                   <input   type="checkbox" 
                            name="zutat" 
                            data-validator="strict" 
                            value="schinken"
                   >
                   Schinken
                </label>
              </li>
              <li>  
                <label>
                  <input  type="checkbox" 
                          name="zutat" 
                          data-validator="strict" 
                          value="sardellen"
                  >
                  Sardellen
                </label>
              </li>
            </ul> 
          </fieldset> 
        </form>
        ';
    }

    protected function getBasicRadioFormStrict()
    {
        return '
        <form id="billing">
          <p>Geben Sie Ihre Zahlungsweise an:</p>
          <fieldset>
            <input  type="radio" 
                    id="mc" 
                    data-validator="strict" 
                    name="Zahlmethode" 
                    value="Mastercard"
            >
            <label for="mc"> Mastercard</label> 
            
            <input  type="radio" 
                    id="vi" 
                    data-validator="strict" 
                    name="Zahlmethode" 
                    value="Visa"
            >
            <label for="vi"> Visa</label>
            
            <input  type="radio" 
                    id="ae" 
                    data-validator="strict" 
                    name="Zahlmethode" 
                    value="AmericanExpress"
            >
            <label for="ae"> American Express</label> 
            
          </fieldset>
        </form>
        ';
    }

    protected function getBasicSelectFormStrict()
    {
        return '
        <form action="%s" id="music">
          <label>Künstler(in):
            <select name="top5"
                    required
                    data-validator="strict|maxLength(10)|minLength(1)"   
            >
              <option>Heino</option>
              <option>Michael Jackson</option>
              <option>Tom Waits</option>
              <option>Nina Hagen</option>
              <option>Marianne Rosenberg</option>
            </select>
          </label>
        </form>
        ';
    }

    protected function getBasicSelectFormNonStrict()
    {
        return '
        <form action="%s" id="music">
          <label>Künstler(in):
            <select name="top5"
                    required
                    data-validator="' . NonStrict::class . '|' . MaxLength::class . '(10)|minLength(1)"   
            >
              <option>Heino</option>
              <option>Michael Jackson</option>
              <option>Tom Waits</option>
              <option>Nina Hagen</option>
              <option>Marianne Rosenberg</option>
            </select>
          </label>
        </form>
        ';
    }

    protected function getBasicValidForm()
    {
        return '
        <form action="%s" method="post">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                value=""
                data-validator="email|maxLength(20)"
                required="required"
            />
            <input type="submit"/>
        </form>
        ';
    }

    protected function getBasicValidFormCustom()
    {
        return '
        <form action="%s" id="foo" method="post">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                value=""
                data-validator="email"
                required="required"
            />
            <input
                type="text"
                id="lall"
                name="lall"
                value=""
                data-validator="foobar"
            />
            <button type="submit">submit</button>
        </form>
        ';
    }

    protected function getBasicValidFormCustomFilter()
    {
        return '
        <form action="%s" id="foo" method="post">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                value=""
                data-validator="email"
                required="required"
            />
            <input
                type="text"
                id="lall"
                name="lall"
                value=""
                data-validator="notEmpty"
                data-filter="append_lall"
            />
            <button type="submit">submit</button>
        </form>
        ';
    }

    protected function getBasicValidFormWithArrayData()
    {
        return '
        <form action="%s" id="user-register" method="post">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="user[1][email]"
                value=""
                data-validator="auto|maxLength(200)"
                required="required"
            />
            
            <div>
                <label for="username">Name:</label>
                <input
                    type="text"
                    form="register"
                    id="username"
                    name="user[2][name]"
                    value=""
                    data-validator="notEmpty"
                    data-filter="strip_tags|trim|escape"
                    required="required"
                />
            </div>
            
            <input type="submit"/>
        </form>
        ';
    }

    protected function getBasicValidFormWithSimpleArrayData()
    {
        return '
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
        ';
    }

    protected function getBasicValidFromWithPattern()
    {
        return '
        <form id="food">
          <label for="choose">Would you prefer a banana or a cherry?</label>
          <input  id="choose" 
                  name="i_like" 
                  required 
                  data-validator="auto"
                  pattern="banana|cherry">
          <button>Submit</button>
        </form>
        ';
    }

    protected function getBasicValidFromWithoutId()
    {
        return '
        <form>
          <label for="choose">Would you prefer a banana or a cherry?</label>
          <input  id="choose" 
                  name="i_like" 
                  required 
                  data-validator="auto"
                  pattern="banana|cherry">
          <button>Submit</button>
        </form>
        ';
    }

    protected function getFilterValidForm()
    {
        return '
        <form action="%s" id="lall-form" method="post">
            <label for="lall">Lall:</label>
            <input
                type="text"
                id="lall"
                name="lall"
                value=""
                data-filter
            />
            <input type="submit"/>
        </form>
        ';
    }

    protected function getFormWithAdditionalInputValidForm()
    {
        return '
        <form action="%s" id="register" method="post">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                value=""
                data-validator="email|notEmpty"
                data-filter="trim"
                required="required"
            />
            <input type="submit"/>
        </form>
        
        <div>
            <label for="username">Name:</label>
            <input
                type="text"
                form="register"
                id="username"
                name="username"
                value=""
                data-validator="notEmpty"
                data-filter="strip_tags(<p>)|trim|escape"
                required="required"
            />
        </div>
        ';
    }

    protected function getFormWithUnknownRule()
    {
        return '
        <form action="%s" method="post">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                value=""
                data-validator="unknown"
                required="required"
            />
            <input type="submit"/>
        </form>
        ';
    }

    protected function getInputsWithoutFormTag()
    {
        return '
        <div>
          <foo class="input_data input_data--1 input_data--2">
            <textarea name="your_text_input"
                      required
                      data-validator="minLength(1)|maxLength(20)"  
            ></textarea>
            
            <input  name="your_age"
                    min="18"
                    data-validator="auto"
            >
          </foo>
        </div>';
    }

    protected function getTwoFormsInputValidAndInvalidForm()
    {
        return '
        <form action="%s" id="foo" method="post">
            <label for="email">Test:</label>
            <input
                type="text"
                name="foo"
                value=""
                data-validator="bar"
                required="required"
            />
            <input type="submit"/>
        </form>
        
        <form action="%s" id="register" method="post">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                value=""
                data-validator="auto"
                required="required"
            />
            <input type="submit"/>
        </form>
        ';
    }

    public function testItCanUseArrayDataForValidations()
    {
        $formHTML = $this->getBasicValidFormWithArrayData();

        $formValidator = new Validator($formHTML);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'user-register' => [
                    'user[1][email]' => 'auto|maxLength(200)|Respect\Validation\Rules\Email',
                    'user[2][name]'  => 'notEmpty',
                ],
            ],
            $rules
        );
        static::assertCount(2, $rules['user-register']);

        // --- valid

        $formData = [
            'user' => [
                '1' => ['email' => 'foo@isanemail.com'],
                '2' => ['name' => 'bar'],
            ],
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'user' => [
                '1' => ['email' => 'foo@isanemail'],
                '2' => ['name' => 'bar'],
            ],
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'user[1][email]' => [
                    0 => '"foo@isanemail" must be valid email',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseAutoFiler()
    {
        $formHTML = $this->getFilterValidForm();

        $formValidator = new Validator($formHTML);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [],
            $rules
        );

        // --- filter

        $filter = $formValidator->getAllFilters();
        static::assertSame(
            [
                'lall-form' => [
                    'lall' => 'htmlentities',
                ],
            ],
            $filter
        );
        static::assertCount(1, $filter['lall-form']);

        // --- valid

        $formData = [
            'user' => [
                '1' => ['name' => 'bar'],
            ],
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());
    }

    public function testItCanUseCustomErrorMessages()
    {
        $formHTML = $this->getBasicValidFormWithSimpleArrayData();

        $formValidator = new Validator($formHTML);
        $formValidator->setTranslator(
            function ($text) {
                return 'Error: ' . $text;
            }
        );

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'register' => [
                    'user[email]' => 'email',
                    'user[name]'  => 'notEmpty|stringType',
                ],
            ],
            $rules
        );
        static::assertCount(2, $rules['register']);

        // --- valid

        $formData = [
            'user' => [
                'email' => 'foo@isanemail.com',
                'name'  => 'bar',
            ],
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'user' => [
                'email' => 'foo@isanemail',
                'name'  => 'bar',
            ],
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'user[email]' => [
                    0 => 'Error: "foo@isanemail" must be valid email',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseCustomFilter()
    {
        $formHTML = $this->getBasicValidFormCustomFilter();

        $formValidator = new Validator($formHTML);
        $formValidator->addCustomFilter(
            'append_lall',
            function ($input) {
                return $input . 'lall';
            }
        );

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'foo' => [
                    'email' => 'email',
                    'lall'  => 'notEmpty',
                ],
            ],
            $rules
        );
        static::assertCount(2, $rules['foo']);

        // --- valid

        $formData = [
            'email' => 'foo@isanemail.com',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- valid

        $formData = [
            'email' => 'foo@isanemail.com',
            'lall'  => '',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'email' => 'foo@isanemail.com',
                'lall'  => 'lall',
            ],
            $formValidatorResult->getValues()
        );
        static::assertSame(
            [],
            $formValidatorResult->getErrorMessages()
        );

        // --- valid

        $formData = [
            'email' => 'foo@isanemail.com',
            'lall'  => 'foobar',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());
    }

    public function testItCanUseCustomValidations()
    {
        $formHTML = $this->getBasicValidFormCustom();

        $formValidator = new Validator($formHTML);
        $formValidator->addCustomRule('foobar', \Respect\Validation\Rules\CustomRule::class);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'foo' => [
                    'email' => 'email',
                    'lall'  => 'foobar',
                ],
            ],
            $rules
        );
        static::assertCount(2, $rules['foo']);

        // --- valid

        $formData = [
            'email' => 'foo@isanemail.com',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'email' => 'foo@isanemail.com',
            'lall'  => 'noop',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'lall' => [
                    0 => 'Invalid input... \'foobar\' is only allowed here... ',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );

        // --- valid

        $formData = [
            'email' => 'foo@isanemail.com',
            'lall'  => 'foobar',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());
    }

    public function testItCanUseCustomValidationsInline()
    {
        $formHTML = $this->getBasicValidFormCustom();

        $formValidator = new Validator($formHTML);
        $formValidator->addCustomRule(
            'foobar',
            v::allOf(
                v::intVal(),
                v::positive()
            )
        );

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'foo' => [
                    'email' => 'email',
                    'lall'  => 'foobar',
                ],
            ],
            $rules
        );
        static::assertCount(2, $rules['foo']);

        // --- valid

        $formData = [
            'email' => 'foo@isanemail.com',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'email' => 'foo@isanemail.com',
            'lall'  => 'noop',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'lall' => [
                    0 => '- All of the required rules must pass for "noop"
  - "noop" must be an integer number
  - "noop" must be positive',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );

        // --- invalid

        $formData = [
            'email' => 'foo@isanemail.com',
            'lall'  => 'foobar',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'lall' => [
                    0 => '- All of the required rules must pass for "foobar"
  - "foobar" must be an integer number
  - "foobar" must be positive',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseHtml5MinAlsoWithoutFormTag()
    {
        $formHTML = $this->getInputsWithoutFormTag();

        $formValidator = new Validator($formHTML, 'foo.input_data');
        $formValidator->addCustomRule('foobar', \Respect\Validation\Rules\CustomRule::class);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'html-element-validator-tmp-foo.input_data' => [
                    'your_text_input' => 'minLength(1)|maxLength(20)',
                    'your_age'        => 'auto|min(s:2:"18";)',
                ],
            ],
            $rules
        );
        static::assertCount(2, $rules['html-element-validator-tmp-foo.input_data']);

        // get all required fields

        static::assertSame(
            [
                'html-element-validator-tmp-foo.input_data' => [
                    'your_text_input' => 'minLength(1)|maxLength(20)',
                ],
            ],
            $formValidator->getRequiredRules()
        );

        // --- invalid -> missing required field

        $formData = [];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertNotSame([], $formValidatorResult->getErrorMessages());
        static::assertSame(
            [
                'your_text_input' => [
                    0 => 'null is to short.',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );

        // --- invalid

        $formData = [
            'your_text_input' => 'foooo',
            'your_age'        => '16',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'your_age' => [
                    0 => '"16" must be greater than or equal to "18"',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );

        // valid

        $formData = [
            'your_text_input' => 'foooo',
            'your_age'        => '18',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseHtml5Pattern()
    {
        $formHTML = $this->getBasicValidFromWithPattern();

        $formValidator = new Validator($formHTML);
        $formValidator->addCustomRule('foobar', \Respect\Validation\Rules\CustomRule::class);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'food' => [
                    'i_like' => 'auto|regex(/banana|cherry/)',
                ],
            ],
            $rules
        );
        static::assertCount(1, $rules['food']);

        // --- valid

        $formData = [
            'i_like' => 'banana',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'email' => 'foo@isanemail.com',
            'lall'  => 'noop',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'i_like' => [
                    0 => 'null must validate against "/banana|cherry/"',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseHtml5PatternWithoutFormId()
    {
        $formHTML = $this->getBasicValidFromWithoutId();

        $formValidator = new Validator($formHTML);
        $formValidator->addCustomRule('foobar', \Respect\Validation\Rules\CustomRule::class);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'html-element-validator-tmp-/html/body/form/' => [
                    'i_like' => 'auto|regex(/banana|cherry/)',
                ],
            ],
            $rules
        );
        static::assertCount(1, $rules['html-element-validator-tmp-/html/body/form/']);

        // --- valid

        $formData = [
            'i_like' => 'banana',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'email' => 'foo@isanemail.com',
            'lall'  => 'noop',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'i_like' => [
                    0 => 'null must validate against "/banana|cherry/"',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseHtmlCheckboxStrict()
    {
        $formHTML = $this->getBasicCheckboxFormStrict();

        $formValidator = new Validator($formHTML);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'food' => [
                    'zutat' => 'strict|in(' . \serialize(['salami', 'schinken', 'sardellen']) . ')',
                ],
            ],
            $rules
        );
        static::assertCount(1, $rules['food']);

        // --- valid

        $formData = [
            'zutat' => 'salami',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'zutat' => 'fooooo',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'zutat' => [
                    0 => '"fooooo" must be in { "salami", "schinken", "sardellen" }',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseHtmlRadioStrict()
    {
        $formHTML = $this->getBasicRadioFormStrict();

        $formValidator = new Validator($formHTML);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'billing' => [
                    'Zahlmethode' => 'strict|in(' . \serialize(['Mastercard', 'Visa', 'AmericanExpress']) . ')',
                ],
            ],
            $rules
        );
        static::assertCount(1, $rules['billing']);

        // --- valid

        $formData = [
            'Zahlmethode' => 'Mastercard',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'Zahlmethode' => 'fooooo',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'Zahlmethode' => [
                    0 => '"fooooo" must be in { "Mastercard", "Visa", "AmericanExpress" }',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseHtmlSelectStrict()
    {
        $formHTML = $this->getBasicSelectFormStrict();

        $formValidator = new Validator($formHTML);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'music' => [
                    'top5' => 'strict|maxLength(10)|minLength(1)|in(' . \serialize(
                            [
                                'Heino',
                                'Michael Jackson',
                                'Tom Waits',
                                'Nina Hagen',
                                'Marianne Rosenberg',
                            ]
                        ) . ')',
                ],
            ],
            $rules
        );
        static::assertCount(1, $rules['music']);

        // --- valid

        $formData = [
            'top5' => 'Heino',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'top5' => 'Michael Jackson',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'top5' => [
                    0 => '"Michael Jackson" is to long.',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );

        // --- invalid

        $formData = [
            'top5' => 'fooooo',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'top5' => [
                    '"fooooo" must be in { "Heino", "Michael Jackson", "Tom Waits", "Nina Hagen", "Marianne Rosenberg" }',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseHtmlSelectNonStrict()
    {
        $formHTML = $this->getBasicSelectFormNonStrict();

        $formValidator = new Validator($formHTML);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'music' => [
                    'top5' => 'voku\HtmlFormValidator\Rules\NonStrict|voku\HtmlFormValidator\Rules\MaxLength(10)|minLength(1)',
                ],
            ],
            $rules
        );
        static::assertCount(1, $rules['music']);

        // --- valid

        $formData = [
            'top5' => 'Heino',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'top5' => 'Michael Jackson',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'top5' => [
                    0 => '"Michael Jackson" is to long.',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );

        // --- still valid, because we use non-strict

        $formData = [
            'top5' => 'fooooo',
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItCanUseSimpleArrayDataForValidations()
    {
        $formHTML = $this->getBasicValidFormWithSimpleArrayData();

        $formValidator = new Validator($formHTML);

        $rules = $formValidator->getAllRules();
        static::assertSame(
            [
                'register' => [
                    'user[email]' => 'email',
                    'user[name]'  => 'notEmpty|stringType',
                ],
            ],
            $rules
        );
        static::assertCount(2, $rules['register']);

        // --- valid

        $formData = [
            'user' => [
                'email' => 'foo@isanemail.com',
                'name'  => 'bar',
            ],
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame([], $formValidatorResult->getErrorMessages());

        // --- invalid

        $formData = [
            'user' => [
                'email' => 'foo@isanemail',
                'name'  => 'bar',
            ],
        ];
        $formValidatorResult = $formValidator->validate($formData);
        static::assertSame(
            [
                'user[email]' => [
                    0 => '"foo@isanemail" must be valid email',
                ],
            ],
            $formValidatorResult->getErrorMessages()
        );
    }

    public function testItLooksForValidationRulesInFormInputDataAttributes()
    {
        $formHTML = $this->getBasicValidForm();

        $formValidator = new Validator($formHTML);

        static::assertCount(1, $formValidator->getAllRules());
    }

    public function testItReturnsFalseIfFormRulesDoNotPass()
    {
        $formHTML = $this->getBasicValidForm();
        $formData = [
            'email' => 'foo@notanemail',
        ];
        $expectedValidatorResult = [
            'email' => [
                0 => '"foo@notanemail" must be valid email',
            ],
        ];

        $formValidator = new Validator($formHTML);
        $formValidatorResult = $formValidator->validate($formData);

        static::assertFalse($formValidatorResult->isSuccess());
        static::assertContains(
            '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required">',
            $formValidator->getHtml()
        );
        static::assertContains(
            '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required" aria-invalid="true">',
            $formValidatorResult->getHtml()
        );
        static::assertSame($expectedValidatorResult, $formValidatorResult->getErrorMessages());
    }

    public function testItReturnsFalseIfFormRulesDoNotPassAgain()
    {
        $formHTML = $this->getBasicValidForm();
        $formData = [
            'email' => 'foo@mailmailmailmailmailmail.com',
        ];
        $expectedValidatorResult = [
            'email' => [
                0 => '"foo@mailmailmailmailmailmail.com" is to long.',
            ],
        ];

        $formValidator = new Validator($formHTML);
        $formValidatorResult = $formValidator->validate($formData);

        static::assertFalse($formValidatorResult->isSuccess());
        static::assertContains(
            '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required">',
            $formValidator->getHtml()
        );
        static::assertContains(
            '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required" aria-invalid="true">',
            $formValidatorResult->getHtml()
        );
        static::assertSame($expectedValidatorResult, $formValidatorResult->getErrorMessages());
    }

    public function testItReturnsParsedHtml()
    {
        $formHTML = $this->getBasicValidForm();

        $formValidator = new Validator($formHTML);

        static::assertContains(
            '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required">',
            $formValidator->getHtml()
        );

        static::assertContains(
            '<input type="submit">',
            $formValidator->getHtml()
        );
    }

    public function testItReturnsTrueIfFormRulesPass()
    {
        $formHTML = $this->getBasicValidForm();
        $formData = [
            'email' => 'foo@isanemail.com',
        ];

        $formValidatorResult = (new Validator($formHTML))->validate($formData);

        static::assertTrue(true, $formValidatorResult->isSuccess());
    }

    public function testItReturnsTrueIfTheGivenInputMatchesBothFormsRules()
    {
        $formHTML = $this->getFormWithAdditionalInputValidForm();
        $formData = [
            'email'    => 'foo@example.com',
            'username' => ' <p onclick="alert(\'hacked\')">lall</p> ',
        ];

        $formValidator = new Validator($formHTML);
        $formValidatorResult = $formValidator->validate($formData);

        static::assertSame(
            [
                'email'    => 'foo@example.com',
                'username' => '&lt;p onclick&equals;&quot;alert&lpar;&apos;hacked&apos;&rpar;&quot;&gt;lall&lt;&sol;p&gt;',
            ],
            $formValidatorResult->getValues()
        );
        static::assertSame(
            [
                'register' => [
                    'email'    => 'email|notEmpty',
                    'username' => 'notEmpty',
                ],
            ],
            $formValidator->getAllRules()
        );
        static::assertTrue($formValidatorResult->isSuccess());
    }

    public function testItReturnsTrueIfTheGivenInputMatchesFormRules()
    {
        $formHTML = $this->getBasicValidForm();
        $formData = [
            'email' => 'foo@example.com',
        ];

        $formValidatorResult = (new Validator($formHTML))->validate($formData);

        static::assertTrue($formValidatorResult->isSuccess());
    }

    public function testItSelectHtmlFromViaSelector()
    {
        $formHTML = $this->getTwoFormsInputValidAndInvalidForm();
        $formData = [
            'email' => 'foo@example.com',
        ];

        $formValidator = new Validator($formHTML, '#register');
        $formValidatorResult = $formValidator->validate($formData);

        static::assertSame(
            [
                'register' => [
                    'email' => 'auto|Respect\Validation\Rules\Email',
                ],
            ],
            $formValidator->getAllRules()
        );
        static::assertTrue($formValidatorResult->isSuccess());
    }

    public function testItSelectHtmlFromViaSelectorWithoutFormData()
    {
        $formHTML = $this->getTwoFormsInputValidAndInvalidForm();
        $formData = [];

        $formValidator = new Validator($formHTML, '#register');
        $formValidatorResult = $formValidator->validate($formData);

        static::assertSame(
            [
                'register' => [
                    'email' => 'auto|Respect\Validation\Rules\Email',
                ],
            ],
            $formValidator->getAllRules()
        );
        static::assertFalse($formValidatorResult->isSuccess());
    }

    public function testItThowsAnExceptionIfAGivenRuleIsUnknown()
    {
        $this->expectException(\voku\HtmlFormValidator\Exceptions\UnknownValidationRule::class);

        $formHTML = $this->getFormWithUnknownRule();
        $formData = [
            'unknown' => '???',
        ];

        (new Validator($formHTML))->validate($formData);
    }

    public function testItThowsAnExceptionIfNoRules()
    {
        $this->expectException(\voku\HtmlFormValidator\Exceptions\NoValidationRule::class);

        $formHTML = '';
        $formData = [
            'foo' => 'bar',
        ];

        (new Validator($formHTML))->validate($formData, true);
    }

    public function testShortTestForATweet()
    {
        $h = '<input 
          type="email"
          name="user[email]"
          data-validator="auto"
          data-filter="trim"
          required
    >';

        // invalid
        $v = (new Validator($h, 'input'))->validate(['user' => ['email' => 'foo@nomail']]);
        static::assertFalse($v->isSuccess());

        // valid
        $v = (new Validator($h, 'input'))->validate(['user' => ['email' => 'foo@ismail.com']]);
        static::assertTrue($v->isSuccess());

        // valid but only with trim (data-filter)
        $v = (new Validator($h, 'input'))->validate(['user' => ['email' => '   foo@ismail.com']]);
        static::assertTrue($v->isSuccess());
    }

    public function testWithPost()
    {
        $html = '
        <form id="register" method="post">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="user[email]"
                value=""
                data-validator="auto"
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
            '<form id="register" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="user[email]" value="" data-validator="auto" data-filter="trim" data-error-class="error-foo-bar" data-error-message--email="Your email [%s] address is not correct." data-error-template-selector="span#email-error-message-template" required="required" aria-invalid="true">
            <span style="color: red;" id="email-error-message-template">Your email [foo@isanemail] address is not correct.</span>
            
            <label for="username">Name:</label>
            <input type="text" id="username" name="user[name]" value="bar" data-validator="notEmpty|maxLength(100)" data-filter="strip_tags(<p>)|trim|escape" data-error-class="error-foo-bar" data-error-template-selector="span#username-error-message-template" required="required" aria-invalid="false">
            <span style="color: red;" id="username-error-message-template"></span>
            
            <label for="date">Date:</label>
            <input type="text" id="date" name="user[date]" value="" data-validator="dateGerman|notEmpty" data-filter="trim" data-error-class="error-foo-bar" data-error-message--dategerman="Date is not correct." data-error-message--notempty="Date is empty." data-error-template-selector="span#date-error-message-template" required="required" aria-invalid="true">
            <span style="color: red;" id="date-error-message-template">Date is not correct. Date is empty.</span>
            
            <button type="submit">submit</button>
        </form>',
            $formValidatorResult->getHtml()
        );
    }
}
