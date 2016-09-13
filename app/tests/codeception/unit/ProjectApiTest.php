<?php

use project\models\Project;
use project\ProjectModule;
use svk\tests\StaticAppTestCase;
use tests\codeception\fixtures\ProjectFixture;
use tests\codeception\fixtures\UserFixture;
use user\models\User;

/**
 * Tests project helpers
 *
 * @method User users(string $userKey) Get user fixture
 * @method Project projects(string $projectKey) Get project fixture
 */
class ProjectApiTest extends StaticAppTestCase
{
    use svk\tests\StaticTransactionalTrait;

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var ProjectModule
     */
    protected $projectApi;

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'projects' => ProjectFixture::className(),
            'users' => UserFixture::className(),
        ];
    }

    public function setUp()
    {
        parent::setUp();
        $this->projectApi = Yii::$app->getModule('project');
    }

    public static function setUpBeforeClass()
    {
        self::beginStaticTransaction();
    }

    public static function tearDownAfterClass()
    {
        self::rollBackStaticTransaction();
    }

    /**
     * Test get project contributions array from date
     *
     * @return BaseCommit First commit from generator
     */
    public function testGetProjectContributions()
    {
        /* @var $project Project */
        $project = $this->projects('comitkaGitProject');

        $dateFrom = new DateTime();

        $dateFrom->setDate(2016, 1, 1);
        $dateFrom->setTime(0, 0, 0);

        $this->assertNotEmpty($this->projectApi->getProjectContributions($project, $dateFrom));
        $this->assertContainsOnly(VcsCommon\BaseCommit::className(), $this->projectApi->getProjectContributions($project, $dateFrom));

        // check if not less than date from
        $firstCommit = null;
        foreach ($this->projectApi->getProjectContributions($project, $dateFrom) as $commit) {
            $this->assertGreaterThan($dateFrom->getTimestamp(), $commit->getDate()->getTimestamp());
            if (is_null($firstCommit)) {
                $firstCommit = $commit;
            }
        }
        return $firstCommit;
    }

    /**
     * Test contribution review
     *
     * @depends testGetProjectContributions
     *
     * @param VcsCommon\BaseCommit $commit
     */
    public function testCreateContributionReview(VcsCommon\BaseCommit $commit)
    {
        /* @var $project Project */
        $project = $this->projects('comitkaGitProject');
        /* @var $reviewer User */
        $reviewer = $this->users('activeUser1');
        /* @var $contributor User */
        $contributor = $this->users('activeUser2');

        // create review without users
        $contributionReview = $this->projectApi->createContributionReview($project, $commit);
        $this->assertInstanceOf(\project\models\ContributionReview::className(), $contributionReview);
        $this->assertInstanceOf(Project::className(), $contributionReview->project);
        $this->assertEquals($contributionReview->project->id, $project->id);
        $this->assertNull($contributionReview->reviewer);
        $this->assertNull($contributionReview->contributor);

        // check unique
        $this->assertNull($this->projectApi->createContributionReview($project, $commit));

        // remove review
        $this->assertEquals(1, $contributionReview->delete());

        // create review with users
        $contributionReview = $this->projectApi->createContributionReview($project, $commit, $contributor->id, $reviewer->id);
        $this->assertInstanceOf(\project\models\ContributionReview::className(), $contributionReview);
        $this->assertInstanceOf(Project::className(), $contributionReview->project);
        $this->assertEquals($contributionReview->project->id, $project->id);
        $this->assertInstanceOf(User::className(), $contributionReview->reviewer);
        $this->assertEquals($contributionReview->reviewer->id, $reviewer->id);
        $this->assertInstanceOf(User::className(), $contributionReview->contributor);
        $this->assertEquals($contributionReview->contributor->id, $contributor->id);
    }
}
