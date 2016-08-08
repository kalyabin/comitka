<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Assets form RaphaelJs library
 */
class RaphaelJsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/DmitryBaranovskiy/raphael';
    public $css = [
    ];
    public $js = [
        'raphael-min.js',
    ];
    public $depends = [
    ];
}
