<?php

namespace user\models;

use app\models\ContributorInterface;
use user\UserModule;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\rbac\DbManager;
use yii\web\IdentityInterface;

/**
 * Model of user and identity class.
 *
 * @property integer $id User primary key
 * @property string $name Username
 * @property string $email User email
 * @property string $password Password hash
 * @property integer $status User status by self::STATUS_* constants
 * @property string $avatar Avatar relative filename
 * @property integer $default_reviewer_id Default user reviewer
 *
 * @property UserChecker $checker Checker model relation
 * @property UserAccount[] $accounts VCS user accounts
 */
class User extends ActiveRecord implements IdentityInterface, ContributorInterface
{
    /**
     * Max name length
     */
    const MAX_NAME_LENGTH = 100;

    /**
     * Max e-mail length
     */
    const MAX_EMAIL_LENGTH = 100;

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
     * Relative path with users avatars
     */
    const AVATAR_PATH = '@app/../htdocs/storage/';

    /**
     * Relative URI with users avatars
     */
    const AVATAR_URL = '/storage/';

    /**
     * Maximum avatar width
     */
    const AVATAR_MAX_WIDTH = 150;

    /**
     * Maximum avatar height
     */
    const AVATAR_MAX_HEIGHT = 150;

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
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['name', 'string', 'max' => self::MAX_NAME_LENGTH],
            ['email', 'string', 'max' => self::MAX_EMAIL_LENGTH],
            ['password', 'string', 'max' => 255],
            ['password', 'required', 'when' => function($data) {
                /* @var $data User */
                return empty($data->newPassword);
            }],
            [['default_reviewer_id'], 'integer'],
            [['default_reviewer_id'], 'default', 'value' => null],
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
            'default_reviewer_id' => Yii::t('user', 'Default user reviewer'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->newPassword) {
            /* @var $api UserModule */
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

    /**
     * Return's true if a user can sign in.
     *
     * @return boolean
     */
    public function canSignIn()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->email;
    }

    /**
     * Get contributor id
     *
     * @return string
     */
    public function getContributorId()
    {
        return $this->id;
    }

    /**
     * Get contributor name
     *
     * @return string
     */
    public function getContributorName()
    {
        return $this->name;
    }

    /**
     * Get contributor e-mail
     *
     * @return string
     */
    public function getContributorEmail()
    {
        return $this->email;
    }

    /**
     * Return's ActiveQuery to find user's checker.
     * If model not exists - create it.
     *
     * @return ActiveQuery
     */
    public function getChecker()
    {
        if (!$this->hasOne(UserChecker::className(), ['user_id' => 'id'])->exists()) {
            // create new model if not exists
            $newModel = new UserChecker();
            $newModel->user_id = $this->id;
            $newModel->save();
        }
        return $this->hasOne(UserChecker::className(), ['user_id' => 'id']);
    }

    /**
     * Retrieve default reviewer model
     *
     * @return User
     */
    public function getDefaultReviewer()
    {
        return $this->hasOne(static::className(), ['default_reviewer_id' => 'id']);
    }

    /**
     * Get user's VCS accounts
     *
     * @return ActiveQuery
     */
    public function getAccounts()
    {
        return $this->hasMany(UserAccount::className(), ['user_id' => 'id']);
    }

    /**
     * Returns statuses list
     *
     * @return array
     */
    public function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => \Yii::t('user', 'Active'),
            self::STATUS_UNACTIVE => \Yii::t('user', 'Unactive'),
            self::STATUS_BLOCKED => \Yii::t('user', 'Blocked'),
        ];
    }

    /**
     * Returns status name
     *
     * @return string
     */
    public function getStatusName()
    {
        $statuses = $this->getStatuses();
        return isset($statuses[$this->status]) ? $statuses[$this->status] : null;
    }

    /**
     * Get avatar relative URI
     *
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatar ? self::AVATAR_URL . $this->avatar : null;
    }

    /**
     * Get default reviewer id
     *
     * @return string
     */
    public function getDefaultViewerId()
    {
        return $this->default_reviewer_id;
    }

    /**
     * Get user roles
     *
     * @return string[]
     */
    public function getUserRoles()
    {
        /* @var $authManager DbManager */
        $authManager = Yii::$app->authManager;

        return array_keys($authManager->getRolesByUser($this->id));
    }
}
