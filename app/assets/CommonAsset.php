<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Application common assets
 */
class CommonAsset extends AssetBundle
{
    public $sourcePath = '@app/static';
    public $css = [
        'css/bootstrap-theme.min.css',
        'css/theme.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
