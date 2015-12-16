<?php
namespace project\controllers\actions;

use project\models\Project;
use VcsCommon\BaseCommit;
use VcsCommon\BaseRepository;
use VcsCommon\exception\CommonException;
use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * View commit summary information using project model
 */
class CommitSummaryAction extends Action
{
    /**
     * @var Project project model
     */
    public $project;

    /**
     * @var BaseRepository repository model
     */
    public $repository;

    /**
     * @var string commit identifier
     */
    public $commitId;

    /**
     * Validate project model before run action
     *
     * @throws InvalidParamException
     */
    public function init()
    {
        if (!$this->project instanceof Project) {
            throw new InvalidParamException('Repository property must be an instance of \project\models\Project');
        }
        if (!$this->repository instanceof BaseRepository) {
            throw new InvalidParamException('Repository property must be an instance of \VcsCommon\BaseRepository');
        }
        if (!is_string($this->commitId) || !preg_match('#[a-f0-9]+#i', $this->commitId)) {
            throw new InvalidParamException('Invalid commit identifier');
        }
    }

    /**
     * Render commit view.
     * If has CommonException - it's may be only not founded commit - generates 404.
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function run()
    {
        /* @var $commit BaseCommit */
        $commit = null;

        try {
            // get commit model by commit identifier
            $commit = $this->repository->getCommit($this->commitId);
        }
        catch (CommonException $ex) {
            throw new NotFoundHttpException(Yii::t('app', 'System error: {message}', [
                'message' => $ex->getMessage(),
            ]), $ex->getCode(), $ex);
        }

        return $this->controller->render('commit/summary', [
            'project' => $this->project,
            'repository' => $this->repository,
            'commit' => $commit,
        ]);
    }
}
