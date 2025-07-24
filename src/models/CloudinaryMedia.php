<?php

namespace yii2cloudinary\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "cloudinary_media".
 *
 * @property int $id
 * @property string $public_id
 * @property string|null $resource_type
 * @property string|null $format
 * @property int|null $bytes
 * @property int $order
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int $published
 * @property string|null $secure_url
 * @property int|null $version
 *
 * @property CloudinaryMediaDesc[] $descriptions
 * @property CloudinaryImageMeta $imageMeta
 */
class CloudinaryMedia extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%cloudinary_media}}';
    }

    public function rules()
    {
        return [
            [['public_id'], 'required'],
            [['public_id'], 'string', 'max' => 255],
            [['public_id'], 'unique'],
            [['resource_type'], 'string', 'max' => 50],
            [['format'], 'string', 'max' => 20],
            [['bytes', 'version'], 'integer'],
            [['order'], 'integer', 'min' => 0],
            [['created_at', 'updated_at'], 'safe'],
            [['published'], 'boolean'],
            [['secure_url'], 'string', 'max' => 500],
        ];
    }

    public function getDescriptions()
    {
        return $this->hasMany(CloudinaryMediaDesc::class, ['cloudinary_media_id' => 'id']);
    }

    public function getImageMeta()
    {
        return $this->hasOne(CloudinaryImageMeta::class, ['cloudinary_media_id' => 'id']);
    }

    public function getTranslation(string $attribute, ?string $language = null): ?string
    {
        $language = $language ?? \Yii::$app->language;

        foreach ($this->descriptions as $desc) {
            if ($desc->lang === $language) {
                return $desc->{$attribute} ?? null;
            }
        }

        return null;
    }

}
