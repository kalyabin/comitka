<?php

use svk\tests\StaticAppTestCase;
use user\models\ChangePasswordForm;
use user\models\User;
use user\models\UserForm;
use user\UserModule;

/**
 * Test user manager: create user, update user and remove him
 */
class UserManagerTest extends StaticAppTestCase
{
    use svk\tests\StaticTransactionalTrait;

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
     * @depends testUpdateUser
     */
    public function testActivateUser(User $user)
    {
        $result = self::$userModule->activateUser($user);
        $this->assertTrue($result);

        $this->assertTrue($user->canSignIn());

        return $user;
    }

    /**
     * Tests delete a user
     *
     * @param User $user
     * @depends testActivateUser
     */
    public function testDeleteUser(User $user)
    {
        $result = $user->delete();
        $this->assertEquals(1, $result);
    }
}
