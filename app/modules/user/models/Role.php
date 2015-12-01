<?php
namespace user\models;

use Yii;
use yii\base\Model;
use yii\rbac\DbManager;
use yii\rbac\Permission;
use yii\rbac\Role as RoleModel;

/**
 * Role form model
 */
class Role extends Model
{
    /**
     * Maximum name length
     */
    const MAX_NAME = 30;

    /**
     * Maximum description length
     */
    const MAX_DESCRIPTION = 50;

    /**
     * @var string role identifier
     */
    protected $_name;

    /**
     * @var boolean true, if role is new
     */
    protected $_isNewRole = true;

    /**
     * @var string role clear name
     */
    public $description;

    /**
     * @var array role permissions
     */
    public $permissions = [];

    /**
     * @var Permission[] all permissions list
     */
    protected $_allPermissions = [];

    /**
     * @inheritdoc
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        /* @var $authManager DbManager */
        $authManager = Yii::$app->authManager;
        // fil allPermissions array
        $res = $authManager->getPermissions();
        foreach ($res as $permission) {
            /* @var $permission Permission */
            $this->_allPermissions[$permission->name] = $permission;
        }
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'description', 'permissions'], 'required'],
            ['name', 'string', 'max' => self::MAX_NAME],
            ['description', 'string', 'max' => self::MAX_DESCRIPTION],
            [['name', 'description'], 'filter', 'filter' => 'trim', 'on' => 'create'],
            ['name', 'validateExistent', 'on' => 'create'],
            ['name', 'match', 'pattern' => '#^[a-z0-9]+$#i', 'on' => 'create'],
            ['permissions', 'each', 'rule' => ['in', 'range' => array_keys($this->_allPermissions)]],
        ];
    }

    /**
     * Role name (or identifier) is unique field.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateExistent($attribute, $params)
    {
        $name = trim($this->{$attribute});

        if (!empty($name)) {
            // get existent role
            /* @var $authManager DbManager */
            $authManager = Yii::$app->authManager;

            $role = $authManager->getRole($name);

            if (!empty($role)) {
                $this->addError($attribute, Yii::t('user', 'Role already exists'));
            }
        }
    }

    /**
     * @return array of attribute lables
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('user', 'Code'),
            'description' => Yii::t('user', 'Name'),
            'permissions' => Yii::t('user', 'Access permissions'),
        ];
    }

    public function attributeHints()
    {
        return [
            'name' => Yii::t('user', 'Type string code contains A-Z, 0-9, a-z symbols.'),
            'description' => Yii::t('user', 'Type short description of role.'),
            'permissions' => Yii::t('user', 'Check role access rules.'),
        ];
    }

    /**
     * Create form model from RBAC model.
     *
     * @param RoleModel $role
     * @param Permission[] $permissions
     * @return static
     */
    public static function createFromRole(RoleModel $role, array $permissions)
    {
        $form = new static();
        $form->_name = $role->name;
        $form->_isNewRole = false;
        $form->description = $role->description ? $role->description : $role->name;
        // set permissions array
        $form->permissions = [];
        foreach ($permissions as $permission) {
            if ($permission instanceof Permission) {
                $form->permissions[] = $permission->name;
            }
        }

        return $form;
    }

    /**
     * @return string name field
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set new name only if new model
     *
     * @param string $name
     */
    public function setName($name)
    {
        if ($this->_isNewRole) {
            $this->_name = $name;
        }
    }

    /**
     * @return boolean true if new model
     */
    public function getIsNewRecord()
    {
        return $this->_isNewRole === true;
    }

    /**
     * Get all checked permissions by models
     *
     * @return Permission[]
     */
    public function getPermissionModels()
    {
        $ret = [];

        foreach ($this->permissions as $permissionName) {
            if (is_string($permissionName) && isset($this->_allPermissions[$permissionName])) {
                $ret[] = $this->_allPermissions[$permissionName];
            }
        }

        return $ret;
    }

    /**
     * @return Permission[] all permissions from DB
     */
    public function getAllPermissions()
    {
        return $this->_allPermissions;
    }
}