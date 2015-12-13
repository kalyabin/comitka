<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Assets form RaphaelJs library
 */
class RaphaelJsAsset extends AssetBundle
{
    public $sourcePath = '@app/static/plugins/raphael';
    public $css = [
    ];
    public $js = [
        'raphael-min.js',
    ];
    public $depends = [
    ];
}
