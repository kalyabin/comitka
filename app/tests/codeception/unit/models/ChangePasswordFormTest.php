<?php
namespace models;

use user\models\ChangePasswordForm;

/**
 * Test change password form model
 */
class ChangePasswordFormTest extends \svk\tests\StaticAppTestCase
{
    use \svk\tests\StaticTransactionalTrait;
    use \svk\tests\ModelTestTrait;

    /**
     * @var UnitTester
     */
    protected $tester;

    public static function setUpBeforeClass()
    {
        self::beginStaticTransaction();
    }

    public static function tearDownAfterClass()
    {
        self::rollBackStaticTransaction();
    }

    public function testTestMe()
    {
        $model = new ChangePasswordForm();

        $attributes = [
            'password' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => '123',
                    'isValid' => false,
                ],
                [
                    'value' => '123123',
                    'isValid' => true,
                ]
            ],
            'confirmPassword' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => '123',
                    'isValid' => false,
                ],
                [
                    'value' => '123456',
                    'isValid' => false,
                ],
                [
                    'value' => '123123',
                    'isValid' => true,
                ]
            ],
        ];

        $this->validateAttributes($model, $attributes);
    }
}
