<?php

use user\models\UserForm;
use yii\bootstrap\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model UserForm */
/* @var $form ActiveForm */

$form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]);
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('user', 'Common settings') ?>
    </div>
    <div class="panel-body">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        <?php if (!$model->isNewRecord):?>
            <?= $form->field($model, 'newPassword')->passwordInput() ?>
            <?= $form->field($model, 'newPasswordConfirmation')->passwordInput() ?>
            <?= $form->field($model, 'generateRandomPassword')->checkbox() ?>
        <?php endif;?>
        <?= $form->field($model, 'sendNotification')->checkbox() ?>
    </div>
    <div class="panel-heading">
        <?= Yii::t('user', 'Permissions') ?>
    </div>
    <div class="panel-body">
        <?= $form->field($model, 'roles')->checkboxList($model->getRolesList(), [
            'itemOptions' => [
                'labelOptions' => ['style' => 'display:block;'],
            ]
        ]) ?>
    </div>
    <div class="panel-footer">
        <?php if ($model->isNewRecord):?>
            <?= Html::submitButton(Yii::t('user', 'Create'), ['class' => 'btn btn-primary']) ?>
        <?php else:?>
            <?php if (Yii::$app->user->can('updateUser')):?>
                <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-primary']) ?>
            <?php endif;?>
            <?php if ($model->id != Yii::$app->user->getId() && Yii::$app->user->can('deleteUser') && $model->canSignIn()):?>
                <?= Html::a(Yii::t('user', 'Lock user'), ['lock', 'id' => $model->id], ['class' => 'btn btn-danger']) ?>
            <?php elseif ($model->id != Yii::$app->user->getId() && Yii::$app->user->can('deleteUser') && !$model->canSignIn()):?>
                <?= Html::a(Yii::t('user', 'Activate user'), ['activate', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
            <?php endif;?>
        <?php endif;?>
        <?= Html::a(Yii::t('user', 'Cancel'), ['index'], ['class' => 'btn btn-default']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
