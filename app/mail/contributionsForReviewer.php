<?php

use app\statistics\result\ContributionByContributor;
use project\models\Project;
use user\models\User;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $user User */
/* @var $contributionsByProjects array */

print Yii::t('project', 'There are commits requiring your reviews: ');
print '<br /><br />';

foreach ($contributionsByProjects as $contributions) {
    /* @var $project Project */
    $project = $contributions['project'];
    /* @var $statistic ContributionByContributor[] */
    $statistic = $contributions['statistic'];

    print Html::tag('strong', Html::encode($project->title)) . ': <br />';

    print Html::beginTag('ul');
    foreach ($statistic as $item) {
        $contributor = Html::encode($item->contributor->getContributorName());
        $contributions = $item->cnt . ' ' . Yii::t('project', '{n, plural, =0{commits} =1{commit} other{commits}}', [
            'n' => $item->cnt,
        ]);
        print Html::tag('li',  $contributor . ': ' . $contributions);
    }
    print Html::endTag('ul');
}

$url = Yii::$app->urlManager->createAbsoluteUrl([
    '/project/contribution-review/list',
    'type' => 'my-reviews',
]);

print '<br /><br />' . Html::a(Yii::t('project', 'More details'), $url);
