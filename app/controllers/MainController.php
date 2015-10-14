<?php
namespace app\controllers;

/**
 * Main system controller
 */
class MainController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge(parent::behaviors(), [
            'accessControl' => [
                'class' => \app\components\AuthControl::className(),
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