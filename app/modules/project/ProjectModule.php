<?php

namespace project;

use app\models\ContributorInterface;
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
     * @param ContributorInterface $contributor Contributor model
     * @param ContributorInterface $reviewer Reviewer model (null if set to default contributor reviewer)
     *
     * @return ContributionReview|null
     */
    public function createContributionReview(Project $project, BaseCommit $commit, ContributorInterface $contributor, $reviewer = null)
    {
        $model = new ContributionReview();

        $model->setAttributes([
            'commit_id' => $commit->getId(),
            'project_id' => $project->id,
            'date' => $commit->getDate()->format('Y-m-d H:i:s'),
            'message' => $commit->message,
            'contributor_name' => $commit->contributorName,
            'contributor_email' => $commit->contributorEmail,
            'repo_type' => $project->repo_type,
            'contributor_id' => $contributor->getContributorId(),
            'reviewer_id' => $reviewer instanceof ContributorInterface ?
                $reviewer->getContributorId() :
                $contributor->getDefaultViewerId(),
        ]);

        if ($model->save()) {
            return $model;
        }

        return null;
    }
}
