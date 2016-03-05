<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Highlight.js JavaScript plugin.
 *
 * @see https://github.com/isagalaev/highlight.js
 */
class HighlightJsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/isagalaev/highlight.js/src';
    public $css = [
        'styles/default.css',
    ];
    public $js = [
        'highlight.js',
    ];
}
