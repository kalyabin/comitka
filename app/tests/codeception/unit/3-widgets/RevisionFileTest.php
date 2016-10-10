<?php

namespace widgets;

use Yii;
use tests\codeception\fixtures\ProjectFixture;
use UnitWebTestCase;
use project\widgets\RevisionFile;
use VcsCommon\BaseCommit;
use project\models\Project;
use VcsCommon\File;

/**
 * Test revision file widget
 */
class RevisionFileTest extends UnitWebTestCase
{
    /**
     * Fixtures
     *
     * @return array
     */
    public function _fixtures()
    {
        return [
            'projects' => ProjectFixture::className(),
        ];
    }

    /**
     * Prepare testing data and returns it
     *
     * @param string $fileStatus Status wich will be set for file
     *
     * @return array
     */
    protected function prepareFixtures($fileStatus)
    {
        /* @var $project Project */
        $project = $this->getModule('Yii2')->grabFixture('projects', 'gitProject');
        /* @var $history BaseCommit[] */
        $history = $project->getRepositoryObject()->getHistory(100, 0);
        $this->assertNotEmpty($history);

        // search commit and file
        $commit = null;
        $file = null;
        foreach ($history as $searchCommit) {
            $this->assertInstanceOf(BaseCommit::className(), $searchCommit);
            foreach ($searchCommit->getChangedFiles() as $searchFile) {
                $commit = $searchCommit;
                $file = new File($searchFile->getPath(), $project->getRepositoryObject(), $fileStatus);
                break;
            }
        }

        return [$project, $commit, $file];
    }

    /**
     * Prepare file link to assert
     *
     * @param string $commitId Commit identifier
     * @param string $filePath Relative file path
     * @param string $mode File view mode
     *
     * @return string
     */
    protected function prepareFileMode($projectId, $commitId, File $file, $mode)
    {
        if ($mode == 'history') {
            return '<a href="/projects/' . $projectId . '/revisions/simple?path=' . $file->getPathname() . '">[history]</a>';
        } else {
            $link = 'commitId=' . $commitId . '&amp;filePath=' . $file->getPathname() . '&amp;mode=' . $mode;
            $containerId = $commitId . md5($file->getPathname());
            return '<a class="js-revision-file revision-file-link" href="#" data-params="' . $link . '" data-container="' . $containerId . '" data-mode="' . $mode . '">[' . $mode . ']</a>';
        }
    }

