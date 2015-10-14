<?php
namespace app\components;

use Yii;
use yii\filters\AccessControl;

/**
 * Redeclare basic AccessControl filter.
 * If user deny access, returns user to login page with corresponding system message.
 */
class AuthControl extends AccessControl
{
    /**
     * @var string message for user redirect
     */
    public $denyMessage;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->denyCallback = function($rule, $action) {
            if ($this->denyMessage) {
                /* @var $systemAlert Alert */
                $systemAlert = Yii::$app->systemAlert;
                $systemAlert->setMessage(Alert::DANGER, $this->denyMessage);
            }
            Yii::$app->user->loginRequired();
        };
    }
}