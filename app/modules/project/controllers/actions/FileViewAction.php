<?php

namespace project\controllers\actions;

use project\models\Project;
use VcsCommon\BaseCommit;
use VcsCommon\BaseDiff;
use VcsCommon\BaseRepository;
use VcsCommon\exception\CommonException;
use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * View file diffs (or raw file) by specific commit id in modal window.
 * Use this action only by AJAX access.
 */
class FileViewAction extends Action
{
    /**
     * Diff type
     */
    const MODE_DIFF = 'diff';

    /**
     * Raw type
     */
    const MODE_RAW = 'raw';

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
     * @var string relative file path
     */
    public $filePath;

    /**
     * @var string file view type: diff or raw
     */
    public $mode;

    /**
     * Validate project model before run action
     *
     * @throws InvalidParamException
     */
    public function init()
    {
        if (!Yii::$app->request->isAjax) {
            // only AJAX access
            throw new ForbiddenHttpException();
        }
        if (!$this->project instanceof Project) {
            throw new InvalidParamException('Repository property must be an instance of \project\models\Project');
        }
        if (!$this->repository instanceof BaseRepository) {
            throw new InvalidParamException('Repository property must be an instance of \VcsCommon\BaseRepository');
        }
        if (!is_string($this->commitId) || !preg_match('#[a-f0-9]+#i', $this->commitId)) {
            throw new InvalidParamException('Invalid commit identifier');
        }
        if (!is_string($this->filePath)) {
            throw new InvalidParamException('Invalid file path');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
    }

    /**
     * Render file view.
     * If has CommonException - it's may by only not found commit, or not found file in project path.
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function run()
    {
        /* @var $commit BaseCommit */
        $commit = null;

        /* @var $fileDiff BaseDiff[] */
        $fileDiff = null;
        /* @var $raw string */
        $raw = null;

        try {
            // get commit model by commit identifier
            $commit = $this->repository->getCommit($this->commitId);

            $fileDiff = $commit->getDiff($this->filePath);
            if ($this->mode === self::MODE_RAW) {
                /**
                 * @todo Get raw file
                 */
            }
        }
        catch (CommonException $ex) {
            throw new NotFoundHttpException(Yii::t('app', 'System error: {message}', [
                'message' => $ex->getMessage(),
            ]), $ex->getCode(), $ex);
        }

        $viewFile = $this->mode === self::MODE_DIFF ? 'file_diff' : 'file_raw';

        return [
            'diff' => Yii::t('project', 'Revision') . ': ' .$commit->getId(),
            'html' => $this->controller->renderAjax('commit/' . $viewFile, [
                'commit' => $commit,
                'diffs' => $fileDiff,
                'path' => $this->filePath,
            ]),
        ];
    }
}
