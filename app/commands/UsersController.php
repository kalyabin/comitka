<?php
namespace app\commands;

use Exception;
use user\models\User;
use user\UserModule;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Manage systems users. Create new administrators, etc.
 */
class UsersController extends Controller
{
    /**
     * @var string Users name
     */
    public $name;

    /**
     * @var string Users e-mail (using as login)
     */
    public $email;

    /**
     * @var array static options list by action id
     */
    protected static $_options = [
        'admin-create' => [
            'name', 'email',
        ],
    ];

    /**
     * Get string options list of current action
     *
     * @param string $actionID
     * @return array
     */
    public function options($actionID)
    {
        return isset(self::$_options[$actionID]) ? self::$_options[$actionID] : [];
    }

    /**
     * Creates a new administrative user's account.
     */
    public function actionAdminCreate()
    {
        $password = $this->prompt('Enter password:', [
            'required' => true
        ]);
        if (!$password) {
            return self::EXIT_CODE_ERROR;
        }

        // create user model
        $user = new User();
        $user->setAttributes([
            'name' => $this->name,
            'email' => $this->email,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->newPassword = $password;
        $this->stdout("\n");

        if (!$user->validate()) {
            // validation errors
            $this->stdout('Validation errors...' . "\n", Console::FG_YELLOW);
            foreach ($user->getErrors() as $attribute => $errors) {
                $error = reset($errors);
                $this->stdout($user->getAttributeLabel($attribute) . ': ', Console::FG_RED);
                $this->stdout($error . "\n");
            }
            return self::EXIT_CODE_ERROR;
        }

        /* @var $api UserModule */
        $api = Yii::$app->getModule('user');

        try {
            $api->createAdmin($user);
            $this->stdout('User successfully created. ID: ' . $user->id . "\n", Console::FG_GREEN);
        }
        catch (Exception $ex) {
            // Database error
            $this->stdout('Database error.' . "\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
    }
}