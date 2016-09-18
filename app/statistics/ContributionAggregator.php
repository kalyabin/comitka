<?php

namespace app\statistics;

use app\components\ContributorApi;
use app\statistics\result\ContributionByContributor;
use project\models\ContributionReview;
use Yii;
use yii\base\Object;
use yii\db\Expression;

/**
 * Contribution aggregator by specified filter.
 */
class ContributionAggregator extends Object
{
    /**
     * Only not finished contributions to aggregate
     */
    const TYPE_NOT_FINISHED = 'not-finished';

    /**
     * Only finished contributions to aggregate
     */
    const TYPE_FINISHED = 'finished';

    /**
     * @var integer Filter by project identifier
     */
    public $projectId;

    /**
     * @var integer Filter by reviewer identifier (and null, if reviewer is not set)
     */
    public $reviewerId;

    /**
     * Contributions type (by TYPE_* constants):
     *
     * - null - all contributions;
     * - not-finished - only not finished contributions;
     * - finished - only finished contributions;
     *
     * @var string
     */
    public $type;

    /**
     * Aggregate contributions by contributor.
     *
     * Returns results as ContributionByContributor array, wich contains contributor interface and contributions count.
     *
     * @return ContributionByContributor[]
     */
    public function aggregateByContributor()
    {
        $query = ContributionReview::find()
            ->select(['repo_type', 'contributor_name', 'contributor_email', 'contributor_id', 'project_id', new Expression('COUNT(*) as cnt')])
            ->andWhere([
                'or', ['reviewer_id' => $this->reviewerId], ['reviewer_id' => null]
            ])
            ->groupBy(['project_id', 'repo_type', 'contributor_id', 'contributor_name', 'contributor_email']);

        if ($this->projectId) {
            $query->andWhere([
                'project_id' => $this->projectId,
            ]);
        }

        if ($this->type == self::TYPE_FINISHED) {
            $query->andWhere(['not', 'reviewed' => null]);
        } elseif ($this->type == self::TYPE_NOT_FINISHED) {
            $query->andWhere(['reviewed' => null]);
        }

        $res = $query->createCommand()->query();

        /* @var $contributorsApi ContributorApi */
        $contributorsApi = Yii::$app->contributors;

        $result = [];

        foreach ($res as $item) {
            $contributor = null;

            if (!is_null($item['contributor_id'])) {
                $contributor = $contributorsApi->getContributorById($item['contributor_id']);
            } else {
                $contributor = $contributorsApi->getContributor($item['repo_type'], $item['contributor_name'], $item['contributor_email']);
            }

            $result[] = new ContributionByContributor([
                'contributor' => $contributor,
                'cnt' => $item['cnt'],
            ]);
        }

        return $result;
    }
}
