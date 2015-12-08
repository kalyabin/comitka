<?php

use project\models\Project;
use VcsCommon\BaseCommit;
use yii\bootstrap\Html;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\LinkPager;

/* @var $this View */
/* @var $project Project */
/* @var $history ArrayDataProvider */
/* @var $pagination Pagination */
?>
<h1>
    <?=Html::encode($project->title)?>
    <span class="<?=$project->getRepoLabelCss()?>"><?=$project->getRepoTypeName()?></span>
</h1>

<?=GridView::widget([
    'dataProvider' => $history,
    'layout' => '{items}',
    'columns' => [
        [
            'label' => Yii::t('project', 'Date'),
            'value' => function($data) {
                /* @var $data BaseCommit */
                return $data->getDate()->format('d\'M y H:i:s');
            },
        ],
        [
            'label' => Yii::t('project', 'Message'),
            'value' => function($data) {
                /* @var $data BaseCommit */
                return Html::a(Html::encode($data->message), ['commit', 'id' => $data->getId()]);
            },
            'format' => 'html',
        ],
        [
            'label' => Yii::t('project', 'Contributor'),
            'value' => function($data) {
                /* @var $data BaseCommit */
                $contributor = Html::encode($data->contributorName);
                if (!empty($data->contributorEmail)) {
                    $contributor .= ' <' . $data->contributorEmail . '>';
                }
                return $contributor;
            }
        ],
    ]
])?>

<?=LinkPager::widget([
    'pagination' => $pagination,
])?>