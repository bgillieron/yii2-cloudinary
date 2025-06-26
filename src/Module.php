<?php

namespace yii2cloudinary;

use Yii;

/**
 * Yii2Cloudinary module class.
 *
 * Register this module in your application's config to enable routing to its controllers:
 *
 * Example:
 * 'modules' => [
 *     'cloudinary' => [
 *         'class' => 'yii2cloudinary\Module',
 *     ],
 * ],
 *
 * Then access your controller actions via:
 * /cloudinary/yii2-cloudinary/upload-handler
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'yii2cloudinary\controllers';

    public function init()
    {
        parent::init();

        if (!Yii::getAlias('@yii2cloudinary', false)) {
            Yii::setAlias('@yii2cloudinary', __DIR__);
        }

        // Automatically register migration namespace
        if (Yii::$app instanceof \yii\console\Application) {
            Yii::$app->controllerMap['migrate']['migrationNamespaces'][] = 'yii2cloudinary\migrations';
        }
    }
}
