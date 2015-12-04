<?php

use yii\db\Schema;
use yii\db\Migration;

use app\components\AccessRulesMigration;

/**
 * Project access rules migration
 */
class m151204_194007_project__access_rules extends AccessRulesMigration
{
    protected function getPermissions()
    {
        return [
            'createProject' => 'Create project',
            'updateProject' => 'Update project',
            'deleteProject' => 'Delete project',
        ];
    }
}
