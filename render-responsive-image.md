# 📷 `renderResponsiveImage()` Usage Guide

The `renderResponsiveImage()` method in the `Yii2CloudinaryComponent` provides a convenient way to generate responsive `<img>` tags with automatic `srcset`, orientation-aware rendering, and Cloudinary transformation support.

---

## ✅ Purpose

Render optimized, responsive images in Yii2 views — including automatic:

- Cloudinary delivery URLs
- Multiple resolutions via `srcset`
- Portrait vs. landscape layout detection (if meta available)
- Transformation options (e.g. crop, gravity, quality)
- Custom HTML attributes (`class`, `alt`, `loading`, etc.)

---

## 🧪 Method Signature

```php
public function renderResponsiveImage(
    CloudinaryMedia $media,
    array $widths = [],
    array $htmlOptions = [],
    array $transformation = []
): string
```

---

## 🧾 Parameters

| Param             | Type               | Description |
|------------------|--------------------|-------------|
| `$media`          | `CloudinaryMedia`  | The media model (must include `public_id`, `format`, etc.) |
| `$widths`         | `array`            | Image widths to include in the `srcset`. Example: `[400, 800]` |
| `$htmlOptions`    | `array`            | HTML attributes for the generated `<img>` tag |
| `$transformation` | `array`            | Cloudinary transformation options (`crop`, `quality`, `gravity`, etc.) |

---

## 🖼 Example Usage

### Basic

```php
echo Yii::$app->yii2cloudinary->renderResponsiveImage($media, [400, 800], [
    'class' => 'img-fluid',
    'alt' => 'Product photo',
]);
```

### With Cloudinary Transformations

```php
echo Yii::$app->yii2cloudinary->renderResponsiveImage($media, [400, 800], [
    'class' => 'img-thumbnail',
    'loading' => 'lazy',
], [
    'crop' => 'fill',
    'gravity' => 'auto',
    'quality' => 'auto',
]);
```

---

## ⚙️ Behavior

- If `$widths` is empty, the method calculates a set of responsive widths based on the image’s original dimensions and orientation. Upscaling is automatically avoided.
- Automatically infers:
  - File format and version
  - Orientation (landscape/portrait) if `cloudinary_image_meta` is available
- Generates:
  - `srcset` with all provided widths
  - `sizes="100vw"` for responsive layouts

---

## 💡 Tips

- Use widths that match your layout breakpoints.
- Add `loading="lazy"` to defer off-screen image loading.
- Combine with CSS aspect-ratio utilities (e.g. Tailwind or custom classes).
- Include `alt` and `title` attributes for accessibility and SEO.
- Use the image meta table to store dimensions for orientation-based rendering.

---

## 🧪 Example Output

```html
<img
  src="https://res.cloudinary.com/demo/image/upload/c_fill,g_auto,w_400/v1234567890/sample.jpg"
  srcset="
    https://res.cloudinary.com/demo/image/upload/c_fill,g_auto,w_400/v1234567890/sample.jpg 400w,
    https://res.cloudinary.com/demo/image/upload/c_fill,g_auto,w_800/v1234567890/sample.jpg 800w
  "
  sizes="100vw"
  class="img-fluid"
  alt="Product photo"
/>
```

---

## 🧱 Requirements

The `$media` model should provide:

- `public_id` *(required)*
- `format` *(required)*
- `version` *(recommended)*
- Requires a related `imageMeta` model (via `cloudinary_image_meta`) with valid `width` and `height` values. This is essential for generating responsive widths and detecting orientation

---

## 🛠 Customization

For advanced scenarios:

- Build Cloudinary URLs manually using:
  ```php
  Yii::$app->yii2cloudinary->getCloudinary()->image('public_id')->toUrl();
  ```

- Or build your own `<img>` using `Html::img()` + custom `srcset`.

---

## 📚 Related

- [Cloudinary Image Transformations](https://cloudinary.com/documentation/image_transformations)
- [HTML `srcset` and `sizes`](https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images)
- [Cloudinary Media Migrations](./cloudinary-media-migrations.md)
