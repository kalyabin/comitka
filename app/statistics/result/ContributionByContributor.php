<?php

namespace app\statistics\result;

use app\models\ContributorInterface;
use yii\base\Object;

/**
 * Contribution statistics by contributors.
 *
 * Contains ContributorInterface and contributions count.
 */
class ContributionByContributor extends Object
{
    /**
     * @var ContributorInterface Contributor for statistics
     */
    public $contributor;

    /**
     * @var integer Contributions count
     */
    public $cnt;
}
