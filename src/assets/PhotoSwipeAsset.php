<?php
namespace yii2cloudinary\assets;

use yii\web\AssetBundle;

class PhotoSwipeAsset extends AssetBundle
{
    public $sourcePath = '@yii2cloudinary/assets/photoswipe';

    public $css = [
        'photoswipe.css',
    ];

    public $js = [
        'photoswipe.esm.min.js',
        'photoswipe-lightbox.esm.min.js',
    ];

    public $jsOptions = [
        'type' => 'module', // Needed for ES module files like Lightbox
    ];
}
