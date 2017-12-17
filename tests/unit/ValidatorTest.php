<?php

use Respect\Validation\Validator as v;
use voku\HtmlFormValidator\Validator;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
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
            data-validator="email"
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
            data-validator="auto"
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
            data-filter="strip_tags|trim|escape"
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
                'user[1][email]' => 'Respect\Validation\Rules\Email',
                'user[2][name]'  => 'notEmpty',
            ],
        ],
        $rules
    );
    self::assertCount(2, $rules['user-register']);

    // ---

    $formData = [
        'user' => [
            '1' => ['email' => 'foo@isanemail.com'],
            '2' => ['name' => 'bar'],
        ],
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // ---

    $formData = [
        'user' => [
            '1' => ['email' => 'foo@isanemail.com'],
            '2' => ['name' => 'bar'],
        ],
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [],
        $formValidatorResult->getErrorMessages()
    );

    // ---

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

    // ---

    $formData = [
        'email' => 'foo@isanemail.com',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // ---

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

    // ---

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

    // ---

    $formData = [
        'email' => 'foo@isanemail.com',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // ---

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

    // ---

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
    $formValidator->addCustomRule('foobar', v::intVal());

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

    // ---

    $formData = [
        'email' => 'foo@isanemail.com',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame([], $formValidatorResult->getErrorMessages());

    // ---

    $formData = [
        'email' => 'foo@isanemail.com',
        'lall'  => 'noop',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'lall' => [
                0 => '"noop" must be an integer number',
            ],
        ],
        $formValidatorResult->getErrorMessages()
    );

    // ---

    $formData = [
        'email' => 'foo@isanemail.com',
        'lall'  => 'foobar',
    ];
    $formValidatorResult = $formValidator->validate($formData);
    self::assertSame(
        [
            'lall' => [
                0 => '"foobar" must be an integer number',
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
        '<input type="email" id="email" name="email" value="" data-validator="email" required="required">',
        $formValidator->getHtml()
    );
    self::assertContains(
        '<input type="email" id="email" name="email" value="" data-validator="email" required="required" aria-invalid="true">',
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
        '<input type="email" id="email" name="email" value="" data-validator="email" required="required">',
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
            'username' => 'lall',
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
                'email' => 'Respect\Validation\Rules\Email',
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
                'email' => 'Respect\Validation\Rules\Email',
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

    (new Validator($formHTML))->validate($formData);
  }
}
