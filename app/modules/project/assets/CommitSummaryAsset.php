<?php
namespace project\assets;

use yii\helpers\Json;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Asset bundle for commit summary page
 * Include this asset to history controller.
 */
class CommitSummaryAsset extends AssetBundle
{
    public $sourcePath = '@project/assets/static';
    public $css = [];
    public $js = [
        'commit-summary.js',
    ];
    public $depends = [
        'app\assets\CommonAsset',
        'app\assets\CodeMirrorAsset',
    ];

    /**
     * Register bundle for view.
     * Send second param as array to set plugin options.
     *
     * @param View $view
     * @param array $jsOptions
     */
    public static function register($view)
    {
        $ret = parent::register($view);
        $jsOptions = func_get_arg(1);
        if (is_array($jsOptions)) {
            $view->registerJs('$(document).commitSummary(' . Json::encode($jsOptions) . ');');
        }
        return $ret;
    }
}
