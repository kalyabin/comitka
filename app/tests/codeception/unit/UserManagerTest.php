<?php

use app\components\ContributorApi;
use Codeception\Test\Unit;
use tests\codeception\fixtures\UserFixture;
use user\models\ChangePasswordForm;
use user\models\SignInForm;
use user\models\User;
use user\models\UserAccount;
use user\models\UserAccountForm;
use user\models\UserForm;
use user\UserModule;

/**
 * Test user manager: create user, update user and remove him
 *
 * @method User users(string $userKey) Get user fixture
 */
class UserManagerTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var UserModule
     */
    protected $userModule;

    /**
     * Test fixtures
     */
    public function _fixtures()
    {
        return [
            'users' => UserFixture::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->userModule = Yii::$app->getModule('user');
        parent::setUp();
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

        $this->assertInstanceOf(User::className(), $this->userModule->createAdmin($user));
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
     * @return User
     */
    public function testUpdateUserRoles()
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');
        $this->assertTrue($this->userModule->updateUserRoles($user, []));
        $this->assertEmpty($user->getUserRoles());
        $this->assertTrue($this->userModule->updateUserRoles($user, ['admin']));
        $this->assertContains('admin', $user->getUserRoles());

        return $user;
    }

    /**
     * Tests check user password
     *
     * @depends testCreateAdmin
     *
     * @return User
     */
    public function testCheckUserPassword()
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

        $this->assertTrue($this->userModule->checkUserPassword($user, 'password_active_user_1'));
        $this->assertFalse($this->userModule->checkUserPassword($user, '123123'));

        return $user;
    }

    /**
     * Tests get user checker
     *
     * @depends testCreateAdmin
     *
     * @return User
     */
    public function testUserChecker()
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

        $checkString = $this->userModule->getUserChecker($user);
        $this->assertNotEmpty($checkString);
        $this->assertInternalType('string', $checkString);

        // confirm retrieve email checker
        unset ($user->checker);

        $newCheckString = $this->userModule->getUserChecker($user);
        $this->assertEquals($newCheckString, $checkString);

        $testUser = $this->userModule->findUserByChecker('email_checker', $newCheckString);
        $this->assertInstanceOf(User::className(), $testUser);
        $this->assertEquals($testUser->id, $user->id);

        return $user;
    }

    /**
     * Test change user password
     *
     * @depends testUserChecker
     *
     * @return User
     */
    public function testChangeUserPassword(User $user)
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

        $checkString = $this->userModule->getUserChecker($user);
        $this->assertNotEmpty($checkString);
        $this->assertInternalType('string', $checkString);

        // e-mail checker will not to be set to null after change password
        $this->assertNotEmpty($user->checker->email_checker);

        $model = new ChangePasswordForm();

        $model->password = $model->confirmPassword = 'admin testing password';

        $this->assertTrue($this->userModule->changeUserPassword($model, $user));
        // test if new password set
        $this->assertTrue($this->userModule->checkUserPassword($user, $model->password));

        $this->assertNotEmpty($user->checker->email_checker);

        return $user;
    }

    /**
     * Test change user forgotten password
     *
     * @depends testChangeUserPassword
     *
     * @return User
     */
    public function testChangeUserForgottenPassword()
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

        $checkString = $this->userModule->getUserChecker($user);
        $this->assertNotEmpty($checkString);
        $this->assertInternalType('string', $checkString);

        // e-mail checker will be set to null after change password
        $this->assertNotEmpty($user->checker->email_checker);

        $model = new ChangePasswordForm();
        $model->password = $model->confirmPassword = 'new admin password';

        $this->assertTrue($this->userModule->changeUserForgottenPassword($model, $user));

        // test if checker has been removed
        $this->assertEmpty($user->checker->email_checker);
        // test if new password set
        $this->assertTrue($this->userModule->checkUserPassword($user, $model->password));

        return $user;
    }

    /**
     * Test sign in
     *
     * @depends testChangeUserForgottenPassword
     *
     * @return User
     */
    public function testSignIn(User $user)
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

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
                    'value' => 'password_active_user_1',
                    'isValid' => true,
                ]
            ],
        ];
        $this->getModule('\Helper\Unit')->validateModelAttributes($model, $attributes, $this);

        // locked user can't sign in
        $this->assertTrue($this->userModule->lockUser($user));
        $this->assertFalse($model->validate());

        $this->assertTrue($this->userModule->activateUser($user));
        $this->assertTrue($model->validate());

        $this->assertInstanceOf(User::className(), $model->getUser());

        $this->assertTrue($this->userModule->signInUser($model->getUser(), $model->password, null, true));

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
        $result = $this->userModule->createUser($user);
        $this->assertNotEmpty($user->id);
        $this->assertTrue($result, 'User successfully created');
        $this->assertNotEmpty($user->password);

        // activate user
        $foundUser = $this->userModule->findUserByChecker('email_checker', $user->checker->email_checker);
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
        $result = $this->userModule->changeUserForgottenPassword($changePasswordForm, $foundUser);
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
     * @depends testCreateUser
     */
    public function testUpdateUser()
    {
        /* @var $user UserForm */
        $user = UserForm::findOne($this->getModule('Yii2')->grabFixture('users', 'activeUser1')->id);

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
        $result = $this->userModule->updateUser($user);
        $this->assertTrue($result);
        $this->assertNotEquals($oldPassword, $user->password);

        // user can authenticate
        $this->assertTrue($user->canSignIn());

        return $user;
    }

    /**
     * Tests lock a user
     *
     * @return User
     *
     * @depends testUpdateUser
     */
    public function testLockAndActivate()
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

        $result = $this->userModule->lockUser($user);
        $this->assertTrue($result);

        $this->assertFalse($user->canSignIn());

        $result = $this->userModule->activateUser($user);
        $this->assertTrue($result);

        $this->assertTrue($user->canSignIn());

        return $user;
    }

    /**
     * Tests VCS bindings update
     *
     * @return User
     *
     * @depends testLockAndActivate
     */
    public function testVCSBindings()
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

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

        $this->assertTrue($this->userModule->updateVcsBindings($user, $accounts));

        $this->assertEquals(count($accounts), count($user->accounts));

        unset ($user->accounts);

        $accounts[0]->deletionFlag = true;

        $this->assertTrue($this->userModule->updateVcsBindings($user, $accounts));

        $this->assertEquals(1, count($user->accounts));

        /* @var $contributorApi ContributorApi */
        $contributorApi = Yii::$app->contributors;
        $getByName = $contributorApi->getContributor(UserAccount::TYPE_HG, 'testing user name hg');

        $this->assertInstanceOf(User::className(), $getByName);
        $this->assertEquals($getByName->id, $user->id);

        return $user;
    }

    /**
     * Tests delete a user
     *
     * @depends testVCSBindings
     */
    public function testDeleteUser()
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');
        $result = $user->delete();
        $this->assertEquals(1, $result);
    }
}
