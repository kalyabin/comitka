<?php
namespace user\controllers;

use app\components\Alert;
use user\models\ChangePasswordForm;
use user\models\ForgotPasswordForm;
use user\models\SignInForm;
use user\models\User;
use user\Module as UserModule;
use Yii;
use yii\base\Model;
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
     * @var UserModule
     */
    protected $userModule;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->userModule = Yii::$app->getModule('user');
    }

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
        // find a user
        $user = $this->userModule->findUserByChecker('email_checker', $hash);

        if (!$user instanceof User) {
            // user not found
            throw new NotFoundHttpException();
        }

        $model = new ChangePasswordForm();

        $ret = $this->performAjaxValidation($model);
        if (is_array($ret)) {
            // AJAX validation
            return $ret;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            /* @var $systemAlert Alert */
            $systemAlert = Yii::$app->systemAlert;
            if ($this->userModule->changeUserForgottenPassword($model, $user)) {
                if ($user->canSignIn()) {
                    // authorize user
                    $this->userModule->signInUser($user, $model->password);
                }
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

        $ret = $this->performAjaxValidation($model);
        if (is_array($ret)) {
            // AJAX validation
            return $ret;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            /* @var $user User */
            $user = $model->getUser();
            /* @var $systemAlert Alert */
            $systemAlert = Yii::$app->systemAlert;

            // send forgot password e-mail
            if ($this->userModule->sendForgotPasswordEmail($user)) {
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

        $ret = $this->performAjaxValidation($model);
        if (is_array($ret)) {
            // AJAX validation
            return $ret;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user = $model->getUser();
            if ($user instanceof User && $this->userModule->signInUser($user, $model->password)) {
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

    /**
     * Performs model ajax validation
     *
     * @param Model $model
     * @return array|null
     */
    protected function performAjaxValidation(Model $model)
    {
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            // AJAX validation
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return null;
    }
}