<?php
namespace app\commands;

use app\components\ContributorApi;
use app\models\ContributorInterface;
use app\statistics\ContributionAggregator;
use DateTime;
use project\models\ContributionReview;
use project\models\Project;
use project\ProjectModule;
use user\models\User;
use VcsCommon\BaseCommit;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Worker for ContributionReview model.
 *
 * @see ContributionReview
 */
class ContributionReviewController extends Controller
{
    /**
     * Log info message for specified value.
     *
     * @param string $message Prefix for value
     * @param mixed $value Value to print out
     */
    protected function logInfo($message, $value)
    {
        $this->stdout($message, Console::FG_GREEN);
        $this->stdout($value . "\n", Console::FG_YELLOW);
    }

    /**
     * Send email to reviewer.
     *
     * $contributionsByProjects array contains:
     * - project - a project model;
     * - statistic - ContributionByContributor array.
     *
     * @param User $reviewer
     * @param array $contributionsByProjects
     */
    protected function sendEmailToReviewer(User $reviewer, array $contributionsByProjects)
    {
        return Yii::$app->mailer->compose('contributionsForReviewer', [
            'user' => $reviewer,
            'contributionsByProjects' => $contributionsByProjects,
        ])
        ->setTo($reviewer->email)
        ->setSubject(Yii::t('user', 'Contributions to need review'))
        ->send();
    }

    /**
     * Send emails to reviewers with contributions to need review.
     * If reviewer has no any reviews - does not send it.
     * Collect contributions by project IDs.
     */
    public function actionSendReviewerEmail()
    {
        // get projects
        /* @var $projects Project */
        $projects = Project::find()->all();
        // get active reviewers
        /* @var $reviewers User[] */
        $reviewers = User::find([
            'status' => User::STATUS_ACTIVE
        ])->indexBy('id')->all();

        // collect contributions to need review for each project
        foreach ($reviewers as $reviewer) {
            $this->logInfo('Collect reviews for: ', $reviewer->getContributorName());
            $contributionsByProjects = [];
            foreach ($projects as $project) {
                $aggregator = new ContributionAggregator([
                    'projectId' => $project->id,
                    'reviewerId' => $reviewer->id,
                    'type' => ContributionAggregator::TYPE_NOT_FINISHED,
                ]);
                $statistic = $aggregator->aggregateByContributor();
                if (!empty($statistic)) {
                    $contributionsByProjects[$project->id] = [
                        'project' => $project,
                        'statistic' => $statistic,
                    ];
                }
            }
            $this->logInfo('Total projects to review: ', count($contributionsByProjects));
            if (!empty($contributionsByProjects)) {
                // send email to reviewer
                $result = $this->sendEmailToReviewer($reviewer, $contributionsByProjects);
                $this->logInfo('Send e-mail to ' . $reviewer->getContributorEmail() . ': ', $result ? 'done' : 'error');
            }
        }
    }

    /**
     * Collect contributions for specified project and date.
     *
     * If projectId is not set, collect contributions for any project.
     * If dateFrom is not set, collect contributions from last check point.
     * If last check point is not set, collect contributions from 2 last days.
     *
     * @param string $dateFrom Date from in Y-m-d H:i:s format. Null if need collect from last check point
     * @param integer $projectId Project identifier or null if need collect for any project
     */
    public function actionCollectContributions($dateFrom = null, $projectId = null)
    {
        $checkpointFile = Yii::getAlias('@runtime/contribution-collector-date.txt');

        if (is_null($dateFrom) && is_file($checkpointFile)) {
            $dateFrom = trim(file_get_contents($checkpointFile));
        } elseif (is_null($dateFrom)) {
            $dateFrom = date('Y-m-d H:i:s', strtotime('-2 day'));
        }
        file_put_contents($checkpointFile, date('Y-m-d H:i:s'));

        $dateFrom = DateTime::createFromFormat('Y-m-d H:i:s', $dateFrom);
        if ($dateFrom === false) {
            $this->stderr('Wrong date time format', Console::FG_RED);
            return 1;
        }

        /* @var $projectApi ProjectModule */
        $projectApi = Yii::$app->getModule('project');
        /* @var $contributorApi ContributorApi */
        $contributorApi = Yii::$app->contributors;

        $resProject = Project::find();

        if (!is_null($projectId) && is_scalar($projectId)) {
            $resProject->andWhere(['id' => (int) $projectId]);
        }

        $this->logInfo('Total projects count: ', $resProject->count());


        foreach ($resProject->each() as $project) {
            /* @var $project Project */
            $this->logInfo('Collect contributions for: ', $project->title);
            // calculate sum
            $cntCollected = 0;
            $cntErrors = 0;
            foreach ($projectApi->getProjectContributions($project, $dateFrom) as $commit) {
                /* @var $commit BaseCommit */
                $contributor = $contributorApi->getContributor($project->repo_type, $commit->contributorName, $commit->contributorEmail);
                $model = $projectApi->createContributionReview($project, $commit, $contributor);
                if ($model) {
                    $this->stdout('.', Console::FG_GREEN);
                    $cntCollected++;
                } else {
                    $this->stdout('x', Console::FG_RED);
                    $cntErrors++;
                }
            }
            $this->stdout("\n");
            $this->logInfo('Collected: ', $cntCollected);
            $this->logInfo('Errors: ', $cntErrors);
        }
    }
}
