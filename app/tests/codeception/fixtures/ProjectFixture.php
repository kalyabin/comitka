<?php
namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;

/**
 * Projects fixture
 */
class ProjectFixture extends ActiveFixture
{
    public $modelClass = 'project\models\Project';

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [
            'comitkaGitProject' => [
                'title' => 'Comitka',
                'repo_type' => 'git',
                'repo_path' => require YII_APP_BASE_PATH . '/../vendor/kalyabin/yii2-git-view/tests/create_repository.php',
            ],
        ];
    }
}
