<?php

use yii\db\Migration;

/**
 * Implements user avatars
 */
class m160819_194513_user__avatar extends Migration
{
    private  $table = '{{%user}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'avatar', "varchar(37) null COMMENT 'User avatar file name'");
    }

    public function safeDown()
    {
        $this->dropColumn($this->table, 'avatar');
    }
}
