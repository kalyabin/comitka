<?php

use user\models\User;
use user\models\UserAccountForm;
use yii\base\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model User */
/* @var $accounts UserAccountForm[] */
/* @var $successMessage string */
/* @var $errorMessage string */

$newAccount = new UserAccountForm();
?>
<div class="panel-body">
    <?php if ($successMessage):?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php elseif ($errorMessage):?>
        <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif;?>
    <?php if (!empty($accounts)):?>
        <?php
        $form = ActiveForm::begin([
            'action' => ['vcs-bindings', 'id' => $model->id],
            'options' => [
                'data' => [
                    'pjax' => 1,
                ],
            ],
            'id' => 'userAccounts' . $model->id,
        ]);
        ?>
        <div class="row col-md-12">
            <div class="form-group">
                <label class="control-label"><?= Yii::t('user', 'Username and VCS type') ?></label>
            </div>
        </div>
        <?php foreach ($accounts as $account):?>
        <div class="row js-binding-row" data-row-id="<?= $account->id ?>"<?php if ($account->deletionFlag):?> style="display:none;"<?php endif;?>>
            <div class="col-md-8">
                <?= $form->field($account, "[{$account->id}]username")->label(false)->textInput() ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($account, "[{$account->id}]type")->label(false)->dropDownList(UserAccountForm::getTypesForDropDown()) ?>
            </div>
            <div class="col-md-2">
                <?php
                $field = $form->field($account, "[{$account->id}]deletionFlag", [
                    'template' => '<div style="display:none;">{input}</div>{button}'
                ])->label(false)->checkbox();
                $field->parts['{button}'] = Html::button('<span class="glyphicon glyphicon-trash"></span>', [
                    'class' => 'btn btn-danger form-control js-remove-binding',
                    'data' => [
                        'row-id' => $account->id,
                    ]
                ]);
                print $field;
                ?>
            </div>
        </div>
        <div class="row col-md-12 js-binding-row-deleted" data-row-id="<?= $account->id ?>"<?php if (!$account->deletionFlag):?> style="display: none;"<?php endif;?>>
            <div class="form-group">
                <span class="glyphicon glyphicon-repeat"></span>
                <?= Yii::t('user', 'Username "{username}" was removed.', [
                    'username' => $account->username,
                ]) ?>
                <a href="#" class="js-binding-row-undo" data-row-id="<?= $account->id ?>"><?= Yii::t('user', 'Undo') ?></a>
            </div>
        </div>
        <?php endforeach;?>
        <?= Html::submitButton(Yii::t('user', 'Update'), [
            'class' => 'btn btn-primary',
            'name' => 'update',
            'value' => 1
        ]) ?>
        <?php ActiveForm::end(); ?>
    <?php endif;?>
</div>
<div class="panel-footer">
    <?php
    $form = ActiveForm::begin([
        'action' => ['vcs-bindings', 'id' => $model->id],
        'options' => [
            'data' => [
                'pjax' => 1,
            ],
        ],
        'id' => 'newAccount' . $model->id,
    ]);
    ?>
    <div class="row col-md-12">
        <div class="form-group">
            <label class="control-label"><?= Yii::t('user', 'Add new binding: type username and VCS type') ?></label>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <?= $form->field($newAccount, "username")->label(false)->textInput() ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($newAccount, "type")->label(false)->dropDownList(UserAccountForm::getTypesForDropDown()) ?>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('user', 'Add'), [
                    'class' => 'btn btn-primary form-control',
                    'name' => 'add-new',
                    'value' => 1
                ]) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
