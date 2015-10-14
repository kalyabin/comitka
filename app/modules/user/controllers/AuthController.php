<?php
namespace user\controllers;

use app\components\Alert;
use user\models\SignInForm;
use user\models\User;
use user\Module;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Sign in, sign out, password recovery and other auth actions
 */
class AuthController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'accessControl' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['sign-in'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['sign-out'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ]);
    }

    /**
     * Sign out
     */
    public function actionSignOut()
    {
        Yii::$app->user->logout();
        return $this->goBack();
    }

    /**
     * Sign in
     *
     * @return mixed
     */
    public function actionSignIn()
    {
        $model = new SignInForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            // AJAX validation
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            /* @var $api Module */
            $api = Yii::$app->getModule('user');
            $user = $model->getUser();
            if ($user instanceof User && $api->signInUser($user, $model->password)) {
                return $this->goHome();
            }

            // account was blocked
            /* @var $systemAlert Alert */
            $systemAlert = Yii::$app->systemAlert;
            $systemAlert->setMessage(Alert::DANGER, Yii::t('user', 'An account is blocked'));
            return $this->refresh();
        }

        return $this->render('sign-in', [
            'model' => $model,
        ]);
    }
}