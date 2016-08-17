<?php
namespace user\widgets;

use user\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\bootstrap\Nav;

/**
 * Widget to represent menu for current user pages.
 */
class ProfileMenu extends Nav
{
    /**
     * @var User Currently authorized user
     */
    public $authUser;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->authUser instanceof User) {
            throw new InvalidParamException('AuthUser must be an instance of ' . User::className());
        }

        $this->items = [
            [
                'url' => ['/user/profile/index'],
                'label' => Yii::t('user', 'Common settings and password'),
            ],
            [
                'url' => ['/user/profile/vcs-bindings'],
                'label' => Yii::t('user', 'VCS bindings'),
            ],
        ];

        parent::init();
    }
}
