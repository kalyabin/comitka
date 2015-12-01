<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Access rules to roles manager
 */
class m151201_200051_user__roles_admin extends \app\components\AccessRulesMigration
{
    /**
     * @inheritdoc
     */
    protected function getPermissions()
    {
        return [
            'manageRole' => 'Manage roles',
        ];
    }
}
