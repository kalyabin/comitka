<?php
namespace user\models;

use app\helpers\ImageResizeHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;
use yii\rbac\Role;
use yii\web\UploadedFile;

/**
 * Create or update user form
 */
class UserForm extends User
{
    /**
     * @var boolean Delete avatar flag
     */
    public $deleteAvatar;

    /**
     * @var UploadedFile Uploaded avatar file
     */
    public $uploadedAvatar;

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
            ['sendNotification', 'boolean', 'except' => ['profile']],
            ['roles', 'required', 'on' => ['update', 'create'], 'except' => ['profile']],
            ['roles', 'each', 'rule' => [
                'in', 'range' => array_keys($this->getRolesList()), 'skipOnEmpty' => false
            ], 'except' => ['profile']],
            ['newPassword', 'string', 'max' => 255, 'on' => 'update', 'except' => ['profile']],
            ['newPasswordConfirmation', 'string', 'max' => 255, 'on' => 'update', 'except' => ['profile']],
            ['newPasswordConfirmation', 'compare',
                'operator' => '==', 'compareAttribute' => 'newPassword', 'on' => 'update', 'except' => ['profile']],
            ['generateRandomPassword', 'boolean', 'on' => 'update', 'except' => ['profile']],

            ['name', 'required'],
            ['email', 'required', 'except' => ['profile']],
            ['name', 'string', 'max' => self::MAX_NAME_LENGTH],
            ['email', 'string', 'max' => self::MAX_EMAIL_LENGTH, 'except' => ['profile']],
            ['email', 'email', 'except' => ['profile']],
            ['email', 'unique', 'except' => ['profile']],

            ['roles', 'each', 'rule' => ['string', 'skipOnEmpty' => false], 'except' => ['profile']],

            ['deleteAvatar', 'boolean'],
            ['uploadedAvatar', 'image'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return ArrayHelper::merge(parent::scenarios(), [
            'profile' => ['name', 'deleteAvatar', 'uploadedAvatar'],
        ]);
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
            'uploadedAvatar' => Yii::t('user', 'Avatar'),
            'deleteAvatar' => Yii::t('user', 'Delete avatar'),
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
            /* @var $role Role */
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

    /**
     * Upload and resize avatar
     *
     * @return string path to resized avatar file
     */
    protected function uploadAvatar()
    {
        $newFileName = Yii::getAlias(self::AVATAR_PATH . md5($this->uploadedAvatar->tempName . time()) . '.' . $this->uploadedAvatar->extension);
        $oldFileName = $this->uploadedAvatar->tempName;

        $image = ImageResizeHelper::cropImage($oldFileName, self::AVATAR_MAX_WIDTH, self::AVATAR_MAX_HEIGHT);
        $image->save($newFileName);

        unlink($this->uploadedAvatar->tempName);

        return basename($newFileName);
    }

    /**
     * Upload or remove avatar
     *
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $oldAvatar = $this->avatar ?
            Yii::getAlias(self::AVATAR_PATH . $this->avatar) :
            '';

        if (($this->uploadedAvatar instanceof UploadedFile || $this->deleteAvatar) && $oldAvatar && is_file($oldAvatar)) {
            // remove old avatar
            unlink($oldAvatar);
            $this->avatar = null;
        }

        // upload new avatar
        if ($this->uploadedAvatar instanceof UploadedFile && $this->validate(['uploadedAvatar'])) {
            $this->avatar = $this->uploadAvatar();
        }

        return parent::beforeSave($insert);
    }
}
