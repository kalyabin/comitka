<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * User VCS accounts assets
 */
class VCSBindingsAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/static/js';
    public $css = [
    ];
    public $js = [
        'vcs-bindings.js',
    ];
    public $depends = [
        'app\assets\CommonAsset',
    ];
}