    /**
     * Test addition status
     */
    public function testAddFile()
    {
        /* @var $project Project */
        /* @var $commit BaseCommit */
        /* @var $file File */

        list($project, $commit, $file) = $this->prepareFixtures(File::STATUS_ADDITION);

        $result = RevisionFile::widget([
            'project' => $project,
            'repository' => $project->getRepositoryObject(),
            'commit' => $commit,
            'file' => $file,
        ]);

        $this->assertContains($file->getPathname(), $result);
        $this->assertContains('<span class="label label-success">A</span>', $result);

        $expectedLinks = [
            'raw' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'raw'),
            'history' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'history'),
        ];
        foreach ($expectedLinks as $link) {
            $this->assertContains($link, $result);
        }

        $unexpectedLinks = [
            'diff' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'diff'),
            'compare' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'compare'),
        ];
        foreach ($unexpectedLinks as $link) {
            $this->assertNotContains($link, $result);
        }
    }

    /**
     * Test copied status
     */
    public function testCopyFile()
    {
        /* @var $project Project */
        /* @var $commit BaseCommit */
        /* @var $file File */

        list($project, $commit, $file) = $this->prepareFixtures(File::STATUS_COPIED);

        $result = RevisionFile::widget([
            'project' => $project,
            'repository' => $project->getRepositoryObject(),
            'commit' => $commit,
            'file' => $file,
        ]);

        $this->assertContains($file->getPathname(), $result);
        $this->assertContains('<span class="label label-success">C</span>', $result);

        $expectedLinks = [
            'raw' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'raw'),
            'history' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'history'),
            'diff' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'diff'),
        ];
        foreach ($expectedLinks as $link) {
            $this->assertContains($link, $result);
        }

        $unexpectedLinks = [
            'compare' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'compare'),
        ];
        foreach ($unexpectedLinks as $link) {
            $this->assertNotContains($link, $result);
        }
    }

    /**
     * Test deletion status
     */
    public function testDeletionFile()
    {
        /* @var $project Project */
        /* @var $commit BaseCommit */
        /* @var $file File */

        list($project, $commit, $file) = $this->prepareFixtures(File::STATUS_DELETION);

        $result = RevisionFile::widget([
            'project' => $project,
            'repository' => $project->getRepositoryObject(),
            'commit' => $commit,
            'file' => $file,
        ]);

        $this->assertContains($file->getPathname(), $result);
        $this->assertContains('<span class="label label-danger">D</span>', $result);

        $expectedLinks = [
            'raw' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'raw'),
            'history' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'history'),
        ];
        foreach ($expectedLinks as $link) {
            $this->assertContains($link, $result);
        }

        $unexpectedLinks = [
            'diff' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'diff'),
            'compare' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'compare'),
        ];
        foreach ($unexpectedLinks as $link) {
            $this->assertNotContains($link, $result);
        }
    }

    /**
     * Test modified status
     */
    public function testModifiedFile()
    {
        /* @var $project Project */
        /* @var $commit BaseCommit */
        /* @var $file File */

        list($project, $commit, $file) = $this->prepareFixtures(File::STATUS_MODIFIED);

        $result = RevisionFile::widget([
            'project' => $project,
            'repository' => $project->getRepositoryObject(),
            'commit' => $commit,
            'file' => $file,
        ]);

        $this->assertContains($file->getPathname(), $result);
        $this->assertContains('<span class="label label-info">M</span>', $result);

        $expectedLinks = [
            'raw' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'raw'),
            'history' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'history'),
            'diff' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'diff'),
            'compare' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'compare'),
        ];
        foreach ($expectedLinks as $link) {
            $this->assertContains($link, $result);
        }
    }

    /**
     * Test renaming status
     */
    public function testRenamingFile()
    {
        /* @var $project Project */
        /* @var $commit BaseCommit */
        /* @var $file File */

        list($project, $commit, $file) = $this->prepareFixtures(File::STATUS_RENAMING);

        $result = RevisionFile::widget([
            'project' => $project,
            'repository' => $project->getRepositoryObject(),
            'commit' => $commit,
            'file' => $file,
        ]);

        $this->assertContains($file->getPathname(), $result);
        $this->assertContains('<span class="label label-info">R</span>', $result);

        $expectedLinks = [
            'raw' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'raw'),
            'history' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'history'),
            'diff' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'diff'),
        ];
        foreach ($expectedLinks as $link) {
            $this->assertContains($link, $result);
        }

        $unexpectedLinks = [
            'compare' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'compare'),
        ];
        foreach ($unexpectedLinks as $link) {
            $this->assertNotContains($link, $result);
        }
    }

    /**
     * Test typed status
     */
    public function testTypedFile()
    {
        /* @var $project Project */
        /* @var $commit BaseCommit */
        /* @var $file File */

        list($project, $commit, $file) = $this->prepareFixtures(File::STATUS_TYPED);

        $result = RevisionFile::widget([
            'project' => $project,
            'repository' => $project->getRepositoryObject(),
            'commit' => $commit,
            'file' => $file,
        ]);

        $this->assertContains($file->getPathname(), $result);
        $this->assertContains('<span class="label label-info">T</span>', $result);

        $expectedLinks = [
            'raw' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'raw'),
            'history' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'history'),
            'diff' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'diff'),
        ];
        foreach ($expectedLinks as $link) {
            $this->assertContains($link, $result);
        }

        $unexpectedLinks = [
            'compare' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'compare'),
        ];
        foreach ($unexpectedLinks as $link) {
            $this->assertNotContains($link, $result);
        }
    }

    /**
     * Test unmerged status
     */
    public function testUnmergedFile()
    {
        /* @var $project Project */
        /* @var $commit BaseCommit */
        /* @var $file File */

        list($project, $commit, $file) = $this->prepareFixtures(File::STATUS_UNMERGED);

        $result = RevisionFile::widget([
            'project' => $project,
            'repository' => $project->getRepositoryObject(),
            'commit' => $commit,
            'file' => $file,
        ]);

        $this->assertContains($file->getPathname(), $result);
        $this->assertContains('<span class="label label-danger">U</span>', $result);

        $expectedLinks = [
            'raw' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'raw'),
            'history' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'history'),
        ];
        foreach ($expectedLinks as $link) {
            $this->assertContains($link, $result);
        }

        $unexpectedLinks = [
            'diff' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'diff'),
            'compare' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'compare'),
        ];
        foreach ($unexpectedLinks as $link) {
            $this->assertNotContains($link, $result);
        }
    }

    /**
     * Test unknown
     */
    public function testUnknownFileStatus()
    {
        /* @var $project Project */
        /* @var $commit BaseCommit */
        /* @var $file File */

        list($project, $commit, $file) = $this->prepareFixtures(File::STATUS_UNKNOWN);

        $result = RevisionFile::widget([
            'project' => $project,
            'repository' => $project->getRepositoryObject(),
            'commit' => $commit,
            'file' => $file,
        ]);

        $this->assertContains($file->getPathname(), $result);
        $this->assertContains('<span class="label label-default">X</span>', $result);

        $expectedLinks = [
            'raw' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'raw'),
            'history' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'history'),
        ];
        foreach ($expectedLinks as $link) {
            $this->assertContains($link, $result);
        }

        $unexpectedLinks = [
            'diff' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'diff'),
            'compare' => $this->prepareFileMode($project->id, $commit->getId(), $file, 'compare'),
        ];
        foreach ($unexpectedLinks as $link) {
            $this->assertNotContains($link, $result);
        }
    }
}
