<?php
namespace user\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;

/**
 * Create or update user form
 */
class UserForm extends User
{
    /**
     * @var boolean true to send user notification on email
     */
    public $sendNotification;

    /**
     * @var string a new password for exists user
     */
    public $newPassword;

    /**
     * @var string a confirmation of new password for exists user
     */
    public $newPasswordConfirmation;

    /**
     * @var boolean true if need to generate random password for exists user
     */
    public $generateRandomPassword;

    /**
     * @var string[] user roles
     */
    public $roles = [];

    /**
     * Validation rules
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['sendNotification', 'boolean'],
            ['roles', 'each', 'rule' => [
                'in', 'range' => array_keys($this->getRolesList()),
            ]],
            ['newPassword', 'string', 'max' => 255, 'on' => 'update'],
            ['newPasswordConfirmation', 'string', 'max' => 255, 'on' => 'update'],
            ['newPasswordConfirmation', 'compare',
                'operator' => '==', 'compareAttribute' => 'newPassword', 'on' => 'update'],
            ['generateRandomPassword', 'boolean', 'on' => 'update'],

            [['name', 'email'], 'required'],
            ['name', 'string', 'max' => self::MAX_NAME_LENGTH],
            ['email', 'string', 'max' => self::MAX_EMAIL_LENGTH],
            ['email', 'email'],
            ['email', 'unique'],

            ['roles', 'each', 'rule' => ['string', 'skipOnEmpty' => false]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'sendNotification' => Yii::t('user', 'Send notification'),
            'newPassword' => Yii::t('user', 'New password'),
            'newPasswordConfirmation' => Yii::t('user', 'New password confirmation'),
            'generateRandomPassword' => Yii::t('user', 'Generate new password automatically'),
            'roles' => Yii::t('user', 'Users roles'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return ArrayHelper::merge(parent::attributes(), [
            'sendNotification', 'newPassword', 'newPasswordConfirmation', 'generateRandomPassword',
            'roles',
        ]);
    }

    /**
     * @return array available roles list
     */
    public function getRolesList()
    {
        /* @var $authManager DbManager */
        $authManager = Yii::$app->authManager;
        $roles = [];
        foreach ($authManager->getRoles() as $role) {
            /* @var $role \yii\rbac\Role */
            $roles[$role->name] = $role->description ? $role->description : $role->name;
        }
        return $roles;
    }

    /**
     * After find get roles list for existent user
     */
    public function afterFind()
    {
        /* @var $authManager DbManager */
        $authManager = Yii::$app->authManager;
        $this->roles = array_keys($authManager->getRolesByUser($this->id));
    }
}
