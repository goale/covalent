<?php

namespace common\models;

use common\modules\Project\models\Project;
use yii;
use yii\db\ActiveRecord;
use yii\helpers\BaseInflector;

/**
 * This is the model class for table "groups".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property integer $user_id
 * @property integer $projects_count
 * @property integer $users_count
 *
 * @property Project[] $projects
 * @property User[] $users
 * @property GroupUser[] $groupUsers
 */
class Group extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'groups';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['name', 'code'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['name'], 'unique'],
            [['code'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'code' => Yii::t('app', 'Code'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    /**
     * Deletes all user assigned to a deleted group
     */
    public function afterDelete()
    {
        GroupUser::deleteAll(['group_id' => $this->id]);

        parent::afterDelete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::className(), ['group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
            ->viaTable('group_user', ['group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupUsers()
    {
        return $this->hasMany(GroupUser::className(), ['group_id' => 'id']);
    }


    /**
     * Finds group by code
     * @param $code
     * @return null|static
     */
    public static function findByCode($code)
    {
        return static::findOne(['code' => $code]);
    }

    /**
     * Gets a list of groups with projects and users count
     * @return array
     */
    public static function getGroups()
    {
        $result = [];

        $groups = self::find();

        if (!Yii::$app->user->can('doAll')) {
            $groups
                ->joinWith('groupUsers')
                ->where(['groups.user_id' => Yii::$app->user->id])
                ->orWhere(['group_user.user_id' => Yii::$app->user->id]);
        }

        foreach ($groups->each() as $group) {
            if (!Yii::$app->user->can('editGroup', ['group' => $group]) && !empty($group->groupUsers)) {
                $editable = $group->groupUsers[0]->role_id >= User::ROLE_MASTER;
            } else {
                $editable = Yii::$app->user->can('editGroup', ['group' => $group]);
            }

            $result[] = [
                'name' => $group->name,
                'code' => $group->code,
                'projects' => $group->projects_count,
                'users' => $group->users_count,
                'editable' => $editable,
            ];
        }

        return $result;
    }

    /**
     * Creates a group and links it to user
     * @return bool
     */
    public function addGroup()
    {
        $this->code = BaseInflector::slug(BaseInflector::transliterate($this->name), '-');
        $this->users_count++;
        $this->user_id = Yii::$app->user->id;

        if (!$this->validate()) {
            return false;
        }

        return $this->save();
    }

    /**
     * Checks if user had created a group
     * @param $userId
     * @return bool
     */
    public function isGroupOwner($userId)
    {
        return $this->user_id == $userId;
    }
}
