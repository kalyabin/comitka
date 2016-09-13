<?php
namespace models;

use project\models\ContributionReview;
use svk\tests\StaticAppTestCase;
use UnitTester;
use user\models\User;
use project\models\Project;

/**
 * Test ContributionReview model
 *
 * @method User users(string $userKey) Get user fixture
 * @method Project projects(string $projectKey) Get project fixture
 */
class ContributionReviewTest extends StaticAppTestCase
{
    use \svk\tests\StaticTransactionalTrait;
    use \svk\tests\ModelTestTrait;

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'users' => \tests\codeception\fixtures\UserFixture::className(),
            'projects' => \tests\codeception\fixtures\ProjectFixture::className(),
        ];
    }

    public static function setUpBeforeClass()
    {
        self::beginStaticTransaction();
    }

    public static function tearDownAfterClass()
    {
        self::rollBackStaticTransaction();
    }

    public function setUp()
    {
        static $fixturesLoaded = false;

        if (!$fixturesLoaded) {
            parent::setUp();
            $fixturesLoaded = true;
        }
    }

    public function tearDown()
    {

    }

    /**
     * Test model validation and save
     *
     * @return ContributionReview
     */
    public function testValidationAndSave()
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
                    'value' => $this->projects('comitkaGitProject')->id,
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
                    'value' => $this->users('activeUser1')->id,
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
                    'value' => $this->users('activeUser2')->id,
                    'isValid' => true,
                ],
            ],
        ];

        $this->validateAttributes($model, $attributes);

        $this->assertTrue($model->validate());
        $this->assertTrue($model->save());

        $this->assertInstanceOf(\project\models\Project::className(), $model->project);
        $this->assertEquals($model->project->id, $this->projects('comitkaGitProject')->id);

        $this->assertInstanceOf(\user\models\User::className(), $model->contributor);
        $this->assertEquals($model->contributor->id, $this->users('activeUser1')->id);

        $this->assertInstanceOf(\user\models\User::className(), $model->reviewer);
        $this->assertEquals($model->reviewer->id, $this->users('activeUser2')->id);

        return $model;
    }

    /**
     * Tests unique model
     *
     * @depends testValidationAndSave
     *
     * @param ContributionReview $model
     */
    public function testUniqueModel(ContributionReview $model)
    {
        $attributes = $model->getAttributes();

        unset ($attributes['id']);

        $newModel = new ContributionReview();
        $newModel->setAttributes($attributes);

        $this->assertFalse($newModel->validate());

        $this->assertArrayHasKey('commit_id', $newModel->getErrors());

        return $model;
    }

    /**
     * Tests deletion model
     *
     * @depends testUniqueModel
     *
     * @param ContributionReview $model
     */
    public function testDeleteModel(ContributionReview $model)
    {
        $this->assertEquals(1, $model->delete());
    }
}
