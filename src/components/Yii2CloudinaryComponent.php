<?php

namespace yii2cloudinary\components;

use Yii;
use yii\base\Component;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;

use yii\db\Exception;
use yii\db\Transaction;
use yii2cloudinary\models\CloudinaryMedia;
use yii2cloudinary\models\CloudinaryImageMeta;

class Yii2CloudinaryComponent extends Component
{
    public string $cloudName;
    public string $apiKey;
    public string $apiSecret;
    public string $uploadPreset;
    public string $uploadHandlerUrl = '/yii2cloudinary/upload-handler';
    public bool $db_defaultPublished = true;
    public int $db_defaultOrder = 999;

    private Cloudinary $cloudinary;

    private array $defaultWidgetOptions = [
        // Required (set dynamically in code)
        // 'cloudName' => '',
        // 'uploadPreset' => '',

        // Widget paramaters
        'sources' => ['local', 'url', 'camera'],
        'defaultSource' => 'local',
        'secure' => true,
        'multiple' => false,
        'maxFiles' => 10,

        // Upload
        'folder' => '',
        'tags' => [],
        'context' => [], // e.g. ['alt' => 'image alt', 'caption' => 'caption']
        'resourceType' => 'auto',
        'publicIdPrefix' => '',
        'useAssetFolderAsPublicIdPrefix' => false,

        // Client-side constraints
        'clientAllowedFormats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'maxFileSize' => 10485760, // 10MB
        'maxImageWidth' => 2000,
        'maxImageHeight' => 2000,
        'validateMaxWidthHeight' => false,
        'minImageWidth' => null,
        'minImageHeight' => null,

        // Cropping (disabled by default)
        'cropping' => false,
        'croppingAspectRatio' => null,
        'croppingDefaultSelectionRatio' => 1.0,
        'croppingShowDimensions' => false,
        'croppingCoordinatesMode' => 'custom',
        'croppingShowBackButton' => true,
        'showSkipCropButton' => true,

        // Thumbnails
        'thumbnailTransformation' => [
            ['width' => 150, 'height' => 150, 'crop' => 'fit']
        ],

        // Containing page integration
        'form' => null,
        'fieldName' => 'upload[]',
        'thumbnails' => '.uploaded-thumbnails',

        // Look and feel
        'theme' => 'default',
        'styles' => [],
        'language' => 'en',
        'buttonClass' => 'cloudinary-button',
        'buttonCaption' => 'Upload image',

        // Behavior
        'autoMinimize' => false,
        'singleUploadAutoClose' => true,
        'showCompletedButton' => false,
        'showUploadMoreButton' => true,
        'showAdvancedOptions' => false,
        'showPoweredBy' => true,
        'queueViewPosition' => 'right:35px',
        'showInsecurePreview' => false,

        // Advanced hooks (leave null unless used)
        'getTags' => null,
        'getUploadPresets' => null,
        'preBatch' => null,
        'prepareUploadParams' => null,
    ];

