# Yii2Cloudinary

Cloudinary integration for the Yii2 PHP Framework.

This extension provides a Yii2-friendly wrapper around the official [Cloudinary PHP SDK](https://github.com/cloudinary/cloudinary_php), enabling seamless uploads, media management, and responsive image rendering in Yii2 projects.

---

## 📁 Project Structure

```plaintext
yii2-cloudinary/
├── src/
│   ├── assets/
│   │   ├── photoswipe/
│   │   │   ├── photoswipe-lightbox.esm.min.js
│   │   │   ├── photoswipe.esm.min.js
│   │   │   ├── photoswipe.css
│   │   └── PhotoSwipeAsset.php
│   ├── components/
│   │   └── Yii2CloudinaryComponent.php
│   ├── controllers/
│   │   └── Yii2CloudinaryController.php
│   ├── messages/
│   │   ├── en/
│   │   │   └── uploadWidget.php
│   │   └── text.json
│   ├── migrations/
│   │   └── m250430_120000_create_cloudinary_media_tables.php
│   ├── models/
│   │   ├── CloudinaryImageMeta.php
│   │   ├── CloudinaryMedia.php
│   │   ├── CloudinaryMediaDesc.php
│   │   └── Module.php
├── cloudinary-media-migrations.md
├── composer.json
├── LICENSE
├── README.md
├── render-responsive-image.md
└── upload-widget-localization.md
```

---

## 🛠 Features

- Upload images and files to Cloudinary with a single call
- Automatically configure credentials via Yii component
- Drop-in Upload Widget integration with runtime options
- Responsive image rendering with automatic `srcset`
- Orientation-aware rendering via image meta
- PhotoSwipe support for lightbox galleries
- Database schema for multilingual media metadata
- Easily relate uploaded media to your own models
- Fully translatable Upload Widget UI

---

## 📦 Installation

Install the extension via Composer:

```bash
composer require bgillieron/yii2-cloudinary
```

---

## 🔧 Configuration

Add the module and component to your Yii2 config:

```php
'modules' => [
    'cloudinary' => [
        'class' => 'yii2cloudinary\Module',
    ],
],
'components' => [
    'yii2cloudinary' => [
        'class' => \yii2cloudinary\components\Yii2CloudinaryComponent::class,
        'cloudName' => $_ENV['CLOUDINARY_CLOUD_NAME'],
        'apiKey' => $_ENV['CLOUDINARY_API_KEY'],
        'apiSecret' => $_ENV['CLOUDINARY_API_SECRET'],
        'uploadPreset' => $_ENV['CLOUDINARY_UPLOAD_PRESET'],
        'uploadHandlerUrl' => '/cloudinary/yii2-cloudinary/upload-handler',
        'db_defaultPublished' => false,
        'db_defaultOrder' => 500,
        'relationSaverMap' => [
            'test-widget' => function ($media) {
                Yii::$app->db->createCommand()->insert('test_media_relation', [
                    'media_id' => $media->id,
                    'label' => 'Uploaded from test widget',
                ])->execute();
            },
        ],
    ],
],
```

---

## 🧱 Migrations

To create the necessary tables for storing Cloudinary media and metadata, run:

```bash
php yii migrate --migrationPath=@yii2cloudinary/migrations
```

This will create:

- `cloudinary_media`
- `cloudinary_media_desc`
- `cloudinary_image_meta`

📖 For full details on table structure and purpose, see [Cloudinary Media Migrations](./cloudinary-media-migrations.md).

---

## 🚀 Media Usage

### Uploading Files (Server-Side)

```php
Yii::$app->yii2cloudinary->upload('/path/to/image.jpg', [
    'folder' => 'my-uploads',
    'tags' => ['profile', 'gallery'],
    'relationKey' => 'post-image',
]);
```

This method:

- Uploads the file to Cloudinary
- Saves the `cloudinary_media` record
- If `relationKey` is provided and defined in your config, invokes the matching relation handler

### 🔄 Uploading with Custom Payload

You can pass extra metadata to control how the upload is saved:

```php
Yii::$app->yii2cloudinary->upload('/path/to/image.jpg', [
    'folder' => 'product-images',
    'customPayload' => [
        'order' => 1,
        'published' => true,
        'product_id' => 123,
    ],
    'relationKey' => 'product-gallery',
]);
```

- `customPayload` is forwarded to both the database save and your `relationSaverMap` callback.
- This allows custom logic or model relation setup based on the upload context.

---

### 🔁 Saving Upload Result & Creating Relations

When a file is uploaded to Cloudinary, the extension automatically saves a corresponding record in the database (`cloudinary_media`) — but in most real-world projects, you’ll also want to associate that file with a specific model (like `Post`, `Product`, `User`, etc.).

To keep your application logic clean and flexible, this extension introduces the concept of **relation callbacks**, defined via the `relationSaverMap`.

#### 🧠 Why Use `relationSaverMap`?

- You may need different logic for different upload contexts (e.g. a product image, an avatar, a gallery).
- Each upload (or widget instance) can pass a `relationKey` to identify which logic should run.
- You configure these mappings once in your app config — no need to pass closures at runtime.

#### 🗂️ Define Any Number of Relation Handlers

```php
'relationSaverMap' => [
    'product-gallery' => function ($media, $payload) {
        Yii::$app->db->createCommand()->insert('product_media', [
            'media_id' => $media->id,
            'product_id' => $payload['product_id'] ?? null,
            'position' => $payload['order'] ?? 1,
        ])->execute();
    },
    'user-avatar' => fn($media) => Yii::$app->user->identity->link('avatar', $media),
    'post-image' => fn($media) => Post::findOne(5)->link('media', $media),
],
```

---

### Rendering a Responsive Image

```php
echo Yii::$app->yii2cloudinary->renderResponsiveImage($media, [400, 800], [
    'class' => 'img-fluid',
]);
```

- Generates `<img>` tag with optimized Cloudinary `srcset`
- Optional aspect ratio detection (e.g. `'4:3'`, `'1:1'`)
- Orientation-aware layout using image meta (landscape vs portrait)
- Lazy loading and format auto-selection supported

📖 See [Render Responsive Image](./render-responsive-image.md) for advanced usage and options.

---

## 🖼 Upload Widget & UI

### Rendering the Upload Widget

```php
Yii::$app->yii2cloudinary->uploadWidget('upload_widget', [
    'relationKey' => 'product-gallery',
    'customPayload' => [
        'product_id' => 123,
        'order' => 1,
        'published' => true,
    ],
    'reloadAfterClose' => true,
]);
```

This registers the Cloudinary Upload Widget and binds it to the DOM element with the given ID.

Key behaviors:

- Automatically registers and loads Cloudinary’s JavaScript dependencies
- Uploads files to Cloudinary and sends metadata to your configured `uploadHandlerUrl` endpoint, which persists the media and optionally links it using your `relationSaverMap`
- If a `relationKey` is passed, it is forwarded to the server to trigger a matching callback from your `relationSaverMap`
- `customPayload` is included in the upload metadata for advanced save logic
- `reloadAfterClose` reloads the page when the widget is closed (optional)
- You can also pass `text` overrides for localization

---

#### 🔁 Upload Handler URL

When a file is uploaded via the widget, Cloudinary sends the file to their CDN,  
then triggers a callback with metadata that is posted to your app’s upload handler.

By default, this handler is set to:

```php
'uploadHandlerUrl' => '/yii2cloudinary/upload-handler',
```

You can override this in your component config if needed:

```php
'uploadHandlerUrl' => '/custom/path/to/upload-handler',
```

This endpoint must match the route defined by your `Yii2CloudinaryController::actionUploadHandler()`  
and is responsible for saving the uploaded media record and executing your `relationSaverMap` logic.  
Only change this if absolutely necessary — overriding it incorrectly will break key functionality of this module.

---

### 🌍 Upload Widget Localization

This extension supports:

- Default English UI via embedded translations
- App-level language overrides via `messages/<lang>/uploadWidget.php`
- Runtime `text` overrides passed to `uploadWidget()`

📖 See [Upload Widget Localization](./upload-widget-localization.md) for a complete guide.

---

### 🖼 Lightbox Support (PhotoSwipe)

#### Register the ESM Asset Bundle

```php
use yii2cloudinary\assets\PhotoSwipeAsset;

PhotoSwipeAsset::register($this);
```

#### Initialize in View

```html
<?php $baseUrl = $this->assetManager->getBundle(\yii2cloudinary\assets\PhotoSwipeAsset::class)->baseUrl; ?>

<script type="module">
import PhotoSwipeLightbox from '<?= $baseUrl ?>/photoswipe-lightbox.esm.min.js';
import PhotoSwipe from '<?= $baseUrl ?>/photoswipe.esm.min.js';

const lightbox = new PhotoSwipeLightbox({
    gallery: '.my-gallery',
    children: 'a',
    pswpModule: PhotoSwipe,
    padding: 20,
    showHideAnimationType: 'zoom',
});

lightbox.init();
</script>
```

📌 _ES modules require explicit `import` and do not auto-run._

---

## ⚙️ Advanced Access

### SDK Access

```php
$cloudinary = Yii::$app->yii2cloudinary->getCloudinary();
$url = $cloudinary->image('your-public-id')->toUrl();
```

This returns the `Cloudinary\Cloudinary` instance for advanced transformations.

---

### Model Reference

These models support multilingual metadata and responsive rendering:

- [`CloudinaryMedia`](src/models/CloudinaryMedia.php)
- [`CloudinaryMediaDesc`](src/models/CloudinaryMediaDesc.php)
- [`CloudinaryImageMeta`](src/models/CloudinaryImageMeta.php)

---

## 📝 License

MIT License
© Brendon Gilliéron
