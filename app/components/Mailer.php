<?php
namespace app\components;

use Yii;
use yii\swiftmailer\Mailer as BaseMailer;

/**
 * Wrapper for default mailer class
 */
class Mailer extends BaseMailer
{
    /**
     * @inheritdoc
     */
    public function compose($view = null, array $params = array())
    {
        if (isset(Yii::$app->params['local']['emailFrom'])) {
            return parent::compose($view, $params)
                ->setFrom(Yii::$app->params['local']['emailFrom']);
        }

        return parent::compose();
    }
}
