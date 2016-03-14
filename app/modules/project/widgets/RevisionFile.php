<?php
namespace project\widgets;

use project\controllers\actions\FileViewAction;
use VcsCommon\BaseCommit;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Represents file row in summary revision description.
 */
class RevisionFile extends Widget
{
    /**
     * Diff status: M, A, R, D, etc.
     *
     * @var string
     */
    public $status;

    /**
     * @var BaseCommit Commit model
     */
    public $commit;

    /**
     * @var string File path name
     */
    public $pathname;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->commit instanceof BaseCommit) {
            throw new InvalidParamException('Commit variable must be an instance of ' . BaseCommit::className());
        }
        $this->id = $this->commit->getId() . md5($this->pathname);
    }

    /**
     * Get file links: raw view, diff view or comparision.
     *
     * @return array
     */
    protected function getLinks()
    {
        $links = [
            [
                'mode' => FileViewAction::MODE_RAW,
                'params' => http_build_query([
                    'commitId' => $this->commit->getId(),
                    'filePath' => $this->pathname,
                    'mode' => FileViewAction::MODE_RAW,
                ]),
                'label' => '[' . Yii::t('project', 'raw') . ']',
            ],
        ];

        if ($this->status === 'M') {
            $links[] = [
                'mode' => FileViewAction::MODE_DIFF,
                'params' => http_build_query([
                    'commitId' => $this->commit->getId(),
                    'filePath' => $this->pathname,
                    'mode' => FileViewAction::MODE_DIFF,
                ]),
                'label' => '[' . Yii::t('project', 'diff') . ']',
            ];
        }

        return $links;
    }

    /**
     * Get HTML for file row description.
     *
     * @return string
     */
    protected function renderFileRow()
    {
        $itemClassSuffix = 'default';

        if ($this->status == 'R' || $this->status == 'D') {
            $itemClassSuffix = 'danger';
        }
        elseif ($this->status === 'A') {
            $itemClassSuffix = 'success';
        }

        $ret = Html::tag(
            'span',
            $this->status,
            [
                'class' => 'label label-' . $itemClassSuffix
            ]
        );
        $ret .= '&nbsp;&nbsp;&nbsp;' . $this->pathname . '&nbsp;&nbsp;';
        $ret .= implode('&nbsp;', array_map(function($link) {
            return Html::tag('a', $link['label'], [
                'href' => '#',
                'class' => 'js-revision-file revision-file-link',
                'data' => [
                    'params' => $link['params'],
                    'container' => $this->getId(),
                    'mode' => $link['mode'],
                ],
            ]);
        }, $this->getLinks()));

        return $ret;
    }

    /**
     * Renders widget.
     *
     * @return string
     */
    public function run()
    {
        $ret = $this->renderFileRow();

        $ret .= Html::tag('div', '', [
            'class' => 'js-revision-file-content',
            'style' => 'display:none;',
            'id' => $this->getId(),
        ]);

        return $ret;
    }
}
