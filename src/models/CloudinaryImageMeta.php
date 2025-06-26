<?php

namespace yii2cloudinary\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "cloudinary_image_meta".
 *
 * @property int $id
 * @property int $cloudinary_media_id
 * @property int|null $width
 * @property int|null $height
 * @property string|null $transformations
 *
 * @property CloudinaryMedia $media
 */
class CloudinaryImageMeta extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%cloudinary_image_meta}}';
    }

    public function rules()
    {
        return [
            [['cloudinary_media_id'], 'required'],
            [['cloudinary_media_id', 'width', 'height'], 'integer'],
            [['transformations'], 'string', 'max' => 255],
        ];
    }

    public function getMedia()
    {
        return $this->hasOne(CloudinaryMedia::class, ['id' => 'cloudinary_media_id']);
    }
}
