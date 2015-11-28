<?php
namespace user;

use Exception;
use user\components\Auth;
use user\models\ChangePasswordForm;
use user\models\ProfileForm;
use user\models\User;
use user\models\UserChecker;
use user\models\UserForm;
use Yii;
use yii\base\Module as BaseModule;
use yii\helpers\Url;
use yii\rbac\Assignment;
use yii\rbac\DbManager;
use yii\rbac\Item;

/**
 * Provides API to manage users
 */
class Module extends BaseModule
{
    /**
     * Generate hash using original password
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Creates administrative account.
     * Returns User model if everything okay.
     *
     * @param User $user
     * @return User
     * @throws Exception
     */
    public function createAdmin(User $user)
    {
        $transaction = $user->getDb()->beginTransaction();

        try {
            if (!$user->save()) {
                throw new Exception();
            }

            $this->updateUserRoles($user, ['admin']);

            $transaction->commit();

            return $user;
        } catch (Exception $ex) {
            $transaction->rollBack();
            throw $ex;
        }
    }

    /**
     * Updates user roles from $roles.
     * An input array indicates roles identifiers.
     * Previous user role will be removed.
     *
     * @param User $user
     * @param string[] $roles
     * @return boolean true if everythings okay.
     * @throws Exception
     */
    public function updateUserRoles(User $user, array $roles)
    {
        /* @var $authManager DbManager */
        $authManager = Yii::$app->authManager;

        $transaction = $user->getDb()->beginTransaction();
        try {
            /* @var $exists Assignment[] */
            $user->getDb()->createCommand()->delete($authManager->assignmentTable, 'user_id=:userId', [
                ':userId' => $user->id,
            ])->execute();

            foreach ($roles as $role) {
                $role = $authManager->getRole($role);
                if ($role instanceof Item) {
                    $authManager->assign($role, $user->id);
                }
            }

            $transaction->commit();

            return true;
        } catch (Exception $ex) {
            $transaction->rollBack();
            throw $ex;
        }
    }

    /**
     * Returns true if password is correct user's password.
     *
     * @param User $user
     * @param string $password
     * @return boolean
     */
    public function checkUserPassword(User $user, $password)
    {
        return Yii::$app->security->validatePassword($password, $user->password);
    }

    /**
     * Sign in user.
     * Return true if it's ok.
     * Send password to validate it.
     *
     * @param User $user user's identity object
     * @param string $password user's password to validate
     * @param integer $duration login duration at seconds
     * @return boolean
     */
    public function signInUser(User $user, $password, $duration = null)
    {
        if (is_null($duration)) {
            $duration = 24*60*60*30*24;
        }
        /* @var $auth Auth */
        $auth = Yii::$app->user;
        if ($user->canSignIn() && $this->checkUserPassword($user, $password)) {
            return $auth->login($user, $duration);
        }
        return false;
    }

    /**
     * Find a user by him checker.
     * Set a checker name (UserChecker attribute) and checker value.
     * Returns user's model or null.
     *
     * @param string $checkerName
     * @param string $checkerValue
     * @return User|null
     */
    public function findUserByChecker($checkerName, $checkerValue)
    {
        $checkerName = (string) $checkerName;
        $checkerValue = (string) $checkerValue;

        return User::find()
            ->joinWith('checker')
            ->andWhere([UserChecker::tableName() . '.' . $checkerName => $checkerValue])
            ->one();
    }

    /**
     * Get user check string.
     *
     * @param User $user user model
     * @param string $field checker type
     * @return string|null
     * @throws Exception
     */
    protected function getUserChecker(User $user, $field = 'email_checker')
    {
        $checker = $user->checker;
        if (!trim($checker->{$field})) {
            // generate new e-mail checker hash
            try {
                $checker->{$field} = md5($user->id . $user->email . time());
                if (!$checker->save(false, [$field])) {
                    throw new Exception();
                }
            }
            catch (Exception $ex) {
                return null;
            }
        }

        return $checker->{$field};
    }

    /**
     * Send forgot password e-mail.
     * Generates new e-mail checker hash if not exists.
     *
     * @param User $user
     * @return boolean true if successfully sent
     * @throws Exception
     */
    public function sendForgotPasswordEmail(User $user)
    {
        $checker = $this->getUserChecker($user, 'email_checker');

        // send e-mail
        $changePasswordLink = Url::toRoute(['/user/auth/change-password',
            'hash' => $checker,
        ], true);
        return Yii::$app->mailer->compose('userChangeForgotPassword', [
            'user' => $user,
            'link' => $changePasswordLink,
        ])
        ->setTo($user->email)
        ->setSubject(Yii::t('user', 'Forgot password'))
        ->send();
    }

    /**
     * Change user's profile from form.
     * TODO: implements avatars and e-mail change (?).
     *
     * @param ProfileForm $form
     * @param User $user
     * @return boolean
     */
    public function changeUserProfile(ProfileForm $form, User $user)
    {
        if (!$form->validate()) {
            // form is not valid
            return false;
        }

        $user->name = $form->name;
        try {
            if (!$user->save()) {
                throw new Exception();
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Change user's password from profile
     *
     * @param ChangePasswordForm $form
     * @param User $user
     * @return boolean
     */
    public function changeUserPassword(ChangePasswordForm $form, User $user)
    {
        if (!$form->validate()) {
            // form is not valid
            return false;
        }

        $user->newPassword = $form->password;
        try {
            if (!$user->save()) {
                throw new Exception();
            }
        }
        catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * Change user's forgotten password.
     * Sets new password to user's model and remove e-mail checker.
     *
     * @param ChangePasswordForm $form
     * @param User $user
     * @return boolean
     */
    public function changeUserForgottenPassword(ChangePasswordForm $form, User $user)
    {
        if (!$form->validate()) {
            // form is not valid
            return false;
        }

        $user->newPassword = $form->password;

        $transaction = $user->getDb()->beginTransaction();
        try {
            // save user model
            if (!$user->save()) {
                throw new Exception();
            }
            // remove e-mail checker
            $checker = $user->checker;
            $checker->email_checker = null;
            if (!$checker->save(false, ['email_checker'])) {
                throw new Exception();
            }
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return false;
        }

        return true;
    }

    /**
     * Update user roles
     *
     * @param UserForm $user
     */
    protected function updateRoles(UserForm $user)
    {
        /* @var $authManager DbManager */
        $authManager = Yii::$app->authManager;
        /* @var $db Connection */
        $db = $authManager->db;

        // remove exists roles
        $db->createCommand()
            ->delete($authManager->assignmentTable, 'user_id=:user_id', [
                ':user_id' => $user->id,
            ])
            ->execute();

        // create new roles
        if (is_array($user->roles)) {
            foreach ($user->roles as $role) {
                $role = $authManager->getRole($role);
                $authManager->assign($role, $user->id);
            }
        }
    }

    /**
     * Update user and send notification
     *
     * @param UserForm $user
     * @return boolean true if successfully updated
     * @throws Exception
     */
    public function updateUser(UserForm $user)
    {
        if ($user->isNewRecord || !$user->validate()) {
            // only exists validated users record
            return false;
        }

        $newPassword = $user->newPassword;

        if ($user->generateRandomPassword) {
            // generate new random password
            $newPassword = $user->newPassword = Yii::$app->security->generateRandomString(10);
        }
        else if ($user->newPassword) {
            // new password typed at form
            $newPassword = $user->newPassword;
        }

        $transaction = $user->getDb()->beginTransaction();

        try {
            $user->save();
            $this->updateRoles($user);
            $transaction->commit();
        }
        catch (Exception $ex) {
            $transaction->rollBack();
            throw $ex;
        }

        if ($user->sendNotification && $newPassword) {
            // send e-mail
             Yii::$app->mailer->compose('userNewPassword', [
                'user' => $user,
                'newPassword' => $newPassword,
            ])
            ->setTo($user->email)
            ->setSubject(Yii::t('user', 'New password'))
            ->send();
        }

        return true;
    }

    /**
     * Activate user
     *
     * @param UserForm $user
     * @return boolean
     */
    public function activateUser(UserForm $user)
    {
        if ($user->isNewRecord) {
            return false;
        }

        $user->status = User::STATUS_ACTIVE;
        return $user->save(false, ['status']);
    }

    /**
     * Lock user
     *
     * @param UserForm $user
     * @return boolean
     */
    public function lockUser(UserForm $user)
    {
        if ($user->isNewRecord) {
            return false;
        }

        $user->status = User::STATUS_BLOCKED;
        return $user->save(false, ['status']);
    }

    /**
     * Create new user and send notification
     *
     * @param UserForm $user
     * @return boolean true if success
     * @throws Exception
     */
    public function createUser(UserForm $user)
    {
        if (!$user->isNewRecord || !$user->validate()) {
            // only new validated users record
            return false;
        }

        $transaction = $user->getDb()->beginTransaction();
        try {
            // generate new random password
            $user->newPassword = Yii::$app->security->generateRandomString(32);
            $user->status = User::STATUS_ACTIVE;
            $user->save();

            $this->updateRoles($user);

            if ($user->sendNotification) {
                // send user's notification
                $changePasswordLink = Url::toRoute(['/user/auth/change-password',
                    'hash' => $this->getUserChecker($user, 'email_checker'),
                ], true);
                Yii::$app->mailer->compose('userNewNotification', [
                    'user' => $user,
                    'link' => $changePasswordLink,
                ])
                ->setTo($user->email)
                ->setSubject(Yii::t('user', 'Account created'))
                ->send();
            }

            $transaction->commit();

            return true;
        } catch (Exception $ex) {
            $transaction->rollBack();
            throw $ex;
        }

        return false;
    }
}