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
            'gitProject' => [
                'title' => 'Comitka',
                'repo_type' => 'git',
                'repo_path' => GIT_PROJECT_PATH,
            ],
        ];
    }
}
