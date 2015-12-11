<?php
namespace user\models;

use Yii;
use yii\base\Model;

/**
 * User's profile form.
 *
 * Change name.
 * TODO: change implements avatars and change avatars, change e-mail (?).
 */
class ProfileForm extends Model
{
    /**
     * @var string user's e-mail
     */
    public $email;

    /**
     * @var string user's name
     */
    public $name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => User::MAX_EMAIL_LENGTH],
            ['email', 'filter', 'filter' => 'strtolower'],
            ['name', 'required'],
            ['name', 'string', 'max' => User::MAX_NAME_LENGTH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('user', 'Your name'),
            'email' => Yii::t('user', 'Your e-mail'),
        ];
    }

    /**
     * Creates form from exists user's model.
     *
     * @param User $user
     * @return static
     */
    public static function createFromExistsUser(User $user)
    {
        $model = new static();
        $model->setAttributes([
            'email' => $user->email,
            'name' => $user->name,
        ]);
        return $model;
    }
}
