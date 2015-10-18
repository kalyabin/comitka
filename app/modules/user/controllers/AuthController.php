<?php
namespace user\controllers;

use app\components\Alert;
use user\models\ChangePasswordForm;
use user\models\ForgotPasswordForm;
use user\models\SignInForm;
use user\models\User;
use user\Module as UserModule;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Sign in, sign out, password recovery and other auth actions
 */
class AuthController extends Controller
{
    public $layout = '@app/views/layouts/one-column';

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
                        'actions' => ['sign-in', 'forgot-password', 'change-password'],
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
     * Change user's forgotten password.
     *
     * @param string $hash
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionChangePassword($hash)
    {
        /* @var $api UserModule */
        $api = Yii::$app->getModule('user');

        // find a user
        $user = $api->findUserByChecker('email_checker', $hash);

        if (!$user instanceof User) {
            // user not found
            throw new NotFoundHttpException();
        }

        $model = new ChangePasswordForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            // AJAX validation
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            /* @var $systemAlert Alert */
            $systemAlert = Yii::$app->systemAlert;
            if ($api->changeUserForgottenPassword($model, $user)) {
                $systemAlert->setMessage(Alert::INFO, Yii::t('user', 'Password successfully changed.'));
                return $this->goHome();
            }
            else {
                $systemAlert->setMessage(Alert::DANGER, Yii::t('user', 'Password change error'));
            }
        }

        return $this->render('change-password', [
            'model' => $model,
        ]);
    }

    /**
     * Forgot password
     *
     * @return mixed
     */
    public function actionForgotPassword()
    {
        $model = new ForgotPasswordForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            // AJAX validation
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            /* @var $api UserModule */
            $api = Yii::$app->getModule('user');
            /* @var $user User */
            $user = $model->getUser();
            /* @var $systemAlert Alert */
            $systemAlert = Yii::$app->systemAlert;

            // send forgot password e-mail
            if ($api->sendForgotPasswordEmail($user)) {
                $systemAlert->setMessage(Alert::INFO, Yii::t('user', 'E-mail to change password successfully sent.'));
                return $this->refresh();
            }
            else {
                $systemAlert->setMessage(Alert::DANGER, Yii::t('user', 'Error send an e-mail.'));
            }
        }

        return $this->render('forgot-password', [
            'model' => $model,
        ]);
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
            /* @var $api UserModule */
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