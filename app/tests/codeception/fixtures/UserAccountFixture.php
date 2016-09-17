<?php
namespace tests\codeception\fixtures;

use Yii;
use yii\test\ActiveFixture;

/**
 * Users account fixtures
 */
class UserAccountFixture extends ActiveFixture
{
    public $modelClass = 'user\models\UserAccount';

    public $depends = [
        'tests\codeception\fixtures\UserFixture',
    ];

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [
            'activeUser1Git' => [
                'user_id' => 1,
                'type' => \user\models\UserAccount::TYPE_GIT,
                'username' => 'git user name',
            ],
            'activeUser1Hg' => [
                'user_id' => 1,
                'type' => \user\models\UserAccount::TYPE_HG,
                'username' => 'hg user name',
            ],
            'activeUser1Svn' => [
                'user_id' => 1,
                'type' => \user\models\UserAccount::TYPE_SVN,
                'username' => 'svn user name',
            ],
            'activeUser2Git' => [
                'user_id' => 2,
                'type' => \user\models\UserAccount::TYPE_GIT,
                'username' => 'git user name 2',
            ],
            'activeUser2Hg' => [
                'user_id' => 2,
                'type' => \user\models\UserAccount::TYPE_HG,
                'username' => 'hg user name 2',
            ],
            'activeUser2Svn' => [
                'user_id' => 2,
                'type' => \user\models\UserAccount::TYPE_SVN,
                'username' => 'svn user name 2',
            ],
        ];
    }
}
