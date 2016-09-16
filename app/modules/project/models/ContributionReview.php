<?php

namespace project\models;

use DateTime;
use user\models\User;
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
 * @property User $contributor Relation to contributor user model
 * @property User $reviewer Relation to reviewer user model
 * @property Project $project Relation to project model
 */
class ContributionReview extends ActiveRecord
{
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
     * Retrieve a contributor user model
     *
     * @return ActiveQuery
     */
    public function getContributor()
    {
        return $this->hasOne(User::className(), ['id' => 'contributor_id']);
    }

    /**
     * Retrieve a reviewer user model
     *
     * @return ActiveQuery
     */
    public function getReviewer()
    {
        return $this->hasOne(User::className(), ['id' => 'reviewer_id']);
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
}
