<?php

namespace widgets;

use app\components\ContributorApi;
use project\models\ContributionReview;
use project\models\Project;
use project\ProjectModule;
use project\widgets\CommitPanel;
use tests\codeception\fixtures\ProjectFixture;
use tests\codeception\fixtures\UserFixture;
use UnitWebTestCase;
use user\components\Auth;
use user\models\User;
use VcsCommon\BaseCommit;
use Yii;
use yii\helpers\Html;

/**
 * Test project panel widget
 */
class CommitPanelTest extends UnitWebTestCase
{
    /**
     * @var ContributorApi
     */
    protected $contributorApi;

    /**
     * @var ProjectModule
     */
    protected $projectModule;

    /**
     * Fixtures
     *
     * @return array
     */
    public function _fixtures()
    {
        return [
            'users' => UserFixture::className(),
            'projects' => ProjectFixture::className(),
        ];
    }

    protected function setUp()
    {
        parent::setUp();
        $this->contributorApi = Yii::$app->contributors;
        $this->projectModule = Yii::$app->getModule('project');
    }

    /**
     * Prepare testing data and returns it
     *
     * @return array
     */
    protected function prepareFixtures()
    {
        /* @var $contributor User */
        $contributor = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');
        /* @var $reviewer User */
        $reviewer = $this->getModule('Yii2')->grabFixture('users', 'activeUser2');
        // authorized user model
        $authModel = new Auth([
            'identityClass' => User::className(),
            'identity' => $reviewer,
        ]);
        /* @var $project Project */
        $project = $this->getModule('Yii2')->grabFixture('projects', 'gitProject');

        /* @var $history BaseCommit[] */
        $history = $project->getRepositoryObject()->getHistory(1, 0);
        $this->assertNotEmpty($history);
        $commit = $history[0];
        $this->assertInstanceOf(BaseCommit::className(), $commit);

        return [$contributor, $reviewer, $authModel, $project, $commit];
    }

    /**
     * Test commit id and parents id
     */
    public function testHeadString()
    {
        list($contributor, $reviewer, $authModel, $project, $commit) = $this->prepareFixtures();

        /* @var $contributor User */
        /* @var $reviewer User */
        /* @var $authModel Auth */
        /* @var $project Project */
        /* @var $commit BaseCommit */

        $result = CommitPanel::widget([
            'project' => $project,
            'commit' => $commit,
            'contributor' => $this->contributorApi->getContributor(
                $project->repo_type,
                $commit->contributorName,
                $commit->contributorEmail
            ),
            'authUser' => $authModel,
            'reviewModel' => null,
        ]);

        // check commitid
        $this->assertContains('<strong>' . $commit->getId() . '</strong>', $result);

        // check parents
        foreach ($commit->getParentsId() as $parentId) {
            $expectedString = '<a href="/projects/' . $project->id . '/revisions/' . $parentId . '/summary">' . $parentId . '</a>';
            $this->assertContains($expectedString, $result);
        }
    }

    /**
     * Test widget with not registered contributor
     *
     * @depends testHeadString
     */
    public function testWithoutContributor()
    {
        list($contributor, $reviewer, $authModel, $project, $commit) = $this->prepareFixtures();

        /* @var $contributor User */
        /* @var $reviewer User */
        /* @var $authModel Auth */
        /* @var $project Project */
        /* @var $commit BaseCommit */

        $result = CommitPanel::widget([
            'project' => $project,
            'commit' => $commit,
            'contributor' => $this->contributorApi->getContributor(
                $project->repo_type,
                $commit->contributorName,
                $commit->contributorEmail
            ),
            'authUser' => $authModel,
            'reviewModel' => null,
        ]);

        // check commiter name
        $expectedString = $commit->contributorName . ' '
                . '&lt;' . $commit->contributorEmail . '&gt; '
                . 'at ' . Html::encode($commit->getDate()->format("d\'M y H:i:s"));
        $this->assertContains($expectedString, $result);

        // check has no contributor name
        $unexpectedString = $contributor->name;
        $this->assertNotContains($unexpectedString, $result);
        $this->assertContains('Not registered', $result);
    }

    /**
     * Test panel with contributor
     *
     * @depends testWithoutContributor
     */
    public function testWithContributor()
    {
        list($contributor, $reviewer, $authModel, $project, $commit) = $this->prepareFixtures();

        /* @var $contributor User */
        /* @var $reviewer User */
        /* @var $authModel Auth */
        /* @var $project Project */
        /* @var $commit BaseCommit */

        $result = CommitPanel::widget([
            'project' => $project,
            'commit' => $commit,
            'contributor' => $contributor,
            'authUser' => $authModel,
            'reviewModel' => null,
        ]);

        // check commiter name
        $expectedString = $commit->contributorName . ' '
                . '&lt;' . $commit->contributorEmail . '&gt; '
                . 'at ' . Html::encode($commit->getDate()->format("d\'M y H:i:s"));
        $this->assertContains($expectedString, $result);

        // check contributor name
        $this->assertContains($contributor->name, $result);
    }

    /**
     * Test panel without review model
     *
     * @depends testWithContributor
     */
    public function testWithoutReview()
    {
        list($contributor, $reviewer, $authModel, $project, $commit) = $this->prepareFixtures();

        /* @var $contributor User */
        /* @var $reviewer User */
        /* @var $authModel Auth */
        /* @var $project Project */
        /* @var $commit BaseCommit */

        $result = CommitPanel::widget([
            'project' => $project,
            'commit' => $commit,
            'contributor' => $contributor,
            'authUser' => $authModel,
            'reviewModel' => null,
        ]);

        // not contains review model and finish review button
        $this->assertNotContains($reviewer->name, $result);
        $this->assertNotContains('did not complete a review', $result);

        $expectedString = '/projects/' . $project->id . '/' . $commit->getId() . '/finish-review';
        $this->assertNotContains($expectedString, $result);
        $this->assertNotContains('Finish review', $result);

        // contains to be a reviewer button
        $this->assertContains('(has no review)', $result);

        // has to be review button
        $expectedString = '/projects/' . $project->id . '/' . $commit->getId() . '/create-self-review';
        $this->assertContains($expectedString, $result);
        $this->assertContains('To be a reviewer', $result);
    }

    /**
     * Test panel with not finished review model
     *
     * @depends testWithoutReview
     */
    public function testWithReview()
    {
        list($contributor, $reviewer, $authModel, $project, $commit) = $this->prepareFixtures();

        $reviewModel = $this->projectModule->createContributionReview($project, $commit, $contributor, $reviewer);
        $this->assertInstanceOf(ContributionReview::className(), $reviewModel);

        /* @var $contributor User */
        /* @var $reviewer User */
        /* @var $authModel Auth */
        /* @var $project Project */
        /* @var $commit BaseCommit */

        $result = CommitPanel::widget([
            'project' => $project,
            'commit' => $commit,
            'contributor' => $contributor,
            'authUser' => $authModel,
            'reviewModel' => $reviewModel,
        ]);

        // contains review model
        $this->assertContains($reviewer->name, $result);
        $this->assertContains('did not complete a review', $result);

        $expectedString = '/projects/' . $project->id . '/' . $commit->getId() . '/finish-review';
        $this->assertContains($expectedString, $result);
        $this->assertContains('Finish review', $result);

        // not contains to be a reviewer button
        $this->assertNotContains('(has no review)', $result);

        // has to be review button
        $expectedString = '/projects/' . $project->id . '/' . $commit->getId() . '/create-self-review';
        $this->assertNotContains($expectedString, $result);
        $this->assertNotContains('To be a reviewer', $result);
    }

    /**
     * Test panel with finished review model
     *
     * @depends testWithReview
     */
    public function testWithFinishedReview()
    {
        list($contributor, $reviewer, $authModel, $project, $commit) = $this->prepareFixtures();

        $reviewModel = $this->projectModule->createContributionReview($project, $commit, $contributor, $reviewer);
        $this->assertInstanceOf(ContributionReview::className(), $reviewModel);
        $this->assertTrue($reviewModel->finishReview());

        /* @var $contributor User */
        /* @var $reviewer User */
        /* @var $authModel Auth */
        /* @var $project Project */
        /* @var $commit BaseCommit */

        $result = CommitPanel::widget([
            'project' => $project,
            'commit' => $commit,
            'contributor' => $contributor,
            'authUser' => $authModel,
            'reviewModel' => $reviewModel,
        ]);

        // contains review model but not contains finish review button
        $this->assertContains($reviewer->name, $result);
        $this->assertNotContains('did not complete a review', $result);

        $expectedString = '/projects/' . $project->id . '/' . $commit->getId() . '/finish-review';
        $this->assertNotContains($expectedString, $result);
        $this->assertNotContains('Finish review', $result);

        // not contains to be a reviewer button
        $this->assertNotContains('(has no review)', $result);

        // has to be review button
        $expectedString = '/projects/' . $project->id . '/' . $commit->getId() . '/create-self-review';
        $this->assertNotContains($expectedString, $result);
        $this->assertNotContains('To be a reviewer', $result);

        // contains finish review date
        $expectedString = 'at ' . $reviewModel->getReviewedDateTime()->format("d\'M y H:i:s");
        $this->assertContains($expectedString, $result);
    }

}
