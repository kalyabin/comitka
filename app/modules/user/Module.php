<?php
namespace user;

use Exception;
use user\components\Auth;
use user\models\User;
use Yii;
use yii\base\Module as BaseModule;
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
}