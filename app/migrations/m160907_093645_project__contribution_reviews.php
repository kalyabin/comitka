<?php

use yii\db\Migration;

/**
 * Table wich represnets a commits to need review
 */
class m160907_093645_project__contribution_reviews extends Migration
{
    private $table = '{{%contribution_review}}';
    private $tableUser = '{{%user}}';
    private $tableProject = '{{%project}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'commit_id' => $this->string(40)->notNull()->comment('Commit identifier'),
            'project_id' => $this->integer()->notNull()->comment('Project identifier'),
            'contributor_id' => $this->integer()->null()->comment('Contributor user id'),
            'reviewer_id' => $this->integer()->null()->comment('Reviewer user id'),
            'date' => $this->datetime()->notNull()->comment('Contribution date'),
        ], "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT 'Contributions reviews'");

        $this->addPrimaryKey('contribution_review_primary_key', $this->table, ['commit_id', 'project_id']);
        $this->addForeignKey('fk_contribution_review_project_id', $this->table, 'project_id', $this->tableProject, 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_contribution_review_contributor_id', $this->table, 'contributor_id', $this->tableUser, 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_contribution_review_reviewer_id', $this->table, 'contributor_id', $this->tableUser, 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_contribution_review_project_id', $this->table);
        $this->dropForeignKey('fk_contribution_review_contributor_id', $this->table);
        $this->dropForeignKey('fk_contribution_review_reviewer_id', $this->table);
        $this->dropTable($this->table);
    }
}
