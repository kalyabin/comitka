<?php

use Codeception\Test\Unit;
use yii\helpers\ArrayHelper;

/**
 * Basic unit test case for test web enviroment
 */
abstract class UnitWebTestCase extends Unit
{
    /**
     * @var string Application class
     */
    protected $appClass = 'yii\web\Application';

    /**
     * @var array Application config wich rewrite global unit config
     */
    protected $appConfig = [
        'request' => [
            'cookieValidationKey' => 'zAcJxMF2k1vQFD9',
            'scriptFile' => __DIR__ .'/index.php',
            'scriptUrl' => '/index.php',
        ],
        'assetManager' => [
            'class' => 'tests\AssetManager',
            'basePath' => '@tests/codeception/_output/assets',
            'baseUrl' => '/',
        ]
    ];

    /**
     * Create application instance
     */
    protected function setUp()
    {
        $assetsPath = Yii::getAlias('@tests/codeception/_output/assets');

        // create assets path
        if (!is_dir($assetsPath)) {
            mkdir($assetsPath, 0755);
        }

        return parent::setUp();
    }
}
