<?php

use Codeception\Test\Unit;
use yii\helpers\ArrayHelper;

/**
 * Basic test case for each unit test
 */
abstract class UnitTestCase extends Unit
{
    /**
     * @var string Application class
     */
    protected $appClass = 'yii\console\Application';

    /**
     * @var array Application config wich rewrite global unit config
     */
    protected $appConfig = [];

    /**
     * Create application instance
     */
    protected function setUp()
    {
        $config = ArrayHelper::merge([
            'class' => $this->appClass,
        ], require(dirname(__DIR__) . '/config/unit.php'), $this->appConfig);
        Yii::createObject($config);

        return parent::setUp();
    }

    /**
     * Destroy application instance
     */
    protected function tearDown()
    {
        Yii::$app = null;

        return parent::tearDown();
    }
}
