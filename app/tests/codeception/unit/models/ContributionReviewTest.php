<?php
namespace models;

use Codeception\Test\Unit;
use project\models\ContributionReview;
use project\models\Project;
use tests\codeception\fixtures\ProjectFixture;
use tests\codeception\fixtures\UserFixture;
use UnitTester;
use user\models\User;
use Yii;

/**
 * Test ContributionReview model
 */
class ContributionReviewTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * Tests fixtures
     */
    public function fixtures()
    {
        return [
            'users' => UserFixture::className(),
            'projects' => ProjectFixture::className(),
        ];
    }

    /**
     * Test model validation and save
     */
    public function testValidationSaveAndDelete()
    {
        $model = new ContributionReview();

        $attributes = [
            'commit_id' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => ['wrong string'],
                    'isValid' => false,
                ],
                [
                    'value' => str_repeat('A', 41),
                    'isValid' => false,
                ],
                [
                    'value' => 1,
                    'isValid' => false,
                ],
                [
                    'value' => md5(uniqid()),
                    'isValid' => true,
                ],
            ],
            'project_id' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => ['wrong string'],
                    'isValid' => false,
                ],
                [
                    'value' => 'string',
                    'isValid' => false,
                ],
                [
                    'value' => $this->getModule('Yii2')->grabFixture('projects', 'comitkaGitProject')->id,
                    'isValid' => true,
                ]
            ],
            'date' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => ['wrong string'],
                    'isValid' => false,
                ],
                [
                    'value' => 'string',
                    'isValid' => false,
                ],
                [
                    'value' => 1,
                    'isValid' => false,
                ],
                [
                    'value' => date('Y-m-d H:i:s'),
                    'isValid' => true,
                ],
            ],
            'contributor_id' => [
                [
                    'value' => null,
                    'isValid' => true,
                ],
                [
                    'value' => [],
                    'isValid' => true,
                ],
                [
                    'value' => '',
                    'isValid' => true,
                ],
                [
                    'value' => 'wrong integer',
                    'isValid' => false,
                ],
                [
                    'value' => ['wrong integer'],
                    'isValid' => false,
                ],
                [
                    'value' => $this->getModule('Yii2')->grabFixture('users', 'activeUser1')->id,
                    'isValid' => true,
                ],
            ],
            'reviewer_id' => [
                [
                    'value' => null,
                    'isValid' => true,
                ],
                [
                    'value' => [],
                    'isValid' => true,
                ],
                [
                    'value' => '',
                    'isValid' => true,
                ],
                [
                    'value' => 'wrong integer',
                    'isValid' => false,
                ],
                [
                    'value' => ['wrong integer'],
                    'isValid' => false,
                ],
                [
                    'value' => $this->getModule('Yii2')->grabFixture('users', 'activeUser2')->id,
                    'isValid' => true,
                ],
            ],
            'message' => [
                [
                    'value' => null,
                    'isValid' => true,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => true,
                ],
                [
                    'value' => ['wrong string'],
                    'isValid' => false,
                ],
                [
                    'value' => 'test message',
                    'isValid' => true,
                ]
            ],
            'contributor_email' => [
                [
                    'value' => null,
                    'isValid' => true,
                ],
                [
                    'value' => [],
                    'isValid' => true,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => ['wrong string'],
                    'isValid' => false,
                ],
                [
                    'value' => 'test contributor',
                    'isValid' => true,
                ]
            ],
            'contributor_name' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => '',
                    'isValid' => false,
                ],
                [
                    'value' => ['wrong string'],
                    'isValid' => false,
                ],
                [
                    'value' => 'test contributor',
                    'isValid' => true,
                ],
            ],
            'repo_type' => [
                [
                    'value' => null,
                    'isValid' => false,
                ],
                [
                    'value' => [],
                    'isValid' => false,
                ],
                [
                    'value' => 0,
                    'isValid' => false,
                ],
                [
                    'value' => '',
                    'isValid' => false,
                ],
                [
                    'value' => ['wrong string'],
                    'isValid' => false,
                ],
                [
                    'value' => 'wrong repo',
                    'isValid' => false,
                ],
                [
                    'value' => 'svn',
                    'isValid' => true,
                ],
                [
                    'value' => 'git',
                    'isValid' => true,
                ],
                [
                    'value' => 'hg',
                    'isValid' => true,
                ],
            ],
            'reviewed' => [
                [
                    'value' => null,
                    'isValid' => true,
                ],
                [
                    'value' => [],
                    'isValid' => true,
                ],
                [
                    'value' => ['wrong string'],
                    'isValid' => false,
                ],
                [
                    'value' => 'string',
                    'isValid' => false,
                ],
                [
                    'value' => 1,
                    'isValid' => false,
                ],
                [
                    'value' => date('Y-m-d H:i:s'),
                    'isValid' => true,
                ],
            ],
        ];

        $this->getModule('\Helper\Unit')->validateModelAttributes($model, $attributes, $this);

        $this->assertTrue($model->validate());
        $this->assertTrue($model->save());

        $this->assertInstanceOf(Project::className(), $model->project);
        $this->assertEquals($model->project->id, $this->getModule('Yii2')->grabFixture('projects', 'comitkaGitProject')->id);

        $this->assertInstanceOf(User::className(), $model->contributor);
        $this->assertEquals($model->contributor->id, $this->getModule('Yii2')->grabFixture('users', 'activeUser1')->id);

        $this->assertInstanceOf(User::className(), $model->reviewer);
        $this->assertEquals($model->reviewer->id, $this->getModule('Yii2')->grabFixture('users', 'activeUser2')->id);

        // test unique model
        $attributes = $model->getAttributes();

        unset ($attributes['id']);

        $newModel = new ContributionReview();
        $newModel->setAttributes($attributes);

        $this->assertFalse($newModel->validate());

        $this->assertArrayHasKey('commit_id', $newModel->getErrors());

        // delete test
        $this->assertEquals(1, $model->delete());
    }
}
