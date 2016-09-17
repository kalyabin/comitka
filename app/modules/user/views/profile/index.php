<?php

use user\models\ChangePasswordForm;
use user\models\UserForm;
use user\widgets\ProfileMenu;
use app\widgets\ContributorAvatar;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $profileForm UserForm */
/* @var $changePasswordForm ChangePasswordForm */

$this->title = Yii::t('user', 'Your profile');
?>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('user', 'Common settings') ?></div>
    <?php
    $form = ActiveForm::begin([
        'id' => $profileForm->formName(),
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validateOnBlur' => false,
        'options' => [
            'enctype' => 'multipart/form-data',
        ]
    ]);
    ?>
    <div class="panel-body">
        <?= $form->field($profileForm, 'email')->staticControl() ?>
        <?= $form->field($profileForm, 'name')->textInput() ?>
        <?php if (($avatarUrl = $profileForm->getAvatarUrl()) !== false):?>
            <?php
            $img = ContributorAvatar::widget([
                'contributor' => $profileForm,
                'size' => 'normal',
                'asBlock' => true,
            ]);
            $deleteField = $form->field($profileForm, 'deleteAvatar')->checkbox();

            $field = $form->field($profileForm, 'uploadedAvatar', [
                'template' => '{label}{img}{checkbox}{input}{error}',
            ])->fileInput();
            $field->parts['{img}'] = $img;
            $field->parts['{checkbox}'] = $deleteField;

            echo $field;
            ?>
        <?php else:?>
            <?= $form->field($profileForm, 'uploadedAvatar')->fileInput() ?>
        <?php endif;?>
    </div>
    <div class="panel-footer">
        <?= Html::submitButton(Yii::t('user', 'Change'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('user', 'Change password') ?></div>
    <?php
    $form = ActiveForm::begin([
        'id' => $changePasswordForm->formName(),
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validateOnBlur' => false,
    ]);
    ?>
    <div class="panel-body">
        <?= $form->field($changePasswordForm, 'password')->passwordInput() ?>
        <?= $form->field($changePasswordForm, 'confirmPassword')->passwordInput() ?>
    </div>
    <div class="panel-footer">
        <?= Html::submitButton(Yii::t('user', 'Change'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$this->blocks['left_block'] = ProfileMenu::widget([
    'authUser' => Yii::$app->user->identity,
    'options' => [
        'class' => 'nav nav-pills nav-stacked',
    ],
]);
?>
