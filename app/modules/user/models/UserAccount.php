<?php

namespace user\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Model of user account (username) at version control system eg GIT, HG, SVN
 *
 * @property integer $id Username primary key
 * @property integer $user_id Relation to user
 * @property string $username Username at version control system
 * @property string $type Version control system name: GIT, HG, SVN
 *
 * @property User $user User's model
 */
class UserAccount extends ActiveRecord
{
    /**
     * Account type - git
     */
    const TYPE_GIT = 'git';

    /**
     * Account type - hg
     */
    const TYPE_HG = 'hg';

    /**
     * Account type - svn
     */
    const TYPE_SVN = 'svn';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'username', 'type'], 'required'],
            [['user_id'], 'integer'],
            [['type'], 'in', 'range' => [self::TYPE_GIT, self::TYPE_HG, self::TYPE_SVN], 'skipOnEmpty' => false],
            [['username'], 'string', 'max' => 100],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['username', 'type'], 'unique', 'targetAttribute' => ['username', 'type']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => Yii::t('user', 'User'),
            'username' => Yii::t('user', 'VCS username'),
            'type' => Yii::t('user', 'Type'),
        ];
    }

    /**
     * Get user's model
     *
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Get account types for drop down select
     *
     * @return string[]
     */
    public static function getTypesForDropDown()
    {
        return [
            self::TYPE_GIT => Yii::t('user', 'Git'),
            self::TYPE_HG => Yii::t('user', 'HG'),
            self::TYPE_SVN => Yii::t('user', 'SVN'),
        ];
    }
}
