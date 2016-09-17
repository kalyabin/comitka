<?php
namespace tests\codeception\fixtures;

use Yii;
use yii\test\ActiveFixture;

/**
 * Users fixtures
 */
class UserFixture extends ActiveFixture
{
    public $modelClass = 'user\models\User';

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [
            'activeUser1' => [
                'id' => 1,
                'name' => 'User1 Active',
                'email' => 'user1@active.ru',
                'password' => Yii::$app->getModule('user')->getPasswordHash('password_active_user_1'),
                'status' => 1,
                'avatar' => null,
            ],
            'activeUser2' => [
                'id' => 2,
                'name' => 'User2 Active',
                'email' => 'user2@active.ru',
                'password' => Yii::$app->getModule('user')->getPasswordHash('password_active_user_2'),
                'status' => 1,
                'avatar' => null,
            ]
        ];
    }
}
