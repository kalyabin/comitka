<?php
namespace project\controllers;

use app\components\Alert;
use app\components\AuthControl;
use project\models\Project;
use VcsCommon\exception\CommonException;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * View projects history using simple view or graph view
 */
class HistoryController extends Controller
{
    /**
     * Commits per page
     */
    const PAGE_LIMIT = 100;

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
            ]
        ]);
    }

    /**
     * Simple project history
     *
     * @param integer $id project identifier
     * @param integer $page page number
     */
    public function actionSimple($id, $page = 1)
    {
        $project = $this->findModel($id);
        $skip = $this->calculateSkip($page);

        $history = new ArrayDataProvider([
            'pagination' => [
                'pageSize' => self::PAGE_LIMIT,
                'defaultPageSize' => self::PAGE_LIMIT,
            ],
        ]);

        try {
            $repository = $project->getRepositoryObject();
            $history->allModels = $repository->getHistory(self::PAGE_LIMIT, $skip);
        }
        catch (CommonException $ex) {
            /* @var $systemAlert Alert */
            $systemAlert = Yii::$app->systemAlert;
            $systemAlert->setMessage(Alert::DANGER, Yii::t('app', 'System error: {message}', [
                'message' => $ex->getMessage(),
            ]));
        }

        // extends ArrayDataProvider pagination
        $pagination = new Pagination([
            'pageSize' => self::PAGE_LIMIT,
            'totalCount' => count($history->allModels) < self::PAGE_LIMIT ?
                $skip + count($history->allModels) :
                $skip + self::PAGE_LIMIT + 1,
            'defaultPageSize' => self::PAGE_LIMIT,
        ]);

        return $this->render('simple', [
            'project' => $project,
            'pagination' => $pagination,
            'history' => $history,
        ]);
    }

    /**
     * Calculate amount of skipped commits using page num.
     *
     * @param integer $page page number
     * @return integer skipped commits
     */
    protected function calculateSkip($page)
    {
        $page = is_scalar($page) ? max(1, (int) $page) : 1;
        $skipPages = max(0, $page - 1);
        return $skipPages * self::PAGE_LIMIT;
    }

    /**
     * Find project model
     *
     * @param integer $id
     * @return Project
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = is_scalar($id) ? Project::findOne($id) : null;
        if (!$model instanceof Project) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}