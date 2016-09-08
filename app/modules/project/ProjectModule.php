<?php

namespace project;

use DateTime;
use project\models\ContributionReview;
use project\models\Project;
use VcsCommon\BaseCommit;
use VcsCommon\BaseRepository;
use yii\base\Module as BaseModule;

/**
 * Provides API to manage projects
 */
class ProjectModule extends BaseModule
{
    /**
     * Retrieve project commits from specified date and time.
     *
     * @param Project $project Project model
     * @param DateTime $dateFrom Date and time from get a commits.
     *
     * @return BaseCommit[]
     */
    public function getProjectContributions(Project $project, DateTime $dateFrom)
    {
        $pageLimit = 10;
        $skip = 0;

        /* @var $repository BaseRepository */
        $repository = $project->getRepositoryObject();

        $currentDate = new DateTime();

        do {
            /* @var $commits BaseCommit[] */
            $commits = $repository->getHistory($pageLimit, $skip);

            foreach ($commits as $commit) {
                if ($commit->getDate()->getTimestamp() > $dateFrom->getTimestamp()) {
                    /* @var $commit BaseCommit */
                    yield $commit;
                }

                // if current timestamp less than commit timestamp
                $currentDate = $commit->getDate();

                if ($currentDate->getTimestamp() <= $dateFrom->getTimestamp()) {
                    break;
                }
            }

            $skip += count($commits);

            // reached last page, break
            if (count($commits) < $pageLimit) {
                break;
            }
        } while ($currentDate->getTimestamp() > $dateFrom->getTimestamp());
    }

    /**
     * Creates contribution review model.
     *
     * Returns model if successfully added.
     *
     * @param Project $project Project model to relate with model
     * @param BaseCommit $commit Contribution model to relate with model
     * @param integer $contributorUserId Contributor user id (null if not set)
     * @param integer $reviewerUserId Reviewer user id (null if not set)
     *
     * @return ContributionReview|null
     */
    public function createContributionReview(Project $project, BaseCommit $commit, $contributorUserId = null, $reviewerUserId = null)
    {
        $model = new ContributionReview();

        $model->setAttributes([
            'commit_id' => $commit->getId(),
            'project_id' => $project->id,
            'date' => $commit->getDate()->format('Y-m-d H:i:s'),
        ]);

        // set users ids
        if (is_scalar($contributorUserId) && (int) $contributorUserId > 0) {
            $model->contributor_id = (int) $contributorUserId;
        }
        if (is_scalar($reviewerUserId) && (int) $reviewerUserId > 0) {
            $model->reviewer_id = (int) $reviewerUserId;
        }

        if ($model->save()) {
            return $model;
        }

        return null;
    }
}
