<?php

namespace yii2cloudinary\migrations;

use yii\db\Migration;

/**
 * Handles the creation of tables:
 * - cloudinary_media (base values for all media, images, video, documents etc)
 * - cloudinary_media_desc (multilingual description for all media)
 * - cloudinary_image_meta (specific only to images)
 */
class m250430_120000_create_cloudinary_media_tables extends Migration
{
    public function safeUp()
    {
        // Create cloudinary_media table
        $this->createTable('{{%cloudinary_media}}', [
            'id' => $this->primaryKey()->unsigned(),
            'public_id' => $this->string(255)->notNull()->unique(),
            'resource_type' => $this->string(50),
            'format' => $this->string(20),
            'bytes' => $this->integer(),
            'order' => $this->integer()->unsigned()->defaultValue(999),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'published' => $this->boolean()->defaultValue(1)->unsigned(),
            'secure_url' => $this->string(500)->null(),
            'version' => $this->integer()->unsigned()->null(),
        ]);

        // Create cloudinary_media_desc table
        $this->createTable('{{%cloudinary_media_desc}}', [
            'id' => $this->primaryKey()->unsigned(),
            'cloudinary_media_id' => $this->integer()->unsigned()->notNull(),
            'lang' => $this->string(5)->notNull(),
            'title' => $this->string(100)->null(),
            'description' => $this->string(200),
        ]);

        $this->addForeignKey(
            'fk-cloudinary_media_desc-cloudinary_media_id',
            '{{%cloudinary_media_desc}}',
            'cloudinary_media_id',
            '{{%cloudinary_media}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Create cloudinary_image_meta table
        $this->createTable('{{%cloudinary_image_meta}}', [
            'id' => $this->primaryKey()->unsigned(),
            'cloudinary_media_id' => $this->integer()->unsigned()->notNull()->unique(),
            'width' => $this->integer()->unsigned(),
            'height' => $this->integer()->unsigned(),
            'transformations' => $this->string(255)->null(),
        ]);

        $this->addForeignKey(
            'fk-cloudinary_image_meta-cloudinary_media_id',
            '{{%cloudinary_image_meta}}',
            'cloudinary_media_id',
            '{{%cloudinary_media}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-cloudinary_image_meta-cloudinary_media_id', '{{%cloudinary_image_meta}}');
        $this->dropTable('{{%cloudinary_image_meta}}');

        $this->dropForeignKey('fk-cloudinary_media_desc-cloudinary_media_id', '{{%cloudinary_media_desc}}');
        $this->dropTable('{{%cloudinary_media_desc}}');

        $this->dropTable('{{%cloudinary_media}}');
    }
}
