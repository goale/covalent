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
 *
 * @property Project[] $projects
 * @property User[] $users
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

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $query = self::find()->select(['id', 'name', 'code'])->with('projects')->with('users');
            $groups = $query->all();
        } else {
            $groups = User::findOne(Yii::$app->user->id)->groups;
        }

        foreach ($groups as $group) {
            $result[$group->id] = [
                'name' => $group->name,
                'code' => $group->code,
                'users' => count($group->users),
//                'projects' => count($group->projects),
//                'users' => 0,
                'projects' => 2,
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

        if (!$this->validate()) {
            return false;
        }

        $saved = $this->save();

        if (!Yii::$app->user->isGuest) {
            $user = User::findOne(Yii::$app->user->id);
            $this->link('users', $user, ['role_id' => 40]);
        }

        return $saved;
    }
}
