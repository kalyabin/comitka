<?php

namespace project\controllers;

use app\components\AuthControl;
use app\components\ContributorApi;
use project\models\ContributionReview;
use project\models\Project;
use project\ProjectModule;
use project\widgets\CommitPanel;
use VcsCommon\BaseCommit;
use VcsCommon\exception\CommonException;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Controller to manage contributions reviews:
 *
 * - list contributions;
 * - finish contributions;
 * - to be a reviewer
 */
class ContributionReviewController extends Controller
{
    /**
     * @var ProjectModule
     */
    protected $projectApi;

    /**
     * @var ContributorApi
     */
    protected $contributorApi;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->projectApi = Yii::$app->getModule('project');
        $this->contributorApi = Yii::$app->contributors;
        return parent::init();
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
                        'roles' => ['@'],
                        'actions' => ['list'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['setSelfReview'],
                        'actions' => ['create-self-review'],
                        'verbs' => ['POST'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['selfFinishReview'],
                        'actions' => ['finish-review'],
                        'verbs' => ['POST'],
                    ],
                ],
            ]
        ]);
    }

    /**
     * List of need to review contributions
     *
     * Type variable has states:
     * - my-reviews - reviews to current contributor;
     * - all-contributions - all contributions;
     * - my-contributions - current user contributions;
     * - no-reviewer - contributions without reviewer;
     *
     * @param string $type Contribution types
     *
     * @return mixed
     *
     * @throws NotFoundHttpException
     */
    public function actionList($type)
    {
        $res = ContributionReview::find()->with('project')->orderBy([
            'date' => SORT_DESC,
        ]);

        if ($type == 'my-reviews') {
            // contribution reviews to current contributor
            $res->andWhere([
                'reviewer_id' => Yii::$app->user->getId(),
                'reviewed' => null,
            ]);
        } elseif ($type == 'all-contributions') {
            // all contributions reviews
            $res->andWhere([
                'reviewed' => null,
            ]);
        } elseif ($type == 'my-contributions') {
            // current contributor commits
            $res->andWhere([
                'contributor_id' => Yii::$app->user->getId(),
            ]);
        } elseif ($type == 'no-reviewer') {
            // without reviewer
            $res->andWhere([
                'reviewer_id' => null,
            ]);
        } else {
            throw new NotFoundHttpException();
        }

        $dataProvider = new ActiveDataProvider([
            'id' => $type,
            'query' => $res,
            'sort' => false,
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Finish review
     *
     * @param integer $projectId Project identifier
     * @param string $commitId Commit identifier
     *
     * @return array
     */
    public function actionFinishReview($projectId, $commitId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $project = $this->findProject($projectId);
        $commit = $this->findCommit($project, $commitId);

        $result = [
            'success' => false,
            'html' => '',
            'message' => '',
        ];

        // find existent review model
        $model = ContributionReview::find()->andWhere([
            'project_id' => $project->id,
            'commit_id' => $commit->getId(),
        ])->one();

        if (!$model instanceof ContributionReview) {
            $result['success'] = false;
            $result['message'] = Yii::t('project', 'Review has not been started');
        } elseif (!$model->canFinishReview(Yii::$app->user->getId())) {
            $result['success'] = false;
            $result['message'] = Yii::t('project', 'You are not a reviewer for this contribution');
        } else {
            $result['success'] = $model->finishReview();
            if (!$result['success']) {
                $result['message'] = Yii::t('project', 'An error occurred during the finish of the review');
            }
        }

        if ($result['success']) {
            $result['html'] = CommitPanel::widget([
                'reviewModel' => $model,
                'authUser' => Yii::$app->user,
                'contributor' => $model->contributor,
                'project' => $project,
                'commit' => $commit,
                'reviewButtonClass' => 'js-review-button',
            ]);
        }

        return $result;
    }

    /**
     * To be reviewer
     *
     * @param integer $projectId Project identifier
     * @param string $commitId Commit identifier
     *
     * @return array
     */
    public function actionCreateSelfReview($projectId, $commitId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $project = $this->findProject($projectId);
        $commit = $this->findCommit($project, $commitId);

        $result = [
            'success' => false,
            'html' => '',
            'message' => '',
        ];

        // find existent review model
        $model = ContributionReview::find()->andWhere([
            'project_id' => $project->id,
            'commit_id' => $commit->getId(),
        ])->one();

        if ($model && $model->reviewer_id == Yii::$app->user->getId()) {
            // user try to create existent model
            // it's not an error
            $result['success'] = true;
        } elseif ($model && is_null($model->reviewer_id)) {
            // model exists, but reviewer is not set
            $model->reviewer_id = Yii::$app->user->getId();
            if ($model->save()) {
                $result['success'] = true;
            }
        } elseif ($model) {
            // other reviewer already installed
            $result['success'] = false;
            $result['message'] = Yii::t('project', 'Reviewer is already installed to this contribution');
        } else {
            // model is not exists
            // create it
            $contributor = $this->contributorApi->getContributor($project->repo_type, $commit->contributorName, $commit->contributorEmail);
            $reviewer = $this->contributorApi->getContributorById(Yii::$app->user->getId());
            $model = $this->projectApi->createContributionReview($project, $commit, $contributor, $reviewer);
            if (!$model instanceof ContributionReview) {
                $result['success'] = false;
                $result['message'] = Yii::t('project', 'An error occurred during the creation of the review');
            } else {
                $result['success'] = true;
            }
        }

        if ($result['success'] && $model) {
            $result['html'] = CommitPanel::widget([
                'reviewModel' => $model,
                'authUser' => Yii::$app->user,
                'contributor' => $model->contributor,
                'project' => $project,
                'commit' => $commit,
                'reviewButtonClass' => 'js-review-button',
            ]);
        }

        return $result;
    }

    /**
     * Get commit model.
     *
     * Throws 404 if commit not found, or system error.
     *
     * @param Project $project Project model
     * @param string $commitId Commit identifier
     *
     * @return BaseCommit
     *
     * @throws NotFoundHttpException
     */
    protected function findCommit(Project $project, $commitId)
    {
        try {
            return $project->getRepositoryObject()->getCommit($commitId);
        } catch (CommonException $ex) {
            throw new NotFoundHttpException(Yii::t('app', 'System error: {message}', [
                'message' => $ex->getMessage(),
            ]), $ex->getCode(), $ex);
        }
    }

    /**
     * Get project model
     *
     * Throws 404 if project not found.
     *
     * @param integer $projectId Project identifier
     *
     * @return Project
     *
     * @throws NotFoundHttpException
     */
    protected function findProject($projectId)
    {
        $project = Project::findOne((int) $projectId);

        if (!$project instanceof Project) {
            throw new NotFoundHttpException(Yii::t('project', 'Project not found'));
        }

        return $project;
    }
}
