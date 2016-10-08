<?php
namespace project\widgets;

use app\models\ContributorInterface;
use app\widgets\ContributorLine;
use project\models\ContributionReview;
use project\models\Project;
use yii\web\User;
use VcsCommon\BaseCommit;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Commit panel with contribution review.
 *
 * Review model could be null, if contribution review is not exists now.
 */
class CommitPanel extends Widget
{
    /**
     * @var Project Project model
     */
    public $project;

    /**
     * @var BaseCommit Commit object to be viewed
     */
    public $commit;

    /**
     * @var ContributorInterface Contributor wich get a reviews
     */
    public $contributor;

    /**
     * @var User Authorized user
     */
    public $authUser;

    /**
     * Contribution review model.
     *
     * Null if has no reviews to this commit.
     *
     * @var ContributionReview|null
     */
    public $reviewModel;

    /**
     * @var string CSS-class for button
     */
    public $reviewButtonClass;

    /**
     * Render parents ids
     *
     * @return string
     */
    protected function renderParentsIds()
    {
        $parents = $this->commit->getParentsId();
        if (!empty($parents)) {
            $ret = ' (';

            $ret .= Yii::t('project', 'parents') . ': ';

            $project = $this->project;

            $ret .= implode(', ', array_map(function ($parentId) use ($project) {
                return Html::a($parentId, [
                    '/project/history/commit-summary',
                    'id' => $project->id,
                    'commitId' => $parentId,
                ]);
            }, $parents));

            $ret .= ')';

            return $ret;
        } else {
            return '';
        }
    }

    /**
     * Render commiter panel
     *
     * @return string
     */
    protected function renderCommiterPanel()
    {
        $ret = Html::tag('strong', Yii::t('project', 'Commited by')) . ' ';
        $ret .= $this->commit->contributorName;
        if (($email = $this->commit->contributorEmail) !== false) {
            $ret .= ' ' . Html::encode('<' . $email . '>');
        }
        $ret .= ' ' . Yii::t('project', 'at') . ' ' . Html::encode($this->commit->getDate()->format("d\'M y H:i:s"));
        return $ret;
    }

    /**
     * Render contributor panel
     *
     * @return string
     */
    protected function renderContributorPanel()
    {
        $ret = Html::tag('strong', Yii::t('project', 'Contributed by')) . '<br />';
        $ret .= ContributorLine::widget([
            'contributor' => $this->contributor,
        ]);
        return $ret;
    }

    /**
     * Render finish button
     *
     * @return string
     */
    protected function renderFinishButton()
    {
        return Html::button(Yii::t('project', 'Finish review'), [
            'class' => 'btn btn-xs btn-primary ' . $this->reviewButtonClass,
            'data' => [
                'url' => Url::to([
                    '/project/contribution-review/finish-review',
                    'projectId' => $this->project->id,
                    'commitId' => $this->commit->getId(),
                ]),
            ],
        ]);
    }

    /**
     * Render to be a reviewer button
     *
     * @return string
     */
    protected function renderBeReviewerButton()
    {
        return Html::button(Yii::t('project', 'To be a reviewer'), [
            'class' => 'btn btn-xs btn-primary ' . $this->reviewButtonClass,
            'data' => [
                'url' => Url::to([
                    '/project/contribution-review/create-self-review',
                    'projectId' => $this->project->id,
                    'commitId' => $this->commit->getId(),
                ]),
            ],
        ]);
    }

    /**
     * Render review panel
     *
     * @return string
     */
    protected function renderReviewPanel()
    {
        $ret = Html::tag('strong', 'Reviewed by') . '<br />';

        if (is_null($this->reviewModel)) {
            $ret .= Yii::t('project', '(has no review)');
            $ret .= ' ' . $this->renderBeReviewerButton();
        } else {
            $reviewer = $this->reviewModel->reviewer;
            if ($reviewer) {
                $ret .= ContributorLine::widget([
                    'contributor' => $reviewer,
                ]);
            } else {
                $ret .= Yii::t('project', '(reviewer is not set)');
                $ret .= ' ' . $this->renderBeReviewerButton();
            }

            if ($this->reviewModel->reviewIsFinished()) {
                $ret .= ' ';
                $ret .= Yii::t('project', 'at') . ' ' . $this->reviewModel->getReviewedDateTime()->format("d\'M y H:i:s");
            } elseif ($reviewer) {
                $ret .= ' ' . Yii::t('project', 'did not complete a review');
            }

            // current user is current reviewer
            if ($this->reviewModel->canFinishReview($this->authUser->getId())) {
                // show finish review button
                $ret .= ' ' . $this->renderFinishButton();
            }
        }
        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $ret = Html::beginTag('div', [
            'class' => !$this->reviewModel || !$this->reviewModel->reviewIsFinished() ?
                'col-md-12 alert alert-danger' :
                'col-md-12 alert alert-info',
        ]);
        $ret .= Html::beginTag('div', [
            'class' => 'row col-md-12',
        ]);
        $ret .= Html::tag('strong', Html::encode($this->commit->getId()));
        $ret .= $this->renderParentsIds();
        $ret .= Html::endTag('div');
        $ret .= Html::beginTag('div', [
            'class' => 'row col-md-12',
        ]);
        $ret .= $this->renderCommiterPanel();
        $ret .= Html::endTag('div');
        $ret .= Html::beginTag('div', [
            'class' => 'row col-md-6',
        ]);
        $ret .= $this->renderContributorPanel();
        $ret .= Html::endTag('div');
        $ret .= Html::beginTag('div', [
            'class' => 'row col-md-6'
        ]);
        $ret .= $this->renderReviewPanel();
        $ret .= Html::endTag('div');
        $ret .= Html::endTag('div');

        return $ret;
    }
}
