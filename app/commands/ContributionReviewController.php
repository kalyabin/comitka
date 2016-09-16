<?php
namespace app\commands;

use DateTime;
use project\models\ContributionReview;
use project\models\Project;
use project\ProjectModule;
use user\UserModule;
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
        /* @var $userApi UserModule */
        $userApi = Yii::$app->getModule('user');

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
                $contributor = $userApi->getUserByUsername($project->repo_type, $commit->contributorName, $commit->contributorEmail);
                $contributorId = $contributor ? $contributor->id : null;
                $reviewerUserId = $contributor ? $contributor->default_reviewer_id : null;
                $model = $projectApi->createContributionReview($project, $commit, $contributorId, $reviewerUserId);
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
