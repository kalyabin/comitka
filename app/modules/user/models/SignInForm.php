<?php
namespace user\models;

use user\UserModule;
use Yii;
use yii\base\Model;

/**
 * A model of authorization forms
 */
class SignInForm extends Model
{
    /**
     * @var string user's email
     */
    public $email;

    /**
     * @var string user's password
     */
    public $password;

    /**
     * @var User a found user's model
     */
    protected $user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            [['email', 'password'], 'string'],
            ['email', 'filter', 'filter' => 'strtolower'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('user', 'E-mail'),
            'password' => Yii::t('user', 'Password'),
        ];
    }

    /**
     * Validates user's password
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatePassword($attribute, $params)
    {
        $this->user = null;

        if (!$this->getErrors()) {
            $this->user = User::findByUsername($this->email);
            if (!($this->user instanceof User)) {
                // user not found
                $this->addError($attribute, Yii::t('user', 'Wrong user name or password'));
                return;
            }
            /* @var $api UserModule */
            $api = Yii::$app->getModule('user');
            if (!$api->checkUserPassword($this->user, $this->{$attribute})) {
                $this->addError($attribute, Yii::t('user', 'Wrong user name or password'));
                return;
            }

            // switch user status
            if (!$this->user->canSignIn()) {
                switch ($this->user->status) {
                    case User::STATUS_BLOCKED:
                        $this->addError($attribute, Yii::t('user', 'An account is blocked'));
                        break;
                    default:
                        $this->addError($attribute, Yii::t('user', 'An account is unactive'));
                        break;
                }
            }
        }
    }

    /**
     * Returns a found user's model
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}