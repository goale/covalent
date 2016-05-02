<?php

use yii\db\Migration;

/**
 * Handles the creation for table `features_table`.
 */
class m160502_170354_create_features_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('features', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'code' => $this->string()->notNull()->unique(),
            'description' => $this->text(),
            'project_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(\common\models\Feature::STATUS_NEW),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp(),
            'user_id' => $this->integer(),
        ]);

        $this->addForeignKey('fk_feature_project', 'features', 'project_id', 'projects', 'id');
        $this->addForeignKey('fk_feature_user', 'features', 'user_id', 'users', 'id');

        $this->createIndex('index_feature_status', 'features', 'status');
        $this->createIndex('index_feature_user', 'features', 'user_id');
        $this->createIndex('index_feature_project', 'features', 'project_id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex('index_feature_project', 'features');
        $this->dropIndex('index_feature_user', 'features');
        $this->dropIndex('index_feature_status', 'features');

        $this->dropForeignKey('fk_feature_user', 'features');
        $this->dropForeignKey('fk_feature_project', 'features');

        $this->dropTable('features');
    }
}
