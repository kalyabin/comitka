<?php

use project\models\Project;
use yii\bootstrap\Html;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
?>
<h1>
    <?=Yii::t('project', 'Projects')?>

    <?php if (Yii::$app->user->can('createProject')):?>
        <?=Html::a(Yii::t('project', 'New project'), ['create'], ['class' => 'btn btn-primary'])?>
    <?php endif;?>
</h1>
<?php
$permissions = [];
if (Yii::$app->user->can('updateProject')) {
    $permissions[] = '{update}';
}
if (Yii::$app->user->can('deleteProject')) {
    $permissions[] = '{delete}';
}
print GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'title',
            'value' => function($data) {
                /* @var $data Project */
                $title = Html::a(Html::encode($data->title), ['/project/history/history', 'id' => $data->getPrimaryKey(), 'type' => 'simple']);
                $title .= ' ' . Html::tag('span', strtoupper($data->getRepoTypeName()), ['class' => $data->getRepoLabelCss()]);
                return $title;
            },
            'format' => 'html',
        ],
        [
            'class' => ActionColumn::className(),
            'template' => implode(' ', $permissions),
        ]
    ]
]);
?>
