<?php

use user\models\User;
use user\models\UserSearch;
use yii\bootstrap\Html;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\web\View;

/* @var $this View */
/* @var $model UserSearch */
/* @var $dataProvider ActiveDataProvider */
?>
<h1>
    <?=Yii::t('user', 'Users list')?>

    <?php if (Yii::$app->user->can('createUser')):?>
        <?=Html::a(Yii::t('user', 'Create'), ['create'], ['class' => 'btn btn-primary'])?>
    <?php endif;?>
</h1>
<?php
print GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'columns' => [
        'id', 'name',
        [
            'attribute' => 'email',
            'value' => function($data) {
                /* @var $data User */
                return Yii::$app->user->can('updateUser') || Yii::$app->user->can('deleteUser') ? Html::a(Html::encode($data->email), ['update', 'id' => $data->id]) : $data->email;
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'status',
            'value' => function($data) {
                /* @var $data User */
                return $data->getStatusName();
            },
            'filter' => Html::activeDropDownList($model, 'status', $model->getStatuses(), ['class' => 'form-control']),
        ],
        [
            'class' => ActionColumn::className(),
            'template' => Yii::$app->user->can('updateUser') || Yii::$app->user->can('deleteUser') ? '{update}' : '',
        ]
    ]
]);
?>
