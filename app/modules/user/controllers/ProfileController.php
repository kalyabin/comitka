<?php
namespace user\controllers;

use app\components\Alert;
use user\controllers\actions\VcsBindingsAction;
use user\models\ChangePasswordForm;
use user\models\UserForm;
use user\UserModule;
use Yii;
use yii\base\Model;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * User's profile controller
 */
class ProfileController extends Controller
{
    /**
     * @var UserModule
     */
    protected $userModule;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->userModule = Yii::$app->getModule('user');
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'vcs-bindings' => [
                'class' => VcsBindingsAction::className(),
                'model' => Yii::$app->user->getIdentity(),
            ]
        ];
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
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ]);
    }

    /**
     * Change profile wrapper
     *
     * @param UserForm $model
     * @return mixed
     */
    protected function changeProfile(UserForm $model)
    {
        /* @var $systemAlert Alert */
        $systemAlert = Yii::$app->systemAlert;
        if ($this->userModule->updateUser($model)) {
            $systemAlert->setMessage(Alert::INFO, Yii::t('user', 'Profile successfully changed'));
        }
        else {
            $systemAlert->setMessage(Alert::DANGER, Yii::t('user', 'Change profile error'));
        }
        return $this->refresh();
    }

    /**
     * Change password wrapper
     *
     * @param ChangePasswordForm $model
     * @return mixed
     */
    protected function changePassword(ChangePasswordForm $model)
    {
        /* @var $systemAlert Alert */
        $systemAlert = Yii::$app->systemAlert;
        if ($this->userModule->changeUserPassword($model, Yii::$app->user->identity)) {
            $systemAlert->setMessage(Alert::INFO, Yii::t('user', 'Password successfully changed'));
        }
        else {
            $systemAlert->setMessage(Alert::DANGER, Yii::t('user', 'Change password error'));
        }
        return $this->refresh();
    }

    /**
     * Profile index
     *
     * @return mixed
     */
    public function actionIndex()
    {
        // profile
        $profileForm = UserForm::findOne(Yii::$app->user->getId());
        $profileForm->setScenario('profile');

        $ret = $this->performAjaxValidation($profileForm);
        if (is_array($ret)) {
            // AJAX validation
            return $ret;
        }
        $dataLoaded = $profileForm->load(Yii::$app->request->post());
        $profileForm->uploadedAvatar = UploadedFile::getInstance($profileForm, 'uploadedAvatar');
        if ($dataLoaded && $profileForm->validate()) {
            // change user's profile
            return $this->changeProfile($profileForm);
        }

        // change password
        $changePasswordForm = new ChangePasswordForm();
        $ret = $this->performAjaxValidation($changePasswordForm);
        if (is_array($ret)) {
            // AJAX validation
            return $ret;
        }
        if ($changePasswordForm->load(Yii::$app->request->post()) && $changePasswordForm->validate()) {
            // change user's password
            return $this->changePassword($changePasswordForm);
        }

        return $this->render('index', [
            'profileForm' => $profileForm,
            'changePasswordForm' => $changePasswordForm,
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
        if (Yii::$app->request->isAjax &&
            $model->load(Yii::$app->request->post()) &&
            Yii::$app->request->post('ajax') == $model->formName()) {
            // AJAX validation
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return null;
    }
}
