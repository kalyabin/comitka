<?php

namespace project\controllers;

use app\components\Alert;
use app\components\AuthControl;
use Exception;
use project\models\Project;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Controller to manage projects:
 *
 * - view project list;
 * - create new projects;
 * - update projects;
 * - delete exists projects;
 */
class ProjectController extends Controller
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
                        'actions' => ['index', 'tree'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['createProject'],
                        'actions' => ['create'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['updateProject'],
                        'actions' => ['update'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['deleteProject'],
                        'actions' => ['delete'],
                        'verbs' => ['POST'],
                    ],
                ],
            ]
        ]);
    }

    /**
     * Projects list
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = Project::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * View project path tree
     *
     * @param integer $id Project identifier
     * @param string $subDir Relative project path (null if root)
     */
    public function actionTree($id, $subDir = null)
    {
        $subDir = trim($subDir, DIRECTORY_SEPARATOR);

        $project = $this->findModel($id);

        $repository = $project->getRepositoryObject();
        $filesList = $repository->getFilesList($subDir);

        $previewPath = null;
        $currentPath = array_map(function($value) use (&$previewPath) {
            $path = $value;
            if ($previewPath !== null) {
                $path = $previewPath . DIRECTORY_SEPARATOR . $value;
            }
            $previewPath = $path;
            return [
                'subDir' => $path,
                'value' => $value,
            ];
        }, $subDir === null ? [] : explode(DIRECTORY_SEPARATOR, FileHelper::normalizePath($subDir)));

        array_unshift($currentPath, [
            'subDir' => null,
            'value' => $project->title,
        ]);

        return $this->render('files-tree', [
            'project' => $project,
            'repository' => $repository,
            'filesList' => $filesList,
            'currentPath' => $currentPath,
        ]);
    }

    /**
     * Create project form
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Project();

        if (Yii::$app->request->isAjax) {
            // AJAX-validation
            $model->load(Yii::$app->request->post());
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        /* @var $systemAlert Alert */
        $systemAlert = Yii::$app->systemAlert;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                if ($model->save()) {
                    $systemAlert->setMessage(Alert::SUCCESS, Yii::t('project', 'Project successfully created'));
                    return $this->redirect(['index']);
                }
                else {
                    $systemAlert->setMessage(Alert::DANGER, Yii::t('project', 'Creation project error'));
                }
            } catch (Exception $ex) {
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
     * Update project form
     *
     * @param integer $id project identifier
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax) {
            // AJAX-validation
            $model->load(Yii::$app->request->post());
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        /* @var $systemAlert Alert */
        $systemAlert = Yii::$app->systemAlert;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                if ($model->save()) {
                    $systemAlert->setMessage(Alert::SUCCESS, Yii::t('project', 'Project successfully updated'));
                    return $this->redirect(['index']);
                }
                else {
                    $systemAlert->setMessage(Alert::DANGER, Yii::t('project', 'Project update error'));
                }
            } catch (Exception $ex) {
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
     * Remove project by identifier
     *
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        /* @var $systemAlert Alert */
        $systemAlert = Yii::$app->systemAlert;

        try {
            if ($model->delete()) {
                $systemAlert->setMessage(Alert::SUCCESS, Yii::t('project', 'Project successfully deleted'));
            }
            else {
                $systemAlert->setMessage(Alert::DANGER, Yii::t('project', 'Project delete error'));
            }
        } catch (Exception $ex) {
            $systemAlert->setMessage(Alert::DANGER, Yii::t('app', 'System error: {message}', [
                'message' => $ex->getMessage(),
            ]));
        }

        return $this->redirect(['index']);
    }

    /**
     * Find project model by identifier
     *
     * @param integer $id
     * @return Project
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = Project::findOne($id);
        if (!$model instanceof Project) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
