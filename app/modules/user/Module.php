<?php
namespace user;

use Exception;
use user\components\Auth;
use user\models\ChangePasswordForm;
use user\models\ProfileForm;
use user\models\User;
use user\models\UserChecker;
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
     * Send forgot password e-mail.
     * Generates new e-mail checker hash if not exists.
     *
     * @param User $user
     * @return boolean
     * @throws Exception
     */
    public function sendForgotPasswordEmail(User $user)
    {
        $checker = $user->checker;
        if (!trim($checker->email_checker)) {
            // generate new e-mail checker hash
            try {
                $checker->email_checker = md5($user->id . $user->email . time());
                if (!$checker->save(false, ['email_checker'])) {
                    throw new Exception();
                }
            }
            catch (Exception $ex) {
                return false;
            }
        }

        // send e-mail
        $changePasswordLink = Url::toRoute(['/user/auth/change-password',
            'hash' => $checker->email_checker
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
}