<?php
namespace app\controllers;

use app\components\AuthControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Main system controller
 */
class MainController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'accessControl' => [
                'class' => AuthControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Application index
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
