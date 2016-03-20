<?php

use yii\db\Migration;

class m160315_102835_create_project extends Migration
{
    public function up()
    {
        $this->createTable('{{%projects}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'code' => $this->string()->notNull()->unique(),
            'slug' => $this->string()->notNull()->unique(),
            'active' => $this->boolean()->defaultValue(true),
            'public' => $this->boolean()->defaultValue(false),
            'description' => $this->text(),
            'url' => $this->string(),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp(),
            'source_url' => $this->string(),
            'user_id' => $this->integer(),
            'group_id' => $this->integer(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%projects}}');
    }
}
