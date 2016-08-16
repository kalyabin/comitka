<?php

namespace user\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_checker}}".
 * This model contains a checker hash, as e-mail checkers for password forgot etc.
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $email_checker
 *
 * @property User $user
 */
class UserChecker extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_checker}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['user_id'], 'unique'],
            [['email_checker'], 'string', 'max' => 32]
        ];
    }

    /**
     * Returns user's model
     *
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
