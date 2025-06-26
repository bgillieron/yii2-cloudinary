## 🌍 Upload Widget Localization (Language Support)

The `Yii2CloudinaryComponent` includes full support for multi-language localization of the Cloudinary Upload Widget via the `text` option.

### 🧠 Localization Workflow

1. **Default translations** in English are provided by the component (sourced from [Cloudinary's `text.json`](https://upload-widget.cloudinary.com/latest/global/text.json)):

   ```
   vendor/yii2-cloudinary/src/messages/en/uploadWidget.php
   ```

2. You can override or extend these translations by creating your own file in your application:

   ```
   @app/messages/<lang>/uploadWidget.php
   ```

3. The component merges both files using `array_replace_recursive()`.  
   This means your app's file takes precedence and only needs to include the keys you wish to override.

4. Translations are defined using dot-notation keys prefixed with `uploader.`.  
   These are automatically **flattened and nested** to match Cloudinary’s expected `text` structure.

   > Example: `'uploader.queue.title'` → becomes `$text['queue']['title']`

5. You can also override translations **at runtime** by passing a `text` array to the `uploadWidget()` method.

---

### 📦 Translation File Format

Create `messages/fr/uploadWidget.php` like this:

```php
<?php
return [
    'uploader.actions.log_out' => 'Se déconnecter',
    'uploader.queue.title' => 'File d’attente',
    'uploader.default.back' => 'Retour',
];
```

The component will:
- Strip the `uploader.` prefix
- Convert the flat keys to nested arrays
- Inject the result into the Cloudinary widget's `text` option

💡 You can copy the provided English file (`messages/en/uploadWidget.php`) as a starting point for your own translations.

---

### 🧩 Runtime Text Overrides

You can override specific translations at runtime when calling `uploadWidget()`:

```php
Yii::$app->yii2cloudinary->uploadWidget('upload_widget', [
    'text' => [
        'queue' => [
            'title' => 'Custom Upload Queue Title',
        ],
        'default' => [
            'back' => 'Go Back',
        ],
        'actions' => [
            'log_out' => 'Sign out',
        ],
    ]
]);
```

This is especially useful when you want to customize labels per context (e.g., per view, per role, or user locale).

💡 You can also force a specific language via:
```php
Yii::$app->language = 'fr';
```

---

### 🔁 Fallback Behavior

If no language file is found for the current `Yii::$app->language`,  
the component will automatically fall back to its internal English (`en`) file.

---

### ✅ Resolution Priority Summary

| Step | Source | Description |
|------|--------|-------------|
| 🥉 Step 1 | `@yii2cloudinary/messages/<lang>/uploadWidget.php` | Component's default translations for the current language (`<lang>`), or English (`en`) if not available. |
| 🥈 Step 2 | `@app/messages/<lang>/uploadWidget.php` | Application-provided translations override the component's defaults. Only partial overrides are needed. |
| 🥇 Step 3 | `uploadWidget()` `text` option | Explicit runtime overrides passed when calling `uploadWidget()`. Highest priority. |

This layered system provides maximum flexibility while ensuring sensible defaults for all languages.

---

### 📊 Example: Translating `'uploader.queue.title'`

Let’s say you're translating the Upload Widget label for `'Upload Queue'`.

#### 🥉 Step 1: Component Default (EN)
```php
// vendor/yii2-cloudinary/src/messages/en/uploadWidget.php
return [
    'uploader.queue.title' => 'Upload Queue',
];
```

#### 🥈 Step 2: User Override (FR)
```php
// @app/messages/fr/uploadWidget.php
return [
    'uploader.queue.title' => 'File d’attente personnalisée',
];
```

#### 🥇 Step 3: Runtime Override (in View or Controller)
```php
Yii::$app->yii2cloudinary->uploadWidget('upload_widget', [
    'text' => [
        'queue' => [
            'title' => 'File dynamique temporaire',
        ],
    ]
]);
```

#### ✅ Result:
- If the current language is `fr`, and all 3 layers are in place:
  - ✅ The widget will display: **"File dynamique temporaire"**
- If the runtime override is removed:
  - ✅ It will fall back to: **"File d’attente personnalisée"**
- If neither override is provided:
  - ✅ It will fall back to: **"Upload Queue"**
