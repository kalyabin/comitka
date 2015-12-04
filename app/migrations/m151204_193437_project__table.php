<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Create project main table
 */
class m151204_193437_project__table extends Migration
{
    private $table = '{{%project}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'title' => "varchar(100) not null comment 'Project title'",
            'repo_type' => "varchar(3) not null comment 'Repository type: git, hg or svn'",
            'repo_path' => "text not null comment 'Repository path'",
        ], "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT 'Projects table'");
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
