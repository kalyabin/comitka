<?php
$config = require(dirname(__DIR__) . '/config/unit.php');
Yii::$container = new \yii\di\Container();
Yii::createObject($config);
