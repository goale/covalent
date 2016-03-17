<?php

namespace common\models;

use Yii;
use yii\helpers\BaseInflector;

/**
 * This is the model class for table "project".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $active
 * @property string $description
 * @property string $url
 * @property string $created_at
 * @property string $updated_at
 * @property string $source_url
 * @property integer $user_id
 * @property integer $group_id
 *
 * @property User $user
 */
class Project extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%projects}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['active', 'user_id', 'group_id'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'code', 'url', 'source_url'], 'string', 'max' => 255],
            [['url', 'source_url'], 'url'],
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
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'active' => 'Active',
            'description' => 'Description',
            'url' => 'Project URL',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'source_url' => 'Source URL',
            'user_id' => 'User ID',
            'group_id' => 'Group ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public function add()
    {
        $this->code = BaseInflector::slug(BaseInflector::transliterate($this->name), '-');
        $this->user_id = Yii::$app->user->id;

        if (!$this->validate()) {
            return false;
        }

        return $this->save();
    }
}
