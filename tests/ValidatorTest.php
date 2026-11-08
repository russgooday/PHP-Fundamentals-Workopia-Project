<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Framework\Validation\Validator;
use Framework\Validation\ValidatorChecks;
use Framework\Validation\Model\Validations;
use Framework\FormRequest;

class TestForm extends FormRequest {

}

class ValidatorTest extends TestCase {

    private static array $messages;

    public static function setUpBeforeClass(): void {
        self::$messages = require dirname(__DIR__) . '/src/Framework/Validation/messages.php';
    }

    private function makeValidator(array $data, array $rules): Validator {
        $checks = new ValidatorChecks($this->createStub(Validations::class));

        return new Validator($data, $rules, $checks, self::$messages);
    }


    /**
     * Each case: [rules, data, expected pass/fail]
     * The field under test is always named 'field'.
     */
    public static function validationCases(): array {
        return [
            // required
            'required: missing key'             => [['required'], [],                       false],
            'required: empty string'            => [['required'], ['field' => ''],          false],
            'required: null value'              => [['required'], ['field' => null],        false],
            'required: "0" counts as present'   => [['required'], ['field' => '0'],         true ],
            'required: value present'           => [['required'], ['field' => 'x'],         true ],

            // nullable
            'nullable: missing key'             => [['nullable', 'email'], [],              true ],
            'nullable: empty string'            => [['nullable', 'email'], ['field' => ''], true ],
            'nullable: value still validated'   => [['nullable', 'email'], ['field' => 'x'], false],

            // sometimes
            'sometimes: missing key is skipped' => [['sometimes', 'required'], [],                  true ],
            'sometimes: present key validated'  => [['sometimes', 'required'], ['field' => ''],     false],

            // string (Laravel-style: 'required' alone accepts an array, 'string' guards it)
            'required alone: array accepted'    => [['required'],           ['field' => ['x']], true ],
            'string: array rejected'            => [['required', 'string'], ['field' => ['x']], false],
            'string: string accepted'           => [['required', 'string'], ['field' => 'x'],   true ],

            // array
            'array: any array accepted'         => [['array'],             ['field' => ['a', 'b']],         true ],
            'array: string rejected'            => [['array'],             ['field' => 'x'],                false],
            'array:keys: allowed key'           => [['array:name,email'],  ['field' => ['name' => 'a']],    true ],
            'array:keys: unexpected key'        => [['array:name,email'],  ['field' => ['admin' => '1']],   false],

            // numeric
            'numeric: number string'            => [['numeric'], ['field' => '123'],   true ],
            'numeric: decimal string'           => [['numeric'], ['field' => '12.5'],  true ],
            'numeric: letters rejected'         => [['numeric'], ['field' => 'abc'],   false],
            'numeric: array rejected'           => [['numeric'], ['field' => ['1']],   false],

            // email
            'email: valid'                      => [['email'], ['field' => 'a@b.com'],  true ],
            'email: invalid'                    => [['email'], ['field' => 'not-an-email'], false],
            'email: array rejected'             => [['email'], ['field' => ['a', 'b']], false],

            // password
            'password: valid'                   => [['password'], ['field' => 'Valid1@pass'], true ],
            'password: no uppercase'            => [['password'], ['field' => 'valid1@pass'], false],
            'password: no lowercase'            => [['password'], ['field' => 'VALID1@PASS'], false],
            'password: no number'               => [['password'], ['field' => 'Valid@pass'],  false],
            'password: no symbol'               => [['password'], ['field' => 'Valid1pass'],  false],
            'password: disallowed character'    => [['password'], ['field' => 'Valid1@ pass'], false],
            'password: null rejected'           => [['password'], ['field' => null],          false],
            'password: array rejected'          => [['password'], ['field' => ['x']],         false],

            // pattern (commas and colons must survive rule parsing)
            'pattern: match'                    => [['pattern:^[a-z]+$'],       ['field' => 'abc'],   true ],
            'pattern: no match'                 => [['pattern:^[a-z]+$'],       ['field' => 'abc1'],  false],
            'pattern: comma kept intact'        => [['pattern:^a{2,3}$'],       ['field' => 'aaa'],   true ],
            'pattern: colon kept intact'        => [['pattern:^\d{2}:\d{2}$'],  ['field' => '12:30'], true ],
            'pattern: colon pattern no match'   => [['pattern:^\d{2}:\d{2}$'],  ['field' => '12'],    false],
            'pattern: null rejected'            => [['pattern:^[a-z]+$'],       ['field' => null],    false],
            'pattern: array rejected'           => [['pattern:^[a-z]+$'],       ['field' => ['x']],   false],

            // size rules: strings measure characters
            'max string: at limit'              => [['string', 'max:5'],  ['field' => 'hello'],   true ],
            'max string: over limit'            => [['string', 'max:5'],  ['field' => 'hello!'],  false],
            'min string: under limit'           => [['string', 'min:3'],  ['field' => 'hi'],      false],
            'between string: inside'            => [['string', 'between:2,4'], ['field' => 'abc'], true ],

            // size rules: numeric measures the value
            'max numeric: at limit'             => [['numeric', 'max:10'], ['field' => '10'],  true ],
            'max numeric: over limit'           => [['numeric', 'max:10'], ['field' => '11'],  false],
            'min numeric: under limit'          => [['numeric', 'min:5'],  ['field' => '4'],   false],

            // size rules: arrays measure the count
            'max array: at limit'               => [['array', 'max:2'], ['field' => ['a', 'b']],      true ],
            'max array: over limit'             => [['array', 'max:2'], ['field' => ['a', 'b', 'c']], false],

            // size rules on a missing field (no required/nullable): must not crash on mb_strlen(null)
            'max: missing field'                => [['max:5'],           [], true],
            'min: missing field'                => [['min:3'],           [], true],
            'between: missing field'            => [['between:2,4'],     [], true],
            'max numeric: missing field'        => [['numeric', 'max:10'], [], true],

            // size rules on a null value (no required/nullable): null measures as 0
            'max: null value'                   => [['max:5'],       ['field' => null], true ],
            'min: null value'                   => [['min:3'],       ['field' => null], false],
            'between: null value'               => [['between:2,4'], ['field' => null], false],
        ];
    }

