<?php

namespace common\modules\Project\models;

use common\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\BaseInflector;

/**
 * This is the model class for table "project".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $slug
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
class Project extends ActiveRecord
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
            [['name', 'code', 'slug'], 'required'],
            [['active', 'user_id', 'group_id'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'code', 'url', 'source_url'], 'string', 'max' => 255],
            [['url', 'source_url'], 'url'],
            [['name'], 'unique'],
            [['code'], 'unique'],
            [['slug'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app', 'Name'),
            'code' => Yii::t('app', 'Code'),
            'slug' => Yii::t('app', 'Slug'),
            'active' => Yii::t('app', 'Active'),
            'description' => Yii::t('app', 'Description'),
            'url' => Yii::t('app', 'Project Url'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'source_url' => Yii::t('app', 'Source Url'),
            'user_id' => 'User ID',
            'group_id' => 'Group ID',
        ];
    }

    /**
     * Gets all active projects
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAll()
    {
        return static::find()
            ->select(['id', 'name', 'code'])
            ->where(['active' => self::STATUS_ACTIVE])
            ->asArray()
            ->all();
    }

    /**
     * Finds project by code
     * 
     * @param $code
     * @return null|static
     */
    public static function findByCode($code)
    {
        return static::findOne(['code' => $code]);
    }

    /**
     * Finds project by slug
     *
     * @param $slug
     * @return null|static
     */
    public static function findBySlug($slug)
    {
        if (empty($slug) || !is_string($slug)) {
            return null;
        }

        return static::findOne(['slug' => $slug]);
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
