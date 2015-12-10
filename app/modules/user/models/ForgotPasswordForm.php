<?php
namespace user\models;

/**
 * Type user's e-mail for to send mail to change password.
 */
class ForgotPasswordForm extends \yii\base\Model
{
    /**
     * @var string user's e-mail
     */
    public $email;

    /**
     * @var User found user model
     */
    protected $user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'validateUser'],
        ];
    }

    /**
     * Validate existence of user and save it's model.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateUser($attribute, $params = [])
    {
        if (!$this->hasErrors()) {
            $res = User::findByUsername($this->{$attribute});
            if (!($res instanceof User)) {
                $this->addError($attribute, \Yii::t('user', 'User not found'));
            }
            else {
                $this->user = $res;
            }
        }
    }

    /**
     * Get user's model
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
