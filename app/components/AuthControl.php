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
        if (!$this->denyMessage && !Yii::$app->user->isGuest) {
            $this->denyMessage = Yii::t('user', 'You have no rights to perform this action');
        }
        else if (!$this->denyMessage && Yii::$app->user->isGuest) {
            $this->denyMessage = Yii::t('user', 'Need authorization to perform this action');
        }
        $this->denyCallback = function($rule, $action) {
            if ($this->denyMessage) {
                /* @var $systemAlert Alert */
                $systemAlert = Yii::$app->systemAlert;
                $systemAlert->setMessage(Alert::DANGER, $this->denyMessage);
            }
            if (Yii::$app->user->isGuest) {
                Yii::$app->user->loginRequired();
            }
            else {
                return false;
            }
        };
    }
}