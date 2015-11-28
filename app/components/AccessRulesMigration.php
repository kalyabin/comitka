<?php

namespace app\components;

use Yii;
use yii\db\Migration;
use yii\rbac\DbManager;
use yii\rbac\Item;

/**
 * Migration class to create RBAC items.
 * Application must composed with authManager component, extended by \yii\rbac\DbManager.
 */
abstract class AccessRulesMigration extends Migration
{
    /**
     * Returns access rules like this:
     *
     * '<rule_id>' => '<rule_name>'
     *
     * @return array
     */
    protected abstract function getPermissions();

    /**
     * Migration up
     */
    public function safeUp()
    {
        /* @var $authManager DbManager */
        $authManager = Yii::$app->authManager;

        /* @var $backendRole Item */
        $adminRole = $authManager->getRole('admin');

        foreach ($this->getPermissions() as $permission => $description) {
            $child = $authManager->createPermission($permission);
            $child->description = $description;
            $authManager->add($child);
            $authManager->addChild($adminRole, $child);
        }
    }

    /**
     * Migration down
     */
    public function safeDown()
    {
        /* @var $authManager DbManager */
        $authManager = Yii::$app->authManager;

        foreach ($this->getPermissions() as $permission => $name) {
            $child = $authManager->getPermission($permission);
            $authManager->remove($child);
        }
    }
}
