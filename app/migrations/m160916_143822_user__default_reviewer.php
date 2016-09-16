<?php

use yii\db\Migration;

/**
 * Default user reviewer
 */
class m160916_143822_user__default_reviewer extends Migration
{
    private $table = '{{%user}}';

    public function safeUp()
    {
        $this->addColumn($this->table, 'default_reviewer_id', $this->integer()->null()->comment('Default user reviewer'));
        $this->addForeignKey('fk_user_default_reviewer_id', $this->table, 'default_reviewer_id', $this->table, 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_user_default_reviewer_id', $this->table);
        $this->dropColumn($this->table, 'default_reviewer_id');
    }
}
