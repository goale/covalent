<?php

use yii\db\Migration;

class m160414_071001_create_project_user extends Migration
{
    public function up()
    {
        $this->createTable('project_user', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'role' => $this->integer()->notNull()->defaultValue(\common\models\User::ROLE_VIEWER),
        ]);

        $this->addForeignKey('fk_project_members_project', 'project_user', 'project_id', 'projects', 'id');
        $this->addForeignKey('fk_project_members_user', 'project_user', 'user_id', 'users', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_project_members_project', 'project_user');
        $this->dropForeignKey('fk_project_members_user', 'project_user');

        $this->dropTable('project_user');
    }
}
