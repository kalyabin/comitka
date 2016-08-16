<?php
namespace models;

use svk\tests\StaticAppTestCase;
use UnitTester;
use user\models\User;
use user\models\UserAccount;
use user\models\UserChecker;

/**
 * Test user model
 */
class UserTest extends StaticAppTestCase
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

    /**
     * Test model validation and save
     *
     * @return User
     */
    public function testValidationAndSave()
    {
        $model = new User();

        $attributes = [
            'name' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => '',
                    'isValid' => false,
                ],
                [
                    'value' => str_repeat('A', User::MAX_NAME_LENGTH + 1),
                    'isValid' => false,
                ],
                [
                    'value' => str_repeat('A', User::MAX_EMAIL_LENGTH),
                    'isValid' => true,
                ]
            ],
            'email' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => '',
                    'isValid' => false,
                ],
                [
                    'value' => 'wrong email',
                    'isValid' => false,
                ],
                [
                    'value' => str_repeat('A', User::MAX_EMAIL_LENGTH - 4) . '@a.ru',
                    'isValid' => false,
                ],
                [
                    'value' => 'tester@test.ru',
                    'isValid' => true,
                ]
            ],
            'status' => [
                [
                    'value' => -100,
                    'isValid' => false,
                ],
                [
                    'value' => 'wrong list value',
                    'isValid' => false,
                ],
                [
                    'value' => User::STATUS_BLOCKED,
                    'isValid' => true,
                ],
                [
                    'value' => User::STATUS_UNACTIVE,
                    'isValid' => true,
                ],
                [
                    'value' => User::STATUS_ACTIVE,
                    'isValid' => true,
                ],
            ],
            'password' => [
                [
                    'value' => str_repeat('A', 256),
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => '',
                    'isValid' => false,
                ],
                [
                    'value' => str_repeat('A', 255),
                    'isValid' => true,
                ]
            ]
        ];

        $this->validateAttributes($model, $attributes);

        $model->newPassword = str_repeat('A', 255);
        $model->password = null;

        $this->validateAttributes($model, [
            'password' => [
                [
                    'value' => null,
                    'isValid' => true,
                ]
            ]
        ]);

        $this->assertTrue($model->validate());
        $this->assertTrue($model->save());

        return $model;
    }

    /**
     * Test unique email
     *
     * @param User $model
     *
     * @depends testValidationAndSave
     *
     * @return User
     */
    public function testUniqueEmail(User $model)
    {
        $newModel = new User();

        $newModel->setAttributes($model->getAttributes());

        $this->assertFalse($newModel->validate());
        $this->assertArrayHasKey('email', $newModel->getErrors());

        return $model;
    }

    /**
     * Test user's checker model
     *
     * @param User $model
     *
     * @depends testValidationAndSave
     *
     * @return User
     */
    public function testUserChecker(User $model)
    {
        $this->assertInstanceOf(UserChecker::className(), $model->checker);

        /* @var $checker UserChecker */
        $checker = $model->checker;

        $this->assertEquals($checker->user_id, $model->id);

        $attributes = [
            'email_checker' => [
                [
                    'value' => null,
                    'isValid' => true,
                ],
                [
                    'value' => str_repeat('A', 33),
                    'isValid' => false,
                ],
                [
                    'value' => str_repeat('A', 32),
                    'isValid' => true,
                ]
            ],
            'user_id' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => 'wrong integer',
                    'isValid' => false,
                ],
                [
                    'value' => $model->id,
                    'isValid' => true,
                ]
            ]
        ];

        $this->validateAttributes($checker, $attributes);

        $this->assertTrue($checker->save());

        // check unique user_id
        $newModel = new UserChecker();
        $newModel->setAttributes($checker->getAttributes());

        $this->assertFalse($newModel->validate());
        $this->assertArrayHasKey('user_id', $newModel->getErrors());

        $this->assertInstanceOf(User::className(), $checker->user);
        $this->assertInstanceOf(UserChecker::className(), $model->checker);

        $this->assertEquals($model->checker->id, $checker->id);
        $this->assertEquals($checker->user->id, $model->id);

        return $model;
    }

    /**
     * Test user accounts relations
     *
     * @param User $user
     *
     * @depends testValidationAndSave
     *
     * @return User
     */
    public function testUserAccounts(User $user)
    {
        $model = new UserAccount();

        $attributes = [
            'user_id' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => -100,
                    'isValid' => false,
                ],
                [
                    'value' => $user->id,
                    'isValid' => true,
                ]
            ],
            'username' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => str_repeat('A', 101),
                    'isValid' => false,
                ],
                [
                    'value' => str_repeat('A', 100),
                    'isValid' => true,
                ]
            ],
            'type' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => 'wrong type',
                    'isValid' => false,
                ],
                [
                    'value' => UserAccount::TYPE_HG,
                    'isValid' => true,
                ],
                [
                    'value' => UserAccount::TYPE_SVN,
                    'isValid' => true,
                ],
                [
                    'value' => UserAccount::TYPE_GIT,
                    'isValid' => true,
                ],
            ],
        ];

        $this->validateAttributes($model, $attributes);

        $this->assertTrue($model->save());

        // check unique record with type and username columns
        $newModel = new UserAccount();
        $newModel->setAttributes($model->getAttributes());

        $this->assertFalse($newModel->validate());
        $this->assertArrayHasKey('type', $newModel->getErrors());
        $this->assertArrayHasKey('username', $newModel->getErrors());

        $newModel->type = UserAccount::TYPE_HG;
        $this->assertTrue($newModel->validate());

        $this->assertTrue($newModel->save());

        $this->assertContainsOnly(UserAccount::className(), $user->accounts);
        $this->assertCount(2, $user->accounts);

        $this->assertInstanceOf(User::className(), $model->user);
        $this->assertEquals($user->id, $model->user->id);

        $this->assertInstanceOf(User::className(), $newModel->user);
        $this->assertEquals($user->id, $newModel->user->id);

        return $user;
    }

    /**
     * Test delete user model
     *
     * @param User $model
     *
     * @depends testUserAccounts
     */
    public function testRemoveUser(User $model)
    {
        $this->assertEquals(1, $model->delete());
    }
}
