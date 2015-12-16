<?php
namespace project\controllers\actions;

use project\models\Project;
use VcsCommon\BaseBranch;
use VcsCommon\BaseCommit;
use VcsCommon\exception\CommonException;
use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * View repository log using project model
 */
class LogAction extends Action
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
     * @var Project project model
     */
    public $project;

    /**
     * @var BaseRepository repository model
     */
    public $repository;

    /**
     * @var string history type (see TYPE_* constants)
     */
    public $type;

    /**
     * Validate input vars before run action
     *
     * @throws InvalidParamException
     */
    public function init()
    {
        parent::init();
        if (!$this->project instanceof Project) {
            throw new InvalidParamException('Repository property must be an instance of \project\models\Project');
        }
        if (!$this->repository instanceof \VcsCommon\BaseRepository) {
            throw new InvalidParamException('Repository property must be an instance of \VcsCommon\BaseRepository');
        }
        if (!is_string($this->type)) {
            throw new InvalidParamException('History type must be a string');
        }
    }

    /**
     * Render repository history using set type (graph or simple).
     * If type not found - generate 404.
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function run()
    {
        $page = is_scalar(Yii::$app->request->get('page', 1)) ? (int) Yii::$app->request->get('page', 1) : 1;

        // calculate skipped commits amount
        $skip = $this->calculateSkip($page);

        // commits list
        /* @var $history BaseCommit[] */
        $history = [];

        // branches list with head commits
        /* @var $branches BaseBranch[] */
        $branches = [];

        try {
            // view simple log
            if ($this->type == self::TYPE_SIMPLE) {
                $history = $this->repository->getHistory(self::PAGE_LIMIT, $skip);
            }
            // view graph log
            else if ($this->type == self::TYPE_GRAPH) {
                $graph = $this->repository->getGraphHistory(self::PAGE_LIMIT, $skip);
                $history = $graph->getCommits();
            }
            // if else - generate 404
            else {
                throw new NotFoundHttpException();
            }

            $branches = $this->repository->getBranches();
        } catch (CommonException $ex) {
            throw new ServerErrorHttpException(Yii::t('app', 'System error: {message}', [
                'message' => $ex->getMessage(),
            ]), $ex->getCode(), $ex);
        }

        // list pages
        $pagination = new Pagination([
            'pageSize' => self::PAGE_LIMIT,
            'totalCount' => count($history) < self::PAGE_LIMIT ?
                $skip + count($history) :
                $skip + self::PAGE_LIMIT + 1,
            'defaultPageSize' => self::PAGE_LIMIT,
        ]);

        return $this->controller->render('log/' . $this->type, [
            'project' => $this->project,
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
}
