<?php
namespace user\controllers;

use app\components\Alert;
use app\components\AuthControl;
use Exception;
use user\models\Role as RoleForm;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;
use yii\rbac\Role;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Controller to manage roles:
 *
 * - create new roles;
 * - update roles;
 * - delete exists roles.
 *
 * Can't update or delete admin role.
 */
class RoleManagerController extends Controller
{
    /**
     * @var DbManager
     */
    protected $authManager;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->authManager = Yii::$app->authManager;
    }

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
                        'roles' => ['manageRole'],
                    ],
                ],
            ]
        ]);
    }

    /**
     * Roles list
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $provider = new ArrayDataProvider([
            'allModels' => $this->authManager->getRoles(),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $provider,
        ]);
    }

    /**
     * Create new role
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $this->layout = '@app/views/layouts/one-column';

        $model = new RoleForm();
        $model->setScenario('create');

        /* @var $systemAlert Alert */
        $systemAlert = Yii::$app->systemAlert;

        if (Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // create new role
                $role = $this->authManager->createRole($model->getName());
                $role->description = $model->description;
                if (!$this->authManager->add($role)) {
                    throw new Exception();
                }

                // add role permissions
                foreach ($model->getPermissionModels() as $permission) {
                    $this->authManager->addChild($role, $permission);
                }

                $transaction->commit();

                $systemAlert->setMessage(Alert::SUCCESS, Yii::t('user', 'Role created successfully'));

                return $this->redirect(['index']);
            }
            catch (Exception $ex) {
                $transaction->rollback();
                $systemAlert->setMessage(Alert::DANGER, Yii::t('app', 'System error: {message}', [
                    'message' => $ex->getMessage(),
                ]));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Update role using string name.
     *
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $this->layout = '@app/views/layouts/one-column';

        $role = $this->findRole($id);

        $model = RoleForm::createFromRole($role, $this->authManager->getChildren($role->name));

        /* @var $systemAlert Alert */
        $systemAlert = Yii::$app->systemAlert;

        if (Yii::$app->request->isAjax && $model->load($_POST)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($_POST) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // update role description
                $role->description = $model->description;
                if (!$this->authManager->update($role->name, $role)) {
                    throw new Exception();
                }

                // update role permissions
                $this->authManager->removeChildren($role);
                foreach ($model->getPermissionModels() as $permission) {
                    $this->authManager->addChild($role, $permission);
                }

                $transaction->commit();

                $systemAlert->setMessage(Alert::SUCCESS, Yii::t('user', 'Role updated successfully'));

                return $this->redirect(['index']);
            }
            catch (Exception $ex) {
                $transaction->rollback();
                $systemAlert->setMessage(Alert::DANGER, Yii::t('app', 'System error: {message}', [
                    'message' => $ex->getMessage(),
                ]));
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Delete role using string name
     */
    public function actionDelete($id)
    {
        $role = $this->findRole($id);

        /* @var $systemAlert Alert */
        $systemAlert = Yii::$app->systemAlert;

        try {
            $this->authManager->remove($role);
            $systemAlert->setMessage(Alert::SUCCESS, Yii::t('user', 'Role successfully deleted'));
        } catch (Exception $ex) {
            $systemAlert->setMessage(Alert::DANGER, Yii::t('app', 'System error: {message}', [
                'message' => $ex->getMessage(),
            ]));
        }

        return $this->redirect(['index']);
    }


    /**
     * Find role by name and throws NotFoundHttpException if it not exists.
     *
     * @param string $id
     * @return Role
     * @throws NotFoundHttpException
     */
    protected function findRole($id)
    {
        $role = is_string($id) ? $this->authManager->getRole($id) : null;

        if (!$role instanceof Role) {
            throw new NotFoundHttpException();
        }
        else if ($role->name == 'admin') {
            // can't remove or update admin role
            throw new ForbiddenHttpException(Yii::t('user', "You can't update or delete administrative role"));
        }

        return $role;
    }

}