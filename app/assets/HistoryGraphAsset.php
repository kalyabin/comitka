<?php
namespace app\assets;

use yii\helpers\Json;
use yii\web\AssetBundle;
use yii\web\JsExpression;
use yii\web\View;

/**
 * Graphic history rendered
 */
class HistoryGraphAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/static/js';
    public $css = [
    ];
    public $js = [
        'history-graph.js',
    ];
    public $depends = [
        'app\assets\RaphaelJsAsset',
        'app\assets\CommonAsset',
    ];

    /**
     * @inheritdoc
     */
    public static function register($view)
    {
        /* @var $view View */
        parent::register($view);
        // register graphic options
        $options = func_get_arg(1);
        $view->registerJs(new JsExpression('document.historyGraph = new HistoryGraph(' . Json::encode($options) . ');'));
    }
}
