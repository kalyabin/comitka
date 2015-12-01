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
/* @var $dataProvider ArrayDataProvider */
?>
<h1>
    <?=Yii::t('user', 'Roles list')?>

    <?=Html::a(Yii::t('user', 'Create'), ['create'], ['class' => 'btn btn-primary'])?>
</h1>
<?php
print GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => SerialColumn::className(),
        ],
        'name', 'description',
        [
            'class' => ActionColumn::className(),
            'template' => '{update} {delete}',
        ],
    ]
]);
?>
