<?php
namespace models;

use UnitTestCase;
use UnitTester;
use user\models\ChangePasswordForm;

/**
 * Test change password form model
 */
class ChangePasswordFormTest extends UnitTestCase
{
    /**
     * @var UnitTester
     */
    protected $tester;

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

        $this->getModule('\Helper\Unit')->validateModelAttributes($model, $attributes, $this);
    }
}
