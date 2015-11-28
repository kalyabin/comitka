<?php

use app\components\AccessRulesMigration;

/**
 * Manage users access rules
 */
class m151128_083531_user__user_creation_access_rules extends AccessRulesMigration
{
    protected function getPermissions()
    {
        return [
            'createUser' => 'Create users',
            'updateUser' => 'Update users',
            'deleteUser' => 'Delete users',
        ];
    }
}
