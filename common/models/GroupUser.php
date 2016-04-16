<?php

namespace common\models;

use yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "group_user".
 *
 * @property integer $id
 * @property integer $group_id
 * @property integer $user_id
 * @property integer $role
 */
class GroupUser extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'user_id'], 'required'],
            [['group_id', 'user_id', 'role'], 'integer']
        ];
    }

    /**
     * Decrements users count in group after deleting user
     */
    public function afterDelete()
    {
        Group::findOne($this->group_id)->updateCounters(['users_count' => -1]);

        parent::afterDelete();
    }

    /**
     * Increments users count after adding new user
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            Group::findOne($this->group_id)->updateCounters(['users_count' => 1]);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'group_id' => Yii::t('app', 'Group ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'role' => Yii::t('app', 'Role'),
        ];
    }
}
