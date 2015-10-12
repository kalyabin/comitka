<?php

namespace user\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Model of user and identity class.
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property integer $status
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * User is an unactive
     */
    const STATUS_UNACTIVE = 0;

    /**
     * User is an active
     */
    const STATUS_ACTIVE = 1;

    /**
     * User is a blocked
     */
    const STATUS_BLOCKED = -1;

    /**
     * @var string create new password hash if new password sent
     */
    public $newPassword;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
            ['email', 'filter', 'filter' => 'strtolower'],
            ['email', 'unique'],
            ['status', 'integer'],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_UNACTIVE, self::STATUS_BLOCKED]],
            [['name', 'email'], 'string', 'max' => 100],
            ['password', 'string', 'max' => 255],
            ['password', 'required', 'when' => function($data) {
                /* @var $data User */
                return empty($data->newPassword);
            }]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('user', 'ID'),
            'name' => Yii::t('user', 'Name'),
            'email' => Yii::t('user', 'E-mail'),
            'password' => Yii::t('user', 'Password'),
            'status' => Yii::t('user', 'Status'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->newPassword) {
            /* @var $api \user\Module */
            $api = Yii::$app->getModule('user');
            $this->password = $api->getPasswordHash($this->newPassword);
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::find()->andWhere(['id' => (int) $id])->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * Finds user by email
     *
     * @param  string $username users email
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return self::find()->where(['email' => (string) $username])->one();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return md5(serialize([
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
        ]));
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() == $authKey;
    }
}
