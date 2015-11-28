<?php

use app\assets\CommonAsset;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $content string */

CommonAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <?php
    NavBar::begin([
        'brandLabel' => 'Comitka',
        'brandUrl' => Yii::$app->urlManager->getHostInfo(),
        'options' => [
            'class' => 'navbar navbar-inverse navbar-fixed-top',
        ],
    ]);
    $navItems = [];
    if (Yii::$app->user->can('createUser') || Yii::$app->user->can('updateUser') || Yii::$app->user->can('deleteUser')) {
        $navItems[] = [
            'label' => Yii::t('user', 'Users'),
            'url' => ['/user/manager/index'],
        ];
    }
    print Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-left'],
        'items' => $navItems,
    ]);
    $authItems = [];
    if (Yii::$app->user->isGuest) {
        $authItems[] = ['label' => Yii::t('user', 'Sign in'), 'url' => ['/user/auth/sign-in']];
    }
    else {
        $authItems[] = [
            'label' => Html::tag('span', '', ['class' => 'glyphicon glyphicon-user']) .
                '&nbsp;&nbsp;' . Yii::$app->user->identity->getUserName() . '',
            'url' => Url::to(['/user/profile/index']),
        ];
        $authItems[] = [
            'label' => Html::tag('small', Yii::t('user', 'Sign out')),
            'url' => ['/user/auth/sign-out'],
            'linkOptions' => ['data-method' => 'post'],
        ];
    }
    print Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'encodeLabels' => false,
        'items' => $authItems,
    ]);
    NavBar::end();
    ?>

    <div class="container theme-showcase" role="main">
        <?=$content?>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy; <a href="http://github.com/kalyabin/comitka">Comitka</a> <?= date('Y') ?></p>

            <p class="pull-right"><?=Yii::powered()?></p>
        </div>
    </footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>