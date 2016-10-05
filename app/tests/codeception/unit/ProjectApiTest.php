<?php

use app\models\ContributorInterface;
use app\models\UnregisteredContributor;
use Codeception\Test\Unit;
use project\models\ContributionReview;
use project\models\Project;
use project\ProjectModule;
use tests\codeception\fixtures\ProjectFixture;
use tests\codeception\fixtures\UserFixture;
use user\models\User;
use VcsCommon\BaseCommit;

/**
 * Tests project helpers
 */
class ProjectApiTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var ProjectModule
     */
    protected $projectApi;

    /**
     * Tests fixtures
     */
    public function _fixtures()
    {
        return [
            'projects' => ProjectFixture::className(),
            'users' => UserFixture::className(),
        ];
    }

    public function setUp()
    {
        $this->projectApi = Yii::$app->getModule('project');
        parent::setUp();
    }

    /**
     * Test get project contributions array from date
     *
     * @return BaseCommit First commit from generator
     */
    public function testGetProjectContributions()
    {
        /* @var $project Project */
        $project = $this->getModule('Yii2')->grabFixture('projects', 'comitkaGitProject');

        $dateFrom = new DateTime();

        $dateFrom->setDate(2016, 1, 1);
        $dateFrom->setTime(0, 0, 0);

        $this->assertNotEmpty($this->projectApi->getProjectContributions($project, $dateFrom));
        $this->assertContainsOnly(BaseCommit::className(), $this->projectApi->getProjectContributions($project, $dateFrom));

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
     * @param BaseCommit $commit
     */
    public function testCreateContributionReview(BaseCommit $commit)
    {
        /* @var $project Project */
        $project = $this->getModule('Yii2')->grabFixture('projects', 'comitkaGitProject');
        /* @var $reviewer User */
        $reviewer = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');
        /* @var $contributor User */
        $contributor = $this->getModule('Yii2')->grabFixture('users', 'activeUser2');

        // create review without users
        $contributionReview = $this->projectApi->createContributionReview($project, $commit, new UnregisteredContributor());
        $this->assertInstanceOf(ContributionReview::className(), $contributionReview);
        $this->assertInstanceOf(Project::className(), $contributionReview->project);
        $this->assertEquals($contributionReview->project->id, $project->id);
        $this->assertNull($contributionReview->reviewer);
        $this->assertInstanceOf(UnregisteredContributor::className(), $contributionReview->contributor);

        // check unique
        $this->assertNull($this->projectApi->createContributionReview($project, $commit, new UnregisteredContributor()));

        // remove review
        $this->assertEquals(1, $contributionReview->delete());

        // create review with users
        $contributionReview = $this->projectApi->createContributionReview($project, $commit, $contributor);
        $this->assertInstanceOf(ContributionReview::className(), $contributionReview);
        $this->assertInstanceOf(Project::className(), $contributionReview->project);
        $this->assertEquals($contributionReview->project->id, $project->id);
        $this->assertNull($contributionReview->reviewer);
        $this->assertInstanceOf(ContributorInterface::class, $contributionReview->contributor);
        $this->assertEquals($contributionReview->contributor->id, $contributor->id);

        $this->assertEquals(1, $contributionReview->delete());

        $contributionReview = $this->projectApi->createContributionReview($project, $commit, $contributor, $reviewer);
        $this->assertInstanceOf(ContributionReview::className(), $contributionReview);
        $this->assertInstanceOf(Project::className(), $contributionReview->project);
        $this->assertEquals($contributionReview->project->id, $project->id);
        $this->assertInstanceOf(ContributorInterface::class, $contributionReview->reviewer);
        $this->assertEquals($contributionReview->reviewer->id, $reviewer->id);
        $this->assertInstanceOf(ContributorInterface::class, $contributionReview->contributor);
        $this->assertEquals($contributionReview->contributor->id, $contributor->id);
    }
}
