<?php

namespace project\models;

use app\components\ContributorApi;
use app\models\ContributorInterface;
use app\models\UnregisteredContributor;
use DateTime;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\validators\DateValidator;

/**
 * Contribution review representation.
 *
 * Contains:
 * - commit identifier and project identifier;
 * - contributor relation and reviewer relation.
 *
 * @property string $commit_id Commit identifier
 * @property integer $project_id Project identifier
 * @property integer $contributor_id Contributor user identifier (null if not detectected)
 * @property integer $reviewer_id Reviewer user identifier (null if not detected)
 * @property string $date Contribution date and time
 * @property string $reviewed Review date by reviewer
 * @property string $message Commit message
 * @property string $contributor_email Contributor e-mail
 * @property string $contributor_name Contributor user name
 * @property string $repo_type Repository type
 *
 * @property ContributorInterface $contributor Relation to contributor user model
 * @property ContributorInterface|null $reviewer Relation to reviewer user model
 * @property Project $project Relation to project model
 */
class ContributionReview extends ActiveRecord
{
    /**
     * @var ContributorInterface|null Contributor model
     */
    protected $contributor;

    /**
     * @var ContributorInterface|null Reviewer model
     */
    protected $reviewer;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contribution_review}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['commit_id', 'project_id', 'date', 'repo_type', 'contributor_name'], 'required'],
            [['project_id', 'contributor_id', 'reviewer_id'], 'integer'],
            [['date', 'reviewed'], 'date', 'format' => 'yyyy-MM-dd HH:mm:ss', 'type' => DateValidator::TYPE_DATETIME],
            [['message'], 'string'],
            [['contributor_email', 'contributor_name'], 'string', 'max' => 100],
            [['repo_type'], 'string'],
            [['repo_type'], 'in', 'range' => [Project::REPO_GIT, Project::REPO_HG, Project::REPO_SVN]],
            [['message', 'contributor_email'], 'default', 'value' => ''],
            [['commit_id'], 'string', 'max' => 40],
            [['commit_id'], 'unique', 'targetAttribute' => ['commit_id', 'project_id']],
            [['contributor_id', 'reviewer_id'], 'default', 'value' => null],
            [['reviewed'], 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'commit_id' => Yii::t('project', 'Commit identifier'),
            'project_id' => Yii::t('project', 'Project identifier'),
            'contributor_id' => Yii::t('project', 'Contributor user id'),
            'reviewer_id' => Yii::t('project', 'Reviewer user id'),
            'date' => Yii::t('project', 'Date'),
            'reviewed' => Yii::t('project', 'Review date by reviewer'),
            'message' => Yii::t('project', 'Commit message'),
            'contributor_email' => Yii::t('project', 'Contributor e-mail'),
            'contributor_name' => Yii::t('project', 'Contributor user name'),
            'repo_type' => Yii::t('project', 'Repository type'),
        ];
    }

    /**
     * Retrieve a project model
     *
     * @return ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    /**
     * Retrieve a contributor model
     *
     * @return ContributorInterface
     */
    public function getContributor()
    {
        if (is_null($this->contributor) && !is_null($this->contributor_id)) {
            /* @var $contributorApi ContributorApi */
            $contributorApi = Yii::$app->contributors;
            $this->contributor = $contributorApi->getContributorById($this->contributor_id);
        } elseif (is_null($this->contributor)) {
            $this->contributor = new UnregisteredContributor([
                'contributorName' => $this->contributor_name,
                'contributorEmail' => $this->contributor_email,
            ]);
        }

        return $this->contributor;
    }

    /**
     * Retrieve a reviewer user model
     *
     * @return ActiveQuery
     */
    public function getReviewer()
    {
        if (is_null($this->reviewer) && !is_null($this->reviewer_id)) {
            /* @var $contributorApi ContributorApi */
            $contributorApi = Yii::$app->contributors;
            $this->reviewer = $contributorApi->getContributorById($this->reviewer_id);
        }

        return $this->reviewer;
    }

    /**
     * Commit date and time object
     *
     * @return DateTime
     */
    public function getDateTime()
    {
        return new DateTime($this->date);
    }

    /**
     * Reviewed date and time object
     *
     * @return DateTime
     */
    public function getReviewedDateTime()
    {
        return !is_null($this->reviewed) ? new DateTime($this->reviewed) : null;
    }

    /**
     * Returns true, if review is finished.
     *
     * @return boolean
     */
    public function reviewIsFinished()
    {
        return !is_null($this->reviewed);
    }

    /**
     * Finish review
     *
     * @return boolean
     */
    public function finishReview()
    {
        $this->reviewed = date('Y-m-d H:i:s');
        return $this->save(true, ['reviewed']);
    }

    /**
     * Returns true, if reviewer can finish review.
     *
     * @param integer $reviewerId Reviewer id want to close review
     *
     * @return boolean
     */
    public function canFinishReview($reviewerId)
    {
        return !$this->reviewIsFinished() && $this->reviewer_id == $reviewerId;
    }
}
