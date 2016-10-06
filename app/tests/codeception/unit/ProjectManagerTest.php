<?php

use Codeception\Test\Unit;
use GitView\Repository as GitRepository;
use HgView\Repository as HgRepository;
use project\models\Project;
use tests\codeception\fixtures\ProjectFixture;

/**
 * Tests project: create, update, get repository instance and delete
 */
class ProjectManagerTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * Tests fixtures
     */
    public function _fixtures()
    {
        return [
            'projects' => ProjectFixture::className(),
        ];
    }

    /**
     * Check repo path validation
     *
     * @param Project $model Validation model
     * @param string $repoType From Project::REPO_* constants
     */
    protected function checkRepoPath(Project $model, $repoType)
    {
        $model->repo_path = null;
        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('repo_path', $model->getErrors(), 'Check repo_path validation');

        $model->repo_path = substr_compare(PHP_OS, 'win', 0, 3, true) === 0 ? 'C:\\' : '/root/';
        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('repo_path', $model->getErrors(), 'Check repo_path validation');

        $hgRepoPath = Yii::$app->params['testingVariables']['hgProjectPath'];
        $gitRepoPath = Yii::$app->params['testingVariables']['gitProjectPath'];

        $invalidRepos = [
            Project::REPO_GIT => [$hgRepoPath],
            Project::REPO_HG => [$gitRepoPath],
        ];

        $validRepos = [
            Project::REPO_GIT => $gitRepoPath,
            Project::REPO_HG => $hgRepoPath,
        ];

        // first check invalid repos
        $model->repo_type = $repoType;
        foreach ($invalidRepos[$repoType] as $repoPath) {
            $model->repo_path = $repoPath;
            $this->assertFalse($model->validate());
            $this->assertArrayHasKey('repo_type', $model->getErrors(), 'Check repo_type validation');

            $model->repo_path = $repoPath . '/.gitignore';
            $this->assertFalse($model->validate());
            $this->assertArrayHasKey('repo_path', $model->getErrors(), 'Check repo_path validation');

            $model->repo_path = $repoPath . '/.hgignore';
            $this->assertFalse($model->validate());
            $this->assertArrayHasKey('repo_path', $model->getErrors(), 'Check repo_path validation');
        }

        $model->repo_path = $validRepos[$repoType];
        $this->assertTrue($model->validate());
    }

    /**
     * Check create a project
     *
     * @return Project
     */
    public function testCreateProject()
    {
        $model = new Project();
        $this->assertFalse($model->validate(), 'Check error validation');

        // check title validation
        $this->assertArrayHasKey('title', $model->getErrors(), 'Check title error validation');
        $model->title = str_repeat('ttt', Project::MAX_TITLE_LENGTH);
        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('title', $model->getErrors(), 'Check title error validation');
        $model->title = 'Test repo';
        $this->assertFalse($model->validate());
        $this->assertArrayNotHasKey('title', $model->getErrors());

        $this->checkRepoPath($model, Project::REPO_HG);
        $this->checkRepoPath($model, Project::REPO_GIT);

        $this->assertTrue($model->save());

        return $model;
    }

    /**
     * Test update project
     *
     * @depends testCreateProject
     *
     * @return Project
     */
    public function testUpdateProject()
    {
        /* @var $model Project */
        $model = $this->getModule('Yii2')->grabFixture('projects', 'comitkaGitProject');

        $model->title = 'New repo title';
        $this->assertTrue($model->validate(), 'Errors: ' . print_r($model->getErrors(), true), 'Attributes: ' . print_r($model->getAttributes(), true));
        $this->assertTrue($model->save());

        return $model;
    }

    /**
     * Tests get repository object
     *
     * @depends testUpdateProject
     *
     * @return Project
     */
    public function testGetRepository()
    {
        $project = $this->getModule('Yii2')->grabFixture('projects', 'comitkaGitProject');

        // change repo path to HG first
        $project->repo_path = Yii::$app->params['testingVariables']['hgProjectPath'];
        $project->repo_type = Project::REPO_HG;

        $this->assertInstanceOf(HgRepository::className(), $project->getRepositoryObject(), 'Check get HG repository object');

        // change repo path to GIT
        $project->repo_path = Yii::$app->params['testingVariables']['gitProjectPath'];
        $project->repo_type = Project::REPO_GIT;

        $this->assertInstanceOf(GitRepository::className(), $project->getRepositoryObject(), 'Check get GIT repository object');

        return $project;
    }

    /**
     * Tests remove project
     *
     * @depends testGetRepository
     */
    public function testRemoveProject()
    {
        $model = $this->getModule('Yii2')->grabFixture('projects', 'comitkaGitProject');
        $result = $model->delete();
        $this->assertEquals(1, $result);
    }
}
