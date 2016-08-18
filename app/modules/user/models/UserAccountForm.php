<?php
namespace user\models;

use yii\helpers\ArrayHelper;

/**
 * Form to update user VCS account
 */
class UserAccountForm extends UserAccount
{
    /**
     * @var boolean Deletion flag
     */
    public $deletionFlag;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['deletionFlag', 'boolean'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // from form can set only username and vcs type
        return [
            self::SCENARIO_DEFAULT => ['username', 'type', 'deletionFlag'],
        ];
    }
}
