# 🧱 Cloudinary Media Migrations

This extension includes a set of database migrations to support Cloudinary media management within your Yii2 application.

---

## 🎯 Purpose

These migrations define tables that:

- Track each uploaded file’s metadata (`cloudinary_media`)
- Store multilingual titles and descriptions (`cloudinary_media_desc`)
- Record image-specific technical data like dimensions and transformations (`cloudinary_image_meta`)

These tables allow you to:

- Relate Cloudinary assets to your own models
- Store localized captions and titles
- Enable responsive and orientation-aware rendering

---

## ▶️ Running the Migration

From your project root, run:

```bash
php yii migrate --migrationPath=@yii2cloudinary/migrations
```

This will create:

- `cloudinary_media`
- `cloudinary_media_desc`
- `cloudinary_image_meta`

📖 For relation examples, see `saveUploadRecord()` usage in the [README](./README.md).

---

## 📁 Tables Created

| Table | Description |
|-------|-------------|
| `cloudinary_media` | Core metadata for all uploaded Cloudinary assets |
| `cloudinary_media_desc` | Localized titles and captions for media |
| `cloudinary_image_meta` | Image-specific attributes like dimensions and transformations |

---

## 📦 Table Structure

These tables are designed to be extensible and normalized for future Cloudinary features.

### `cloudinary_media`

Stores core metadata about the Cloudinary resource.

| Column         | Type                | Description |
|----------------|---------------------|-------------|
| `id`           | PK, int unsigned    | Primary key |
| `public_id`    | string(255), unique | Cloudinary asset ID |
| `resource_type`| string(50)          | `image`, `video`, `raw`, etc. |
| `format`       | string(20)          | File format (`jpg`, `png`, `mp4`, etc.) |
| `bytes`        | int                 | File size in bytes |
| `order`        | int unsigned        | Sort order (default: 999) |
| `created_at`   | timestamp           | Creation timestamp |
| `updated_at`   | timestamp           | Auto-updated on modification |
| `published`    | boolean unsigned    | Visibility flag (default: 1) |
| `secure_url`   | string(500)         | HTTPS delivery URL |
| `version`      | int unsigned        | Cloudinary version (used for cache busting) |

#### 🧪 Example Row

| id | public_id        | format | resource_type | bytes  | version  | published |
|----|------------------|--------|---------------|--------|----------|-----------|
| 1  | user_photos/abc  | jpg    | image         | 123456 | 16890123 | 1         |

---

### `cloudinary_media_desc`

Stores localized titles and descriptions for each media item.

| Column               | Type             | Description |
|----------------------|------------------|-------------|
| `id`                 | PK, int unsigned | Primary key |
| `cloudinary_media_id`| FK, int unsigned | References `cloudinary_media(id)` |
| `lang`               | string(5)        | Language code (`en`, `fr`, etc.) |
| `title`              | string(100)      | Title of the media |
| `description`        | string(200)      | Caption or description |

- On delete: `CASCADE` — descriptions are automatically removed if the parent media is deleted.

---

### `cloudinary_image_meta`

Holds image-specific metadata to assist with responsive rendering or filtering.

| Column               | Type               | Description |
|----------------------|--------------------|-------------|
| `id`                 | PK, int unsigned   | Primary key |
| `cloudinary_media_id`| FK, int unsigned   | References `cloudinary_media(id)` — unique |
| `width`              | int unsigned       | Image width in pixels |
| `height`             | int unsigned       | Image height in pixels |
| `transformations`    | string(255)        | Optional transformation string (e.g., `c_fill,g_auto,w_800`) — used to store predefined transformations for display (e.g., square crop, blur thumb, etc.) |

- This table is used only for images.
- Each `cloudinary_media` row can have **at most one** corresponding meta record.

---

## 🔄 Suggested Model Relation Example

```php
// Allows Post::getMedia() to access CloudinaryMedia via pivot
public function getMedia()
{
    return $this->hasMany(CloudinaryMedia::class, ['id' => 'media_id'])
        ->viaTable('post_gallery', ['post_id' => 'id']);
}
```

This allows you to attach images to models like `Post`, `Product`, etc., with multilingual metadata and responsive rendering.

---

## 🔐 Data Privacy

These tables store only metadata returned by Cloudinary (e.g. dimensions, format, secure URL).  
No sensitive credentials such as your API secret are ever persisted.

---

## 📚 References

- [Yii2 Migrations Guide](https://www.yiiframework.com/doc/guide/2.0/en/db-migrations)
- [Cloudinary Upload API Reference](https://cloudinary.com/documentation/image_upload_api_reference)
- [README: `saveUploadRecord()` and model relations](./README.md)
