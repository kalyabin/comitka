<?php
namespace user\controllers\actions;

use user\models\User;
use user\models\UserAccountForm;
use user\UserModule;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Update VCS bindings accounts for user
 */
class VcsBindingsAction extends Action
{
    /**
     * @var integer User id
     */
    public $userId;

    /**
     * @var User User model
     */
    public $model;

    /**
     * @var UserModule
     */
    protected $userModule;

    /**
     * Validate user model
     *
     * @throws NotFoundHttpException
     */
    public function init()
    {
        if (!$this->model instanceof User && $this->userId) {
            $userId = is_scalar($this->userId) ? (int) $this->userId : 0;
            $this->model = User::findOne($userId);
        }
        if (!$this->model instanceof User) {
            throw new NotFoundHttpException();
        }
        $this->userModule = Yii::$app->getModule('user');
        parent::init();
    }

    /**
     * Run action: update exists accounts or add new account
     */
    public function run()
    {
        $model = $this->model;

        $successMessage = '';
        $errorMessage = '';

        $query = $model->getAccounts();
        $query->modelClass = UserAccountForm::className();
        $accounts = $query->indexBy('id')->all();

        // new model
        $newAccount = new UserAccountForm();

        if (Yii::$app->request->post('add-new') && $newAccount->load(Yii::$app->request->post()) && $newAccount->validate()) {
            // add new username
            $result = $this->userModule->updateVcsBindings($model, [$newAccount]);
            if ($result) {
                $successMessage = Yii::t('user', 'New binding was successfully added');
            } else {
                $errorMessage = Yii::t('user', 'Error add a new binding');
            }
        } elseif (Yii::$app->request->post('update') && UserAccountForm::loadMultiple($accounts, Yii::$app->request->post()) && UserAccountForm::validateMultiple($accounts)) {
            $result = $this->userModule->updateVcsBindings($model, $accounts);
            if ($result) {
                $successMessage = Yii::t('user', 'Bindings was successfully updated');
            } else {
                $errorMessage = Yii::t('user', 'Update bindings error');
            }
        }

        if (!empty($_POST)) {
            // reset accounts list
            $accounts = $query->indexBy('id')->all();
        }

        return $this->controller->render('vcs-bindings', [
            'model' => $model,
            'accounts' => $accounts,
            'newAccount' => $newAccount,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
        ]);
    }
}
