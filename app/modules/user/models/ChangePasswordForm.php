<?php
namespace user\models;

use Yii;
use yii\base\Model;

/**
 * Change forgotten password
 */
class ChangePasswordForm extends Model
{
    /**
     * @var string new password
     */
    public $password;

    /**
     * @var string password confirmation
     */
    public $confirmPassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'confirmPassword'], 'required'],
            [['password', 'confirmPassword'], 'string'],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password', 'operator' => '==='],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password' => Yii::t('user', 'New password'),
            'confirmPassword' => Yii::t('user', 'Password confirmation'),
        ];
    }
}