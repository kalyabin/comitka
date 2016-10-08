<?php

namespace widgets;

use tests\codeception\fixtures\ProjectFixture;
use UnitWebTestCase;
use project\widgets\ProjectPanel;
use project\models\Project;

/**
 * Test project panel widget
 */
class ProjectPanelTest extends UnitWebTestCase
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
     * Test widget
     */
    public function testMe()
    {
        /* @var $project Project */
        $project = $this->getModule('Yii2')->grabFixture('projects', 'gitProject');

        $projectTitle = $project->title;
        $projectId = $project->id;

        $expectedString = '
            <div class="inline-title">
                <h1 class="inline-title">' . $projectTitle . '</h1>
                <span class="label label-warning label-git">Git</span>
                <ul id="w0" class="nav nav-tabs">
                    <li><a href="/projects/' . $projectId .'/revisions/simple">History</a></li>
                    <li><a href="/projects/' . $projectId .'/revisions/graph">Graph</a></li>
                    <li><a href="/projects/' . $projectId .'/tree">Repository tree</a></li>
                </ul>
            </div>
        ';

        $expectedDom = new \DOMDocument();
        $expectedDom->loadXML($expectedString);

        $result = ProjectPanel::widget([
            'project' => $project,
        ]);

        $resultDom = new \DOMDocument();
        $resultDom->loadXML($result);

        $this->assertEqualXMLStructure($expectedDom->firstChild, $resultDom->firstChild, true);
    }

    /**
     * Test widget exception
     *
     * @expectedException yii\base\InvalidParamException
     */
    public function testExceptionThrow()
    {
        ProjectPanel::widget();
    }
}
