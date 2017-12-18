<?php

use Respect\Validation\Validator as v;
use voku\HtmlFormValidator\Validator;

class ValidatorTest extends \PHPUnit\Framework\TestCase
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
              <input type="checkbox" name="zutat" data-validator="strict" value="salami">
              Salami
            </label>
          </li>
          <li> 
            <label>
               <input type="checkbox" name="zutat" data-validator="strict" value="schinken">
               Schinken
            </label>
          </li>
          <li>  
            <label>
              <input type="checkbox" name="zutat" data-validator="strict" value="sardellen">
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
        <input type="radio" id="mc" data-validator="strict" name="Zahlmethode" value="Mastercard">
        <label for="mc"> Mastercard</label> 
        <input type="radio" id="vi" data-validator="strict" name="Zahlmethode" value="Visa">
        <label for="vi"> Visa</label>
        <input type="radio" id="ae" data-validator="strict" name="Zahlmethode" value="AmericanExpress">
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

  /**
   * @test
   */
  public function it_can_use_array_data_for_validations()
  {
    $formHTML = $this->getBasicValidFormWithArrayData();

    $formValidator = new Validator($formHTML);

    $rules = $formValidator->getAllRules();
    self::assertSame(
        [
            'user-register' => [
                'user[1][email]' => 'auto|maxLength(200)|Respect\Validation\Rules\Email',
                'user[2][name]'  => 'notEmpty',
            ],
        ],
        $rules
    );
    self::assertCount(2, $rules['user-register']);

    // --- valid

    $formData = [
        'user' => [
            '1' => ['email' => 'foo@isanemail.com'],
            '2' => ['name' => 'bar'],
        ],
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- invalid

    $formData = [
        'user' => [
            '1' => ['email' => 'foo@isanemail'],
            '2' => ['name' => 'bar'],
        ],
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'user[1][email]' => [
                0 => '"foo@isanemail" must be valid email',
            ],
        ], $formValidatorResult->getErrorMessages()
    );
  }

  /**
   * @test
   */
  public function it_can_use_auto_filer()
  {
    $formHTML = $this->getFilterValidForm();

    $formValidator = new Validator($formHTML);

    $rules = $formValidator->getAllRules();
    self::assertSame(
        [],
        $rules
    );

    // --- filter

    $filter = $formValidator->getAllFilters();
    self::assertSame(
        [
            'lall-form' => [
                'lall' => 'htmlentities',
            ],
        ],
        $filter
    );
    self::assertCount(1, $filter['lall-form']);

    // --- valid

    $formData = [
        'user' => [
            '1' => ['name' => 'bar'],
        ],
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());
  }

  /**
   * @test
   */
  public function it_can_use_custom_error_messages()
  {
    $formHTML = $this->getBasicValidFormWithSimpleArrayData();

    $formValidator = new Validator($formHTML);
    $formValidator->setTranslator(
        function ($text) {
          return 'Error: ' . $text;
        }
    );

    $rules = $formValidator->getAllRules();
    self::assertSame(
        [
            'register' => [
                'user[email]' => 'email',
                'user[name]'  => 'notEmpty|stringType',
            ],
        ],
        $rules
    );
    self::assertCount(2, $rules['register']);

    // --- valid

    $formData = [
        'user' => [
            'email' => 'foo@isanemail.com',
            'name'  => 'bar',
        ],
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- invalid

    $formData = [
        'user' => [
            'email' => 'foo@isanemail',
            'name'  => 'bar',
        ],
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'user[email]' => [
                0 => 'Error: "foo@isanemail" must be valid email',
            ],
        ],
        $formValidatorResult->getErrorMessages()
    );
  }

  /**
   * @test
   */
  public function it_can_use_custom_filter()
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
    self::assertSame(
        [
            'foo' => [
                'email' => 'email',
                'lall'  => 'notEmpty',
            ],
        ],
        $rules
    );
    self::assertCount(2, $rules['foo']);

    // --- valid

    $formData = [
        'email' => 'foo@isanemail.com',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- valid

    $formData = [
        'email' => 'foo@isanemail.com',
        'lall'  => '',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'email' => 'foo@isanemail.com',
            'lall'  => 'lall',
        ],
        $formValidatorResult->getValues()
    );
    self::assertSame(
        [],
        $formValidatorResult->getErrorMessages()
    );

    // --- valid

    $formData = [
        'email' => 'foo@isanemail.com',
        'lall'  => 'foobar',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());
  }

  /**
   * @test
   */
  public function it_can_use_custom_validations()
  {
    $formHTML = $this->getBasicValidFormCustom();

    $formValidator = new Validator($formHTML);
    $formValidator->addCustomRule('foobar', \Respect\Validation\Rules\CustomRule::class);

    $rules = $formValidator->getAllRules();
    self::assertSame(
        [
            'foo' => [
                'email' => 'email',
                'lall'  => 'foobar',
            ],
        ],
        $rules
    );
    self::assertCount(2, $rules['foo']);

    // --- valid

    $formData = [
        'email' => 'foo@isanemail.com',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- invalid

    $formData = [
        'email' => 'foo@isanemail.com',
        'lall'  => 'noop',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
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
    self::assertSame([], $formValidatorResult->getErrorMessages());
  }

  /**
   * @test
   */
  public function it_can_use_custom_validations_inline()
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
    self::assertSame(
        [
            'foo' => [
                'email' => 'email',
                'lall'  => 'foobar',
            ],
        ],
        $rules
    );
    self::assertCount(2, $rules['foo']);

    // --- valid

    $formData = [
        'email' => 'foo@isanemail.com',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- invalid

    $formData = [
        'email' => 'foo@isanemail.com',
        'lall'  => 'noop',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
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
    self::assertSame(
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

  /**
   * @test
   */
  public function it_can_use_html5_pattern()
  {
    $formHTML = $this->getBasicValidFromWithPattern();

    $formValidator = new Validator($formHTML);
    $formValidator->addCustomRule('foobar', \Respect\Validation\Rules\CustomRule::class);

    $rules = $formValidator->getAllRules();
    self::assertSame(
        [
            'food' => [
                'i_like' => 'auto|regex(/banana|cherry/)',
            ],
        ],
        $rules
    );
    self::assertCount(1, $rules['food']);

    // --- valid

    $formData = [
        'i_like' => 'banana',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- invalid

    $formData = [
        'email' => 'foo@isanemail.com',
        'lall'  => 'noop',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'i_like' => [
                0 => 'null must validate against "/banana|cherry/"',
            ],
        ],
        $formValidatorResult->getErrorMessages()
    );
  }

  /**
   * @test
   */
  public function it_can_use_html_checkbox_strict()
  {
    $formHTML = $this->getBasicCheckboxFormStrict();

    $formValidator = new Validator($formHTML);

    $rules = $formValidator->getAllRules();
    self::assertSame(
        [
            'food' => [
                'zutat' => 'strict|in(a:3:{i:0;s:6:"salami";i:1;s:8:"schinken";i:2;s:9:"sardellen";})',
            ],
        ],
        $rules
    );
    self::assertCount(1, $rules['food']);

    // --- valid

    $formData = [
        'zutat' => 'salami',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- invalid

    $formData = [
        'zutat' => 'fooooo',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'zutat' => [
                0 => '"fooooo" must be in { "salami", "schinken", "sardellen" }',
            ],
        ],
        $formValidatorResult->getErrorMessages()
    );
  }

  /**
   * @test
   */
  public function it_can_use_html_radio_strict()
  {
    $formHTML = $this->getBasicRadioFormStrict();

    $formValidator = new Validator($formHTML);

    $rules = $formValidator->getAllRules();
    self::assertSame(
        [
            'billing' => [
                'Zahlmethode' => 'strict|in(a:3:{i:0;s:10:"Mastercard";i:1;s:4:"Visa";i:2;s:15:"AmericanExpress";})',
            ],
        ],
        $rules
    );
    self::assertCount(1, $rules['billing']);

    // --- valid

    $formData = [
        'Zahlmethode' => 'Mastercard',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- invalid

    $formData = [
        'Zahlmethode' => 'fooooo',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'Zahlmethode' => [
                0 => '"fooooo" must be in { "Mastercard", "Visa", "AmericanExpress" }',
            ],
        ],
        $formValidatorResult->getErrorMessages()
    );
  }

  /**
   * @test
   */
  public function it_can_use_html_select_strict()
  {
    $formHTML = $this->getBasicSelectFormStrict();

    $formValidator = new Validator($formHTML);

    $rules = $formValidator->getAllRules();
    self::assertSame(
        [
            'music' => [
                'top5' => 'strict|maxLength(10)|minLength(1)|in(a:5:{i:0;s:5:"Heino";i:1;s:15:"Michael Jackson";i:2;s:9:"Tom Waits";i:3;s:10:"Nina Hagen";i:4;s:18:"Marianne Rosenberg";})',
            ],
        ],
        $rules
    );
    self::assertCount(1, $rules['music']);

    // --- valid

    $formData = [
        'top5' => 'Heino',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- invalid

    $formData = [
        'top5' => 'Michael Jackson',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'top5' => [
                0 => '"Michael Jackson" is to long.',
            ],
        ], $formValidatorResult->getErrorMessages()
    );

    // --- invalid

    $formData = [
        'top5' => 'fooooo',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'top5' => [
                0 => '"fooooo" must be in { "Heino", "Michael Jackson", "Tom Waits", "Nina Hagen", "Marianne Rosenberg" }',
            ],
        ],
        $formValidatorResult->getErrorMessages()
    );
  }

  /**
   * @test
   */
  public function it_can_use_simple_array_data_for_validations()
  {
    $formHTML = $this->getBasicValidFormWithSimpleArrayData();

    $formValidator = new Validator($formHTML);

    $rules = $formValidator->getAllRules();
    self::assertSame(
        [
            'register' => [
                'user[email]' => 'email',
                'user[name]'  => 'notEmpty|stringType',
            ],
        ],
        $rules
    );
    self::assertCount(2, $rules['register']);

    // --- valid

    $formData = [
        'user' => [
            'email' => 'foo@isanemail.com',
            'name'  => 'bar',
        ],
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // --- invalid

    $formData = [
        'user' => [
            'email' => 'foo@isanemail',
            'name'  => 'bar',
        ],
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'user[email]' => [
                0 => '"foo@isanemail" must be valid email',
            ],
        ],
        $formValidatorResult->getErrorMessages()
    );
  }

  /**
   * @test
   */
  public function it_looks_for_validation_rules_in_form_input_data_attributes()
  {
    $formHTML = $this->getBasicValidForm();

    $formValidator = new Validator($formHTML);

    self::assertCount(1, $formValidator->getAllRules());
  }

  /**
   * @test
   */
  public function it_returns_false_if_form_rules_do_not_pass()
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

    self::assertFalse($formValidatorResult->isSuccess());
    self::assertContains(
        '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required">',
        $formValidator->getHtml()
    );
    self::assertContains(
        '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required" aria-invalid="true">',
        $formValidatorResult->getHtml()
    );
    self::assertSame($expectedValidatorResult, $formValidatorResult->getErrorMessages());
  }

  /**
   * @test
   */
  public function it_returns_false_if_form_rules_do_not_pass_again()
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

    self::assertFalse($formValidatorResult->isSuccess());
    self::assertContains(
        '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required">',
        $formValidator->getHtml()
    );
    self::assertContains(
        '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required" aria-invalid="true">',
        $formValidatorResult->getHtml()
    );
    self::assertSame($expectedValidatorResult, $formValidatorResult->getErrorMessages());
  }

  /**
   * @test
   */
  public function it_returns_parsed_html()
  {
    $formHTML = $this->getBasicValidForm();

    $formValidator = new Validator($formHTML);

    self::assertContains(
        '<input type="email" id="email" name="email" value="" data-validator="email|maxLength(20)" required="required">',
        $formValidator->getHtml()
    );

    self::assertContains(
        '<input type="submit">',
        $formValidator->getHtml()
    );
  }

  /**
   * @test
   */
  public function it_returns_true_if_form_rules_pass()
  {
    $formHTML = $this->getBasicValidForm();
    $formData = [
        'email' => 'foo@isanemail.com',
    ];

    $formValidatorResult = (new Validator($formHTML))->validate($formData);

    self::assertTrue(true, $formValidatorResult->isSuccess());
  }

  /**
   * @test
   */
  public function it_returns_true_if_the_given_input_matches_both_forms_rules()
  {
    $formHTML = $this->getFormWithAdditionalInputValidForm();
    $formData = [
        'email'    => 'foo@example.com',
        'username' => ' <p onclick="alert(\'hacked\')">lall</p> ',
    ];

    $formValidator = new Validator($formHTML);
    $formValidatorResult = $formValidator->validate($formData);

    self::assertSame(
        [
            'email'    => 'foo@example.com',
            'username' => '&lt;p onclick&equals;&quot;alert&lpar;&apos;hacked&apos;&rpar;&quot;&gt;lall&lt;&sol;p&gt;',
        ],
        $formValidatorResult->getValues()
    );
    self::assertSame(
        [
            'register' => [
                'email'    => 'email|notEmpty',
                'username' => 'notEmpty',
            ],
        ],
        $formValidator->getAllRules()
    );
    self::assertTrue($formValidatorResult->isSuccess());
  }

  /**
   * @test
   */
  public function it_returns_true_if_the_given_input_matches_form_rules()
  {
    $formHTML = $this->getBasicValidForm();
    $formData = [
        'email' => 'foo@example.com',
    ];

    $formValidatorResult = (new Validator($formHTML))->validate($formData);

    self::assertTrue($formValidatorResult->isSuccess());
  }

  /**
   * @test
   */
  public function it_select_html_from_via_selector()
  {
    $formHTML = $this->getTwoFormsInputValidAndInvalidForm();
    $formData = [
        'email' => 'foo@example.com',
    ];

    $formValidator = new Validator($formHTML, '#register');
    $formValidatorResult = $formValidator->validate($formData);

    self::assertSame(
        [
            'register' => [
                'email' => 'auto|Respect\Validation\Rules\Email',
            ],
        ],
        $formValidator->getAllRules()
    );
    self::assertTrue($formValidatorResult->isSuccess());
  }

  /**
   * @test
   */
  public function it_select_html_from_via_selector_without_form_data()
  {
    $formHTML = $this->getTwoFormsInputValidAndInvalidForm();
    $formData = [];

    $formValidator = new Validator($formHTML, '#register');
    $formValidatorResult = $formValidator->validate($formData);

    self::assertSame(
        [
            'register' => [
                'email' => 'auto|Respect\Validation\Rules\Email',
            ],
        ],
        $formValidator->getAllRules()
    );
    self::assertFalse($formValidatorResult->isSuccess());
  }

  /**
   * @test
   *
   * @expectedException voku\HtmlFormValidator\Exceptions\UnknownValidationRule
   */
  public function it_thows_an_exception_if_a_given_rule_is_unknown()
  {
    $formHTML = $this->getFormWithUnknownRule();
    $formData = [
        'unknown' => '???',
    ];

    (new Validator($formHTML))->validate($formData);
  }

  /**
   * @test
   *
   * @expectedException voku\HtmlFormValidator\Exceptions\NoValidationRule
   */
  public function it_thows_an_exception_if_no_rules()
  {
    $formHTML = '';
    $formData = [
        'foo' => 'bar',
    ];

    (new Validator($formHTML))->validate($formData, true);
  }
}
