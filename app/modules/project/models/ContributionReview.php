<?php

namespace project\models;

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
            [['commit_id', 'project_id', 'date'], 'required'],
            [['project_id', 'contributor_id', 'reviewer_id'], 'integer'],
            [['date'], 'date', 'format' => 'yyyy-MM-dd HH:mm:ss', 'type' => DateValidator::TYPE_DATETIME],
            [['commit_id'], 'string', 'max' => 40],
            [['commit_id'], 'unique', 'targetAttribute' => ['commit_id', 'project_id']],
            [['contributor_id', 'reviewer_id'], 'default', 'value' => null],
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
}
