<?php
namespace user;

use Yii;
use yii\base\Module as BaseModule;

/**
 * Provides API to manage users
 */
class Module extends BaseModule
{
    /**
     * Generate hash using original password
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return Yii::$app->security->generatePasswordHash($password);
    }
}