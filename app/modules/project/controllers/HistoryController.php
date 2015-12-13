<?php
namespace project\controllers;

use app\components\Alert;
use app\components\AuthControl;
use project\models\Project;
use VcsCommon\BaseBranch;
use VcsCommon\BaseCommit;
use VcsCommon\exception\CommonException;
use VcsCommon\Graph;
use Yii;
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
     * Graph history type
     */
    const TYPE_GRAPH = 'graph';

    /**
     * Simple history type
     */
    const TYPE_SIMPLE = 'simple';

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
     * View project history: graph history or simple
     *
     * @param string $type history type: graph or simple, throws 404 if else
     * @param integer $id project identifier
     * @param integer $page page number
     * @throws NotFoundHttpException
     */
    public function actionHistory($type, $id, $page = 1)
    {
        $project = $this->findModel($id);

        $skip = $this->calculateSkip($page);

        // commits list
        /* @var $history BaseCommit[] */
        $history = [];

        // branches list with head commits
        /* @var $branches BaseBranch[] */
        $branches = [];

        try {
            $repository = $project->getRepositoryObject();
            if ($type == self::TYPE_SIMPLE) {
                $history = $repository->getHistory(self::PAGE_LIMIT, $skip);
            }
            else if ($type == self::TYPE_GRAPH) {
                $graph = $repository->getGraphHistory(self::PAGE_LIMIT, $skip);
                $history = $graph->getCommits();
            }
            else {
                throw new NotFoundHttpException();
            }

            $branches = $repository->getBranches();
        } catch (CommonException $ex) {
            /* @var $systemAlert Alert */
            $systemAlert = Yii::$app->systemAlert;
            $systemAlert->setMessage(Alert::DANGER, Yii::t('app', 'System error: {message}', [
                'message' => $ex->getMessage(),
            ]));
        }

        // list pages
        $pagination = new Pagination([
            'pageSize' => self::PAGE_LIMIT,
            'totalCount' => count($history) < self::PAGE_LIMIT ?
                $skip + count($history) :
                $skip + self::PAGE_LIMIT + 1,
            'defaultPageSize' => self::PAGE_LIMIT,
        ]);

        return $this->render($type, [
            'project' => $project,
            'pagination' => $pagination,
            'history' => $history,
            'branches' => $branches,
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
