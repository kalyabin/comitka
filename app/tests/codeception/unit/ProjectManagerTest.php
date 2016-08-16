<?php

use GitView\Repository as GitRepository;
use HgView\Repository as HgRepository;
use project\models\Project;
use svk\tests\StaticAppTestCase;

/**
 * Tests project: create, update, get repository instance and delete
 */
class ProjectManagerTest extends StaticAppTestCase
{
    use svk\tests\StaticTransactionalTrait;

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var string path to current repository
     */
    protected static $projectPath;

    public static function setUpBeforeClass()
    {
        self::beginStaticTransaction();

        self::$projectPath = dirname(YII_APP_BASE_PATH);
    }

    public static function tearDownAfterClass()
    {
        self::rollBackStaticTransaction();
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

        $model->repo_path = '/root/';
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
        $this->assertFalse($model->isNewRecord);

        return $model;
    }

    /**
     * Test update project
     *
     * @depends testCreateProject
     * @param Project $project
     * @return Project
     */
    public function testUpdateProject(Project $project)
    {
        /* @var $model Project */
        $model = Project::findOne($project->id);
        $this->assertInstanceOf(Project::className(), $model);

        $model->title = 'New repo title';
        $this->assertTrue($model->validate());
        $this->assertTrue($model->save());

        return $model;
    }

    /**
     * Tests get repository object
     *
     * @depends testUpdateProject
     * @param Project $project
     * @return Project
     */
    public function testGetRepository(Project $project)
    {
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
     * @param Project $model
     */
    public function testRemoveProject(Project $model)
    {
        $result = $model->delete();
        $this->assertEquals(1, $result);
    }
}
