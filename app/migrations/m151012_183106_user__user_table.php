<?php
use yii\db\Migration;

/**
 * Creates users table
 */
class m151012_183106_user__user_table extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => 'pk',
            'name' => "varchar(100) not null comment 'Users display name'",
            'email' => "varchar(100) not null comment 'Users email (login)'",
            'password' => "varchar(255) not null comment 'Users password hash'",
            'status' => "smallint not null default 0 comment 'Users status'",
            'UNIQUE (email)',
        ], "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT 'Users table'");
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
