# Yii2Cloudinary

Cloudinary integration for the Yii2 PHP Framework.

This extension provides a Yii2-friendly wrapper around the official [Cloudinary PHP SDK](https://github.com/cloudinary/cloudinary_php), allowing you to easily upload and manage images or other media within your Yii2 application.

---

## 🛠 Features

- Upload files to Cloudinary from your Yii2 app
- Autoconfigures Cloudinary credentials via Yii component
- Easy to integrate into forms, controllers, and services
- Migration-ready schema for media metadata and multilingual support
- Built-in support for the Cloudinary Upload Widget
- Modular routing support via Yii2 modules

---

## 📦 Installation

Install via Composer:

```bash
composer require bgillieron/yii2-cloudinary
```

---

## 🔧 Configuration

Add the **module** and **component** to your application config:

```php
'modules' => [
    'cloudinary' => [
        'class' => 'yii2cloudinary\Module',
    ],
],

'components' => [
    'yii2cloudinary' => [
        'class' => \yii2cloudinary\components\Yii2CloudinaryComponent::class,
        'cloudName' => 'your-cloud-name',
        'apiKey' => 'your-api-key',
        'apiSecret' => 'your-api-secret',
        'uploadPreset' => 'your-upload-preset',
    ],
],
```

---

## 🚀 Usage

### Uploading a File

```php
Yii::$app->yii2cloudinary->upload('/absolute/path/to/image.jpg', [
    'folder' => 'my-uploads',
    'tags' => ['gallery', 'profile'],
]);
```

This will upload the file to your Cloudinary account under the specified folder and tags.

### Getting the Cloudinary Object (for advanced features)

```php
$cloudinary = Yii::$app->yii2cloudinary->getCloudinary();
$url = $cloudinary->image('my-image-public-id')->toUrl();
```

### Rendering the Upload Widget

```php
Yii::$app->yii2cloudinary->uploadWidget('upload_widget');
```

The widget will automatically:
- Register required Cloudinary JS
- Use your configured options or override via parameters
- Trigger a default or custom callback to a controller (e.g. `/cloudinary/yii2-cloudinary/upload-handler`)

---

## 🧱 Database Migration

To create the default `cloudinary_media` tables (including multilingual descriptions and image metadata), run:

```bash
php yii migrate --migrationPath=@yii2cloudinary/migrations
```

This will create the following tables:

- `cloudinary_media`
- `cloudinary_media_desc`
- `cloudinary_image_meta`

---

## 🗂 Directory Structure

```
yii2-cloudinary/
├── src/
│   ├── Module.php
│   ├── components/
│   │   └── Yii2CloudinaryComponent.php
│   ├── controllers/
│   │   └── Yii2CloudinaryController.php
│   └── messages/
│       └── en/
│           └── uploadWidget.php
├── migrations/
│   └── m240430_120000_create_cloudinary_media_tables.php
├── composer.json
├── README.md
└── LICENSE
```

---

## 📝 License

MIT © Brendon Gilliéron
