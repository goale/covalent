<?php

namespace common\modules\Project\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\BaseInflector;

/**
 * This is the model class for table "groups".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 *
 * @property Project[] $projects
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::className(), ['group_id' => 'id']);
    }
    
    
    public static function findByCode($code)
    {
        return static::findOne(['code' => $code]);
    }
    
    public function addGroup()
    {
        $this->code = BaseInflector::slug(BaseInflector::transliterate($this->name), '-');
        
        if (!$this->validate()) {
            return false;
        }
        
        return $this->save();
    }
}
