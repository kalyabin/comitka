<?php

use app\components\AccessRulesMigration;

/**
 * Rules to be a reviewer
 */
class m160917_140731_user__do_reviews_permissions extends AccessRulesMigration
{
    protected function getPermissions()
    {
        return [
            'changeReviewer' => 'Change a contribution reviewer',
            'setSelfReview' => 'To become self a reviewer',
            'selfFinishReview' => 'Make your own review',
        ];
    }
}
