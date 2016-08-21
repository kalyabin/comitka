<?php

use svk\tests\StaticAppTestCase;
use user\models\ChangePasswordForm;
use user\models\SignInForm;
use user\models\User;
use user\models\UserAccount;
use user\models\UserAccountForm;
use user\models\UserForm;
use user\UserModule;

/**
 * Test user manager: create user, update user and remove him
 */
class UserManagerTest extends StaticAppTestCase
{
    use svk\tests\StaticTransactionalTrait;
    use svk\tests\ModelTestTrait;

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var UserModule
     */
    protected static $userModule;

    public static function setUpBeforeClass()
    {
        self::beginStaticTransaction();

        self::$userModule = Yii::$app->getModule('user');
    }

    public static function tearDownAfterClass()
    {
        self::rollBackStaticTransaction();
    }

    /**
     * Tests create simple admin user
     *
     * @return User
     */
    public function testCreateAdmin()
    {
        $user = new User();

        $user->email = 'admin@comitka.test';
        $user->name = 'Testing admin';
        $user->newPassword = 'testing admin password';

        $this->assertInstanceOf(User::className(), self::$userModule->createAdmin($user));
        $this->assertFalse($user->isNewRecord);
        $this->assertNotEmpty($user->id);
        $this->assertContains('admin', $user->getUserRoles());

        return $user;
    }

    /**
     * Tests update user roles
     *
     * @depends testCreateAdmin
     *
     * @param User $user
     *
     * @return User
     */
    public function testUpdateUserRoles(User $user)
    {
        $this->assertTrue(self::$userModule->updateUserRoles($user, []));
        $this->assertEmpty($user->getUserRoles());
        $this->assertTrue(self::$userModule->updateUserRoles($user, ['admin']));
        $this->assertContains('admin', $user->getUserRoles());

        return $user;
    }

    /**
     * Tests check user password
     *
     * @depends testCreateAdmin
     *
     * @param User $user
     *
     * @return User
     */
    public function testCheckUserPassword(User $user)
    {
        $this->assertTrue(self::$userModule->checkUserPassword($user, 'testing admin password'));
        $this->assertFalse(self::$userModule->checkUserPassword($user, '123123'));

        return $user;
    }

    /**
     * Tests get user checker
     *
     * @depends testCreateAdmin
     *
     * @param User $user
     *
     * @return User
     */
    public function testUserChecker(User $user)
    {
        $checkString = self::$userModule->getUserChecker($user);
        $this->assertNotEmpty($checkString);
        $this->assertInternalType('string', $checkString);

        // confirm retrieve email checker
        unset ($user->checker);

        $newCheckString = self::$userModule->getUserChecker($user);
        $this->assertEquals($newCheckString, $checkString);

        $testUser = self::$userModule->findUserByChecker('email_checker', $newCheckString);
        $this->assertInstanceOf(User::className(), $testUser);
        $this->assertEquals($testUser->id, $user->id);

        return $user;
    }

    /**
     * Test change user password
     *
     * @depends testUserChecker
     *
     * @param User $user
     *
     * @return User
     */
    public function testChangeUserPassword(User $user)
    {
        // e-mail checker will not to be set to null after change password
        $this->assertNotEmpty($user->checker->email_checker);

        $model = new ChangePasswordForm();

        $model->password = $model->confirmPassword = 'admin testing password';

        $this->assertTrue(self::$userModule->changeUserPassword($model, $user));
        // test if new password set
        $this->assertTrue(self::$userModule->checkUserPassword($user, $model->password));

        $this->assertNotEmpty($user->checker->email_checker);

        return $user;
    }

    /**
     * Test change user forgotten password
     *
     * @depends testChangeUserPassword
     *
     * @param User $user
     *
     * @return User
     */
    public function testChangeUserForgottenPassword(User $user)
    {
        // e-mail checker will be set to null after change password
        $this->assertNotEmpty($user->checker->email_checker);

        $model = new ChangePasswordForm();
        $model->password = $model->confirmPassword = 'new admin password';

        $this->assertTrue(self::$userModule->changeUserForgottenPassword($model, $user));

        // test if checker has been removed
        $this->assertEmpty($user->checker->email_checker);
        // test if new password set
        $this->assertTrue(self::$userModule->checkUserPassword($user, $model->password));

        return $user;
    }

