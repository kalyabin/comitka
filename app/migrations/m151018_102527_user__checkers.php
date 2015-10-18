<?php
use yii\db\Migration;

/**
 * Create user's checkers table
 */
class m151018_102527_user__checkers extends Migration
{
    private $table = '{{%user_checker}}';
    private $tableUser = '{{%user}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'user_id' => "int not null comment 'Users id'",
            'email_checker' => "varchar(32) null comment 'Hash to check e-mail'",
        ]);
        $this->addForeignKey('fk_user_checker_user_id',
            $this->table, 'user_id', $this->tableUser, 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
