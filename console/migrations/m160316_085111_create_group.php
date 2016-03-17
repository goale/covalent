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
        ]);
        
        $this->addForeignKey('fk_project_group', 'projects', 'group_id', '{{%groups}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_project_group', '{{%groups}}');
        
        $this->dropTable('{{%groups}}');
    }
}
