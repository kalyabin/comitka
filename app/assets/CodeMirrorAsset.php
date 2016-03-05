<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for CodeMirror JavaScript plugin.
 *
 * @see https://github.com/codemirror/CodeMirror
 */
class CodeMirrorAsset extends AssetBundle
{
    public $sourcePath = '@vendor/codemirror/codemirror/lib';
    public $css = [
        'codemirror.css',
    ];
    public $js = [
        'codemirror.js',
    ];
}
