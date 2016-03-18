<?php
namespace project\widgets;

use project\controllers\actions\FileViewAction;
use VcsCommon\BaseCommit;
use VcsCommon\File;
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
     * @var BaseCommit Commit model
     */
    public $commit;

    /**
     * @var File File model with status
     */
    public $file;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->commit instanceof BaseCommit) {
            throw new InvalidParamException('Commit variable must be an instance of ' . BaseCommit::className());
        }
        if (!$this->file instanceof File) {
            throw new InvalidParamException('File variable must be an instance of ' . File::className());
        }
        $this->id = $this->commit->getId() . md5($this->file->getPathname());
    }

    /**
     * Get file links: raw view, diff view or comparision.
     *
     * @return array
     */
    protected function getLinks()
    {
        $commonFileLinkParams = http_build_query([
            'commitId' => $this->commit->getId(),
            'filePath' => $this->file->getPathname(),
        ]);

        $links = [
            [
                'mode' => FileViewAction::MODE_RAW,
                'params' => $commonFileLinkParams,
                'label' => '[' . Yii::t('project', 'raw') . ']',
            ],
        ];

        if (in_array($this->file->getStatus(), [
            File::STATUS_MODIFIED, File::STATUS_COPIED,
            File::STATUS_RENAMING, File::STATUS_TYPED
        ])) {
            $links[] = [
                'mode' => FileViewAction::MODE_DIFF,
                'params' => $commonFileLinkParams,
                'label' => '[' . Yii::t('project', 'diff') . ']',
            ];
        }

        if ($this->file->getStatus() === File::STATUS_MODIFIED) {
            $links[] = [
                'mode' => FileViewAction::MODE_COMPARE,
                'params' => $commonFileLinkParams,
                'label' => '[' . Yii::t('project', 'compare') . ']',
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

        $status = $this->file->getStatus();

        if ($status === File::STATUS_DELETION || $status === File::STATUS_UNMERGED) {
            $itemClassSuffix = 'danger';
        }
        elseif ($status === File::STATUS_ADDITION || $status === File::STATUS_COPIED) {
            $itemClassSuffix = 'success';
        }
        elseif ($status === File::STATUS_MODIFIED || $status === File::STATUS_RENAMING || $status === File::STATUS_TYPED) {
            $itemClassSuffix = 'info';
        }
        else {
            $status = File::STATUS_UNKNOWN;
        }

        $ret = Html::beginTag('div', [
            'class' => 'revision-file',
        ]);
        $ret .= Html::tag(
            'span',
            $status,
            [
                'class' => 'label label-' . $itemClassSuffix
            ]
        );
        $ret .= '&nbsp;&nbsp;&nbsp;' . $this->file->getPathname() . '&nbsp;&nbsp;';
        $ret .= implode('&nbsp;', array_map(function($link) {
            return Html::tag('a', $link['label'], [
                'href' => '#',
                'class' => 'js-revision-file revision-file-link',
                'data' => [
                    'params' => $link['params'] . '&mode=' . $link['mode'],
                    'container' => $this->getId(),
                    'mode' => $link['mode'],
                ],
            ]);
        }, $this->getLinks()));
        $ret .= Html::endTag('div');

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
