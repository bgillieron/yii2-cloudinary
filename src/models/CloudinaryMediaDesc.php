<?php

namespace yii2cloudinary\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "cloudinary_media_desc".
 *
 * @property int $id
 * @property int $cloudinary_media_id
 * @property string $lang
 * @property string|null $title
 * @property string|null $description
 *
 * @property CloudinaryMedia $media
 */
class CloudinaryMediaDesc extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%cloudinary_media_desc}}';
    }

    public function rules()
    {
        return [
            [['cloudinary_media_id', 'lang'], 'required'],
            [['cloudinary_media_id'], 'integer'],
            [['lang'], 'string', 'max' => 5],
            [['title'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 200],
        ];
    }

    public function getMedia()
    {
        return $this->hasOne(CloudinaryMedia::class, ['id' => 'cloudinary_media_id']);
    }
}
