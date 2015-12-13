<?php

namespace project\widgets;

use project\models\Project;
use Yii;
use yii\base\InvalidParamException;
use yii\bootstrap\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\Widget;

/**
 * View project panel with links
 */
class ProjectPanel extends Widget
{
    /**
     * @var Project
     */
    public $project;

    /**
     * @var array project links
     */
    protected $links = [];

    /**
     * Project variable must be an instance of project
     *
     * @throws InvalidParamException
     */
    public function init()
    {
        if (!$this->project instanceof Project) {
            throw new InvalidParamException();
        }

        $this->links = [
            [
                'label' => Yii::t('project', 'History'),
                'url' => ['/project/history/history', 'id' => $this->project->id, 'type' => 'simple'],
            ],
            [
                'label' => Yii::t('project', 'Graph'),
                'url' => ['/project/history/history', 'id' => $this->project->id, 'type' => 'graph'],
            ]
        ];
    }

    /**
     * Renders project title
     *
     * @return string
     */
    protected function renderTitle()
    {
        $content = '<h1 class="inline-title">' . Html::encode($this->project->title) . '</h1>' . "\n";
        $content .= '<span class="' . $this->project->getRepoLabelCss() . '">' . $this->project->getRepoTypeName() . '</span>' . "\n";

        return $content;
    }

    /**
     * Renders project menu links
     *
     * @return string
     */
    protected function renderLinks()
    {
        return Nav::widget([
            'options' => ['class' => 'nav nav-tabs'],
            'items' => $this->links,
        ]);
    }

    /**
     * Render widget
     */
    public function run()
    {
        $content = '<div class="inline-title">';
        $content .= $this->renderTitle();
        $content .= $this->renderLinks();
        $content .= '</div>';
        return $content;
    }
}
