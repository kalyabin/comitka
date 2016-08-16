<?php

use yii\db\Migration;

/**
 * VCS usernames in GIT, HG, SVN
 */
class m160816_182627_user__user_vcs_accounts extends Migration
{
    private $table = '{{%user}}';
    private $tableAccounts = '{{%user_account}}';

    public function safeUp()
    {
        $this->createTable($this->tableAccounts, [
            'id' => 'pk',
            'user_id' => "int not null COMMENT 'Relation to user table'",
            'username' => "varchar(100) not null COMMENT 'Username'",
            'type' => "enum('git', 'hg', 'svn') not null COMMENT 'VCS system'",
        ]);
        $this->createIndex('user_account_username', $this->tableAccounts, 'username, type', true);
        $this->addForeignKey('fk_user_account_user_id', $this->tableAccounts, 'user_id', $this->table, 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_user_account_user_id', $this->tableAccounts);
        $this->dropTable($this->tableAccounts);
    }
}