    public function init(): void
    {
        parent::init();

        if (!$this->cloudName || !$this->apiKey || !$this->apiSecret) {
            throw new \RuntimeException("Missing Cloudinary credentials: please check 'cloudName', 'apiKey', and 'apiSecret'.");
        }

        $config = Configuration::instance([
            'cloud' => [
                'cloud_name' => $this->cloudName,
                'api_key'    => $this->apiKey,
                'api_secret' => $this->apiSecret
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        $this->cloudinary = new Cloudinary($config);
    }

    public function upload(string $filePath, array $options = []): array
    {
        $response = $this->cloudinary->uploadApi()->upload($filePath, $options);
        $data = $response->getArrayCopy();

        $model = $this->saveUploadRecord($data, $options);

        if ($model === null) {
            Yii::error([
                'uploadDbFailure' => 'Failed to save media record after Cloudinary upload.',
                'data' => $data,
            ], 'yii2cloudinary.upload');
        }

        return [
            'data' => $data,
            'model' => $model,
        ];
    }


    public function getCloudinary(): Cloudinary
    {
        return $this->cloudinary;
    }

    public function getUploadWidgetText(?string $lang = null): array
    {
        $lang = $lang ?? Yii::$app->language;

        // Try component’s translation for current language
        $defaultPath = __DIR__ . "/messages/{$lang}/uploadWidget.php";
        $default = file_exists($defaultPath) ? require $defaultPath : [];

        // Fallback to English if missing and not already using English
        if (empty($default) && $lang !== 'en') {
            $defaultPath = __DIR__ . "/messages/en/uploadWidget.php";
            $default = file_exists($defaultPath) ? require $defaultPath : [];
        }

        // User supplied override
        $customPath = Yii::getAlias("@app/messages/{$lang}/uploadWidget.php");
        $custom = file_exists($customPath) ? require $customPath : [];

        // Flatten → unflatten (stripping uploader. prefix)
        $decode = function (array $flat): array {
            $nested = [];
            foreach ($flat as $key => $value) {
                if (!str_starts_with($key, 'uploader.')) continue;

                $parts = explode('.', substr($key, 9));
                $ref = &$nested;
                foreach ($parts as $part) {
                    $ref = &$ref[$part];
                }
                $ref = $value;
                unset($ref);
            }
            return $nested;
        };

        return array_replace_recursive(
            $decode($default),
            $decode($custom)
        );
    }

    public function uploadWidget(string $buttonId = 'upload_widget', array $widgetOptions = [], ?string $uploadHandlerUrl = null): void
    {
        $view = Yii::$app->getView();

        $view->registerJsFile(
            'https://upload-widget.cloudinary.com/latest/global/all.js',
            ['position' => \yii\web\View::POS_HEAD]
        );

        $options = array_merge(
            [
                'text' => $this->getUploadWidgetText(),
                'cloudName' => $this->cloudName,
                'uploadPreset' => $this->uploadPreset,
            ],
            $this->defaultWidgetOptions,
            $widgetOptions
        );

        $jsonOptions = json_encode($options);
        $endpoint = $uploadHandlerUrl ?? $this->uploadHandlerUrl;

        $js = <<<JS
        var cloudinaryUploadWidget = cloudinary.createUploadWidget($jsonOptions, function(error, result) {
            if (!error && result && result.event === "success") {
                console.log("Upload successful:", result.info);
                fetch('$endpoint', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(result.info)
                });
            }
        });

        document.getElementById("$buttonId").addEventListener("click", function () {
            cloudinaryUploadWidget.open();
        }, false);
        JS;

        $view->registerJs($js, \yii\web\View::POS_END);
    }


    public function saveUploadRecord(array $data, array $options = []): ?CloudinaryMedia
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $media = new CloudinaryMedia();
            $media->public_id = $data['public_id'] ?? null;
            $media->resource_type = $data['resource_type'] ?? null;
            $media->format = $data['format'] ?? null;
            $media->bytes = $data['bytes'] ?? null;
            $media->version = $data['version'] ?? null;
            $media->secure_url = $data['secure_url'] ?? null;

            $timestamp = isset($data['created_at']) ? (new \DateTime($data['created_at']))->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
            $media->created_at = $timestamp;
            $media->updated_at = $timestamp;

            $media->order = $options['order'] ?? $this->db_defaultOrder;
            $media->published = $options['published'] ?? $this->db_defaultPublished;

            if (!$media->save()) {
                Yii::error([
                    'saveMediaError' => $media->getErrors(),
                    'data' => $data,
                ], 'yii2cloudinary.saveUploadRecord');
                $transaction->rollBack();
                return null;
            }

            if (
                isset($data['width'], $data['height'])
                && $data['resource_type'] === 'image'
            ) {
                $image = new CloudinaryImageMeta();
                $image->cloudinary_media_id = $media->id;
                $image->width = $data['width'];
                $image->height = $data['height'];

                if (!$image->save()) {
                    Yii::error([
                        'saveImageMetaError' => $image->getErrors(),
                        'data' => $data,
                    ], 'yii2cloudinary.saveUploadRecord');
                    $transaction->rollBack();
                    return null;
                }
            }

            $transaction->commit();
            return $media;

        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error([
                'uploadDbException' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'yii2cloudinary.saveUploadRecord');
            return null;
        }
    }

    public function renderResponsiveImage(
        \yii2cloudinary\models\CloudinaryMedia $media,
        array $widths = [400, 800, 1200],
        array $htmlOptions = [],
        ?string $aspectRatio = null, // e.g. '1:1', '4:3'
        string $format = 'auto'
    ): string {
        $meta = $media->imageMeta ?? null;

        if (!$meta || !$meta->width || !$meta->height || !$media->public_id || !$media->format) {
            return '';
        }

        $cloudName = $this->cloudName ?? throw new \RuntimeException("Cloudinary cloud_name not set.");

        $base = "https://res.cloudinary.com/{$cloudName}/image/upload";

        // Build Cloudinary transform: base is q_auto
        $baseTransform = ['q_auto'];

        if ($aspectRatio) {
            $baseTransform[] = 'ar_' . $aspectRatio;
            $baseTransform[] = 'g_auto';
            $baseTransform[] = 'c_fill';
        } else {
            // auto aspect detection if width/height are set
            if ($meta->width && $meta->height) {
                if ($meta->width > $meta->height) {
                    $baseTransform[] = 'ar_4:3';
                } elseif ($meta->height > $meta->width) {
                    $baseTransform[] = 'ar_3:4';
                }
                if ($meta->width !== $meta->height) {
                    $baseTransform[] = 'g_auto';
                    $baseTransform[] = 'c_fill';
                } else {
                    $baseTransform[] = 'c_scale'; // it's square already
                }
            } else {
                $baseTransform[] = 'c_scale';
            }
        }


        // Handle format logic
        $formatExtension = null;

        if ($format === 'auto') {
            $baseTransform[] = 'f_auto'; // smart browser format
            $formatExtension = null;     // no extension
        } else {
            $baseTransform[] = "f_{$format}"; // e.g. f_webp
            $formatExtension = $format;       // use explicit extension
        }
        $formatSuffix = $formatExtension ? ".{$formatExtension}" : '';


        $srcset = [];
        foreach ($widths as $w) {
            $transformStr = implode(',', array_merge($baseTransform, ["w_{$w}"]));
            $url = "{$base}/{$transformStr}/{$media->public_id}{$formatSuffix}";
            $srcset[] = "{$url} {$w}w";
        }

        $maxWidth = max($widths);
        $defaultTransform = implode(',', array_merge($baseTransform, ["w_{$maxWidth}"]));
        $defaultSrc = "{$base}/{$defaultTransform}/{$media->public_id}{$formatSuffix}";

        $sizes = $htmlOptions['sizes'] ?? '(min-width: 768px) 33vw, 100vw';

        $altText = '';
        foreach ($media->descriptions as $desc) {
            if ($desc->lang === Yii::$app->language && !empty($desc->description)) {
                $altText = $desc->description;
                break;
            }
        }
        if ($altText === '') {
            foreach ($media->descriptions as $desc) {
                if (!empty($desc->description)) {
                    $altText = $desc->description;
                    break;
                }
            }
        }

        $attrs = array_merge([
            'src' => $defaultSrc,
            'srcset' => implode(', ', $srcset),
            'width' => $meta->width,
            'height' => $meta->height,
            'alt' => $altText,
            'loading' => 'lazy',
        ], $htmlOptions);

        unset($attrs['sizes']); // remove override so we only render once below

        $attrString = '';
        foreach ($attrs as $key => $value) {
            $escaped = htmlspecialchars($value, ENT_QUOTES);
            $attrString .= " {$key}=\"{$escaped}\"";
        }

        return "<img{$attrString} sizes=\"{$sizes}\">";
    }



}