    /**
     * Test sign in
     *
     * @depends testChangeUserForgottenPassword
     *
     * @param User $user
     *
     * @return User
     */
    public function testSignIn(User $user)
    {
        $model = new SignInForm();

        $attributes = [
            'email' => [
                [
                    'value' => null,
                    'isValid' => false
                ],
                [
                    'value' => 0,
                    'isValid' => false
                ],
                [
                    'value' => '',
                    'isValid' => false
                ],
                [
                    'value' => 'wrong e-mail',
                    'isValid' => false
                ],
                [
                    'value' => $user->email,
                    'isValid' => true,
                ]
            ],
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
                    'value' => 'wrong user password',
                    'isValid' => false,
                ],
                [
                    'value' => 'new admin password',
                    'isValid' => true,
                ]
            ],
        ];
        $this->validateAttributes($model, $attributes);

        // locked user can't sign in
        $this->assertTrue(self::$userModule->lockUser($user));
        $this->assertFalse($model->validate());

        $this->assertTrue(self::$userModule->activateUser($user));
        $this->assertTrue($model->validate());

        $this->assertInstanceOf(User::className(), $model->getUser());

        $this->assertTrue(self::$userModule->signInUser($model->getUser(), $model->password, null, true));

        return $user;
    }

    /**
     * Tests user create form
     */
    public function testCreateUser()
    {
        $user = new UserForm();

        $user->setScenario('create');

        // create user and check every error
        $this->assertFalse($user->validate(), 'Check error validation');
        $this->assertArrayHasKey('roles', $user->getErrors(), 'Check has roles error');
        $user->roles[] = 'admin';
        $this->assertFalse($user->validate());
        $this->assertArrayHasKey('name', $user->getErrors(), 'Check has name error');
        $user->name = 'Tester';
        $this->assertFalse($user->validate());
        $this->assertArrayHasKey('email', $user->getErrors(), 'Check has email error');
        $user->email = 'wrong email format';
        $this->assertArrayHasKey('email', $user->getErrors(), 'Check has wrong e-mail format');
        $user->email = 'tester@test.ru';
        $user->sendNotification = true;
        $this->assertTrue($user->validate(), 'Check every field is validated');

        // create user model
        $result = self::$userModule->createUser($user);
        $this->assertNotEmpty($user->id);
        $this->assertTrue($result, 'User successfully created');
        $this->assertNotEmpty($user->password);

        // activate user
        $foundUser = self::$userModule->findUserByChecker('email_checker', $user->checker->email_checker);
        $this->assertInstanceOf(User::className(), $foundUser);
        $this->assertEquals($foundUser->id, $user->id);

        // change user password
        $changePasswordForm = new ChangePasswordForm();
        $this->assertFalse($changePasswordForm->validate(), 'Check error validation');
        $this->assertArrayHasKey('password', $changePasswordForm->getErrors(), 'Check has password error');
        $changePasswordForm->password = 'new user password';
        $this->assertFalse($changePasswordForm->validate());
        $this->assertArrayHasKey('confirmPassword', $changePasswordForm->getErrors(), 'Check has confirmPassword error');
        $changePasswordForm->confirmPassword = 'wrong new user password';
        $this->assertFalse($changePasswordForm->validate());
        $this->assertArrayHasKey('confirmPassword', $changePasswordForm->getErrors(), 'Check has confirmPassword error');
        $changePasswordForm->confirmPassword = 'new user password';
        $this->assertTrue($changePasswordForm->validate(), 'Check every field is validated');
        $result = self::$userModule->changeUserForgottenPassword($changePasswordForm, $foundUser);
        $this->assertTrue($result, 'Password successfully changed');
        $this->assertNull($foundUser->checker->email_checker);

        // user can authenticate
        $this->assertTrue($user->canSignIn());

        // create new user with exists data
        $newUser = new UserForm();
        $newUser->setAttributes($user->getAttributes());
        $this->assertFalse($newUser->validate());
        $this->assertArrayHasKey('email', $newUser->getErrors(), 'Check user already exists');

        return $foundUser;
    }

    /**
     * Tests user  update form
     *
     * @param User $user
     * @depends testCreateUser
     */
    public function testUpdateUser(User $user)
    {
        $user = UserForm::findOne($user->id);
        $this->assertInstanceOf(UserForm::className(), $user);

        $user->setScenario('update');

        $oldPassword = $user->password;

        // remove role
        $user->roles = [];
        $this->assertFalse($user->validate());
        $this->assertArrayHasKey('roles', $user->getErrors(), 'Check empty roles');
        $user->roles[] = 'admin';

        // generate new password
        $user->generateRandomPassword = true;
        $user->sendNotification = true;
        $result = self::$userModule->updateUser($user);
        $this->assertTrue($result);
        $this->assertNotEquals($oldPassword, $user->password);

        // user can authenticate
        $this->assertTrue($user->canSignIn());

        return $user;
    }

    /**
     * Tests update a user
     *
     * @param User $user
     * @return User
     *
     * @depends testUpdateUser
     */
    public function testLockUser(User $user)
    {
        $result = self::$userModule->lockUser($user);
        $this->assertTrue($result);

        $this->assertFalse($user->canSignIn());

        return $user;
    }

    /**
     * Tests activate a user
     *
     * @param User $user
     * @return User
     *
     * @depends testLockUser
     */
    public function testActivateUser(User $user)
    {
        $result = self::$userModule->activateUser($user);
        $this->assertTrue($result);

        $this->assertTrue($user->canSignIn());

        return $user;
    }

    /**
     * Tests VCS bindings update
     *
     * @param User $user
     * @return User
     *
     * @depends testActivateUser
     */
    public function testVCSBindings(User $user)
    {
        $accounts = [
            0 => new UserAccountForm([
                'username' => 'testing user name git',
                'type' => UserAccount::TYPE_GIT,
            ]),
            1 => new UserAccountForm([
                'username' => 'testing user name hg',
                'type' => UserAccount::TYPE_HG,
            ])
        ];

        $this->assertTrue(self::$userModule->updateVcsBindings($user, $accounts));

        $this->assertEquals(count($accounts), count($user->accounts));

        unset ($user->accounts);

        $accounts[0]->deletionFlag = true;

        $this->assertTrue(self::$userModule->updateVcsBindings($user, $accounts));

        $this->assertEquals(1, count($user->accounts));

        $getByName = self::$userModule->getUserByUsername(UserAccount::TYPE_HG, 'testing user name hg');

        $this->assertInstanceOf(User::className(), $getByName);
        $this->assertEquals($getByName->id, $user->id);

        return $user;
    }

    /**
     * Tests delete a user
     *
     * @param User $user
     *
     * @depends testVCSBindings
     */
    public function testDeleteUser(User $user)
    {
        $result = $user->delete();
        $this->assertEquals(1, $result);
    }
}
