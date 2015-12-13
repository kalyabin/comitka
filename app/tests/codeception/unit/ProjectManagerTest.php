<?php

use Codeception\TestCase\Test;
use GitView\Commit;
use GitView\Repository;
use project\models\Project;
use VcsCommon\Graph;

/**
 * Tests project: create, update, get repository instance and delete
 */
class ProjectManagerTest extends Test
{
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
        self::$projectPath = dirname(YII_APP_BASE_PATH);

        Project::deleteAll([
            'repo_path' => self::$projectPath,
        ]);
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

        // check repo path
        $this->assertArrayHasKey('repo_path', $model->getErrors(), 'Check repo path error validation');
        $model->repo_path = self::$projectPath . '/.gitignore';
        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('repo_path', $model->getErrors(), 'Check repo path error validation');
        $model->repo_path = '/root/';
        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('repo_path', $model->getErrors(), 'Check repo path error validation');
        $model->repo_path = self::$projectPath . '/';
        $this->assertFalse($model->validate());
        $this->assertArrayNotHasKey('repo_path', $model->getErrors());

        // check repo type
        $model->repo_type = Project::REPO_HG;
        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('repo_type', $model->getErrors(), 'Check repo type error validation');
        $model->repo_type = Project::REPO_GIT;
        $this->assertTrue($model->validate(), 'Check every field is validated');

        $result = $model->save();
        $this->assertTrue($result);
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
        $model = Project::findOne($project->id);
        $this->assertInstanceOf(Project::className(), $model);

        $model->repo_type = Project::REPO_HG;
        $this->assertFalse($model->validate());
        $this->assertArrayHasKey('repo_type', $model->getErrors(), 'Check wrong repo type');
        $model->repo_type = Project::REPO_GIT;
        $this->assertTrue($model->validate());
        $this->assertTrue($model->save());

        return $model;
    }

    /**
     * Tests get repository object
     *
     * @depends testUpdateProject
     * @param Project $project
     * @return Repository
     */
    public function testGetRepository(Project $project)
    {
        $repository = $project->getRepositoryObject();
        $this->assertInstanceOf(Repository::className(), $repository, 'Check get repository object');
        return $repository;
    }

    /**
     * Tests get repository history
     *
     * @depends testGetRepository
     * @param Repository $repository
     */
    public function testGetRepositoryHistory(Repository $repository)
    {
        $history = $repository->getHistory(10, 2);
        $this->assertContainsOnly(Commit::className(), $history);
        $this->assertCount(10, $history);
    }

    /**
     * Tests graph repository
     *
     * @depends testGetRepository
     * @param Repository $repository
     */
    public function testGetRepositoryGraphHistory(Repository $repository)
    {
        $graph = $repository->getGraphHistory(10, 2);
        $this->assertInstanceOf(Graph::className(), $graph);
        $this->assertCount(10, $graph->getCommits());
        $this->assertContainsOnly(Commit::className(), $graph->getCommits());
        return $repository;
    }

    /**
     * Tests remove project
     *
     * @depends testUpdateProject
     * @param Project $model
     */
    public function testRemoveProject(Project $model)
    {
        $result = $model->delete();
        $this->assertEquals(1, $result);
    }
}