    #[DataProvider('validationCases')]
    public function testValidate(array $rules, array $data, bool $expected): void {
        $validator = $this->makeValidator($data, ['field' => $rules]);

        $this->assertSame($expected, $validator->validate());
    }


    public function testErrorMessageForRequired(): void {
        $validator = $this->makeValidator([], ['title' => ['required']]);
        $validator->validate();

        $this->assertSame(
            ['title' => 'The title field is required.'],
            $validator->getErrors()
        );
    }

    public function testErrorMessageForMaxUsesStringVariant(): void {
        $validator = $this->makeValidator(['title' => 'hello!'], ['title' => ['string', 'max:5']]);
        $validator->validate();

        $this->assertSame('The title field must be at most 5 characters.', $validator->getErrors()['title']);
    }

    public function testErrorMessageForMaxUsesNumericVariant(): void {
        $validator = $this->makeValidator(['salary' => '11'], ['salary' => ['numeric', 'max:10']]);
        $validator->validate();

        $this->assertSame('The salary field must be at most 10.', $validator->getErrors()['salary']);
    }

    public function testErrorMessageForPassword(): void {
        $validator = $this->makeValidator(['password' => 'weakpass'], ['password' => ['password']]);
        $validator->validate();

        $this->assertSame(
            'The password field must contain at least 1 uppercase, 1 lowercase, 1 number, and 1 symbol.',
            $validator->getErrors()['password']
        );
    }

    public function testErrorMessageForPattern(): void {
        $validator = $this->makeValidator(['code' => 'abc1'], ['code' => ['pattern:^[a-z]+$']]);
        $validator->validate();

        $this->assertSame('The code field format is invalid.', $validator->getErrors()['code']);
    }

    public function testCustomMessageOverridesDefault(): void {
        $validator = new Validator(
            ['code' => 'abc1'],
            ['code' => ['pattern:^[a-z]+$']],
            new ValidatorChecks($this->createStub(Validations::class)),
            ['code.pattern' => fn() => 'Lowercase letters only.']
        );
        $validator->validate();

        $this->assertSame('Lowercase letters only.', $validator->getErrors()['code']);
    }

    public function testOnlyFirstFailingRuleIsReported(): void {
        $validator = $this->makeValidator(['email' => ''], ['email' => ['required', 'email']]);
        $validator->validate();

        $this->assertSame('The email field is required.', $validator->getErrors()['email']);
    }

    public function testErrorsAreKeyedPerField(): void {
        $validator = $this->makeValidator(
            ['title' => '', 'email' => 'nope'],
            ['title' => ['required'], 'email' => ['required', 'email']]
        );
        $validator->validate();

        $this->assertSame(['title', 'email'], array_keys($validator->getErrors()));
    }

    public function testUnknownRuleThrows(): void {
        $validator = $this->makeValidator(['title' => 'x'], ['title' => ['made_up_rule']]);

        $this->expectException(Exception::class);
        $validator->validate();
    }

    public function testValidatedReturnsOnlyFieldsWithRules(): void {
        $validator = $this->makeValidator(
            ['title' => 'x', '_method' => 'PUT', 'junk' => 'y'],
            ['title' => ['required']]
        );
        $validator->validate();

        $this->assertSame(['title' => 'x'], $validator->validated());
    }

    public function testInputsAreSame(): void {
        $validator = $this->makeValidator(
            ['password' => 'password_123', 'password_confirmation' => 'password_123'],
            ['password' => ['required'], 'password_confirmation' => ['required', 'same:password']]
        );

        $this->assertSame(true, $validator->validate());
    }

    public function testErrorMessageForSameNamesBothFields(): void {
        $validator = $this->makeValidator(
            ['password' => 'password_123', 'password_confirmation' => 'password_456'],
            ['password' => ['required'], 'password_confirmation' => ['required', 'same:password']]
        );
        $validator->validate();

        $this->assertSame(
            'The password_confirmation field must match the password field.',
            $validator->getErrors()['password_confirmation']
        );
    }
}
