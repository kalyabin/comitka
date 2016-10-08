<?php
namespace user\widgets;

use user\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\bootstrap\Nav;

/**
 * Widget to represent menu for user's pages.
 *
 * A user's pages is not a self-profile pages.
 */
class UserMenu extends Nav
{
    /**
     * @var User The user's model to form a menu
     */
    public $model;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->model instanceof User) {
            throw new InvalidParamException('Model must be an instance of ' . User::className());
        }

        $this->items = [
            [
                'url' => ['/user/user-manager/update', 'id' => $this->model->id],
                'label' => Yii::t('user', 'Common settings'),
            ],
            [
                'url' => ['/user/user-manager/vcs-bindings', 'id' => $this->model->id],
                'label' => Yii::t('user', 'VCS bindings'),
            ],
        ];

        parent::init();
    }
}
