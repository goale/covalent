<?php

use yii\db\Migration;

class m160316_085111_create_group extends Migration
{
    public function up()
    {
        $this->createTable('{{%groups}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'code' => $this->string()->notNull()->unique(),
            'description' => $this->text(),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp()
        ]);

        $this->createTable('{{%group_user}}', [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'role_id' => $this->integer()->notNull()->defaultValue(1),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%group_user}}');
        $this->dropTable('{{%groups}}');
    }
}
