<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;

/**
 * Global alert.
 * Sets alert as flash to session.
 * Several alert's types:
 * - info;
 * - success;
 * - danger.
 */
class Alert extends Component
{
    const DANGER = 'danger';
    const SUCCESS = 'success';
    const INFO = 'info';

    /**
     * Append system message.
     * To set type see class constants:
     * - DANGER;
     * - SUCCESS;
     * - INFO;
     * - etc.
     *
     * @param string $type alert type: danger, success, info, etc.
     * @param string $message text message or html.
     */
    public function setMessage($type, $message)
    {
        Yii::$app->setFlash('system-alert', [
            'type' => $type,
            'message' => $message,
        ]);
    }

    /**
     * Renders system message at frontend.
     *
     * @return string
     */
    public function viewMessage()
    {
        $alert = Yii::$app->getFlash('system-alert');
        $type = ArrayHelper::getValue($alert, 'type', self::INFO);
        $message = ArrayHelper::getValue($alert, 'message', '');
        if (!empty($message)) {
            return Html::tag('div', $message, ['class' => 'alert alert-' . $type]);
        }
        return '';
    }
}