<?php

use yii\db\Migration;

use yii\base\InvalidConfigException;
use yii\rbac\DbManager;

/**
 * Create admin's role. Admin can do everything.
 */
class m151012_201309_user__admin_role extends Migration
{
    /**
     * @throws yii\base\InvalidConfigException
     * @return DbManager
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }
        return $authManager;
    }

    public function safeUp()
    {
        /* @var $authManager DbManager */
        $authManager = $this->getAuthManager();
        /* @var $role Role */
        $role = $authManager->createRole('admin');
        $authManager->add($role);
    }

    public function safeDown()
    {
        /* @var $authManager DbManager */
        $authManager = $this->getAuthManager();
        /* @var $role \yii\rbac\Item */
        $role = $authManager->getRole('admin');
        if ($role instanceof \yii\rbac\Item) {
            $authManager->remove($role);
        }
    }
}
