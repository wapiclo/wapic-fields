A lightweight and developer-friendly custom fields system for WordPress.  
Supports **Options Page, Post Meta, and Taxonomy Term Meta** — built following WordPress standards, without heavy frameworks.

Easily add and manage fields like text, select, checkbox, image, gallery, editor, and more with full support for **conditional logic** and **client-side JS validation**.

---
## Features

- **Lightweight & Simple** – no bloat, just clean custom fields.  
- **Flexible** – works with post meta, options page, and term meta.  
- **Conditional Fields** – show, hide, enable, or disable fields dynamically.  
- **Built-in JS Validation** – user-friendly client-side validation.  
- **WordPress Standards** – fully compatible and extendable with WP core functions.  

---

## Supported Fields

Wapic Fields provides the following field types for WordPress. This list is for **quick reference**:

1. **Text** – Single-line text input  
2. **Textarea** – Multi-line text input  
3. **URL** – URL input with validation  
4. **Email** – Email input with validation  
5. **Number** – Numeric input  
6. **Phone** – Phone number input  
7. **Checkbox** – Single or multiple checkboxes  
8. **Radio** – Single choice selection  
9. **Toggle** – On/off switch  
10. **Select** – Dropdown select  
11. **Select2** – Enhanced select with search & multiple selection  
12. **Image** – Single image upload  
13. **Gallery** – Multiple images upload  
14. **File** – File upload  
15. **Color** – Color picker  
16. **Date** – Date picker  
17. **WP Editor** – WordPress rich text editor
18. **HTML** – HTML content

---

## Why Use This?

Unlike bulky frameworks, this project focuses on being:

- **Fast** → lightweight and minimal.  
- **Native** → built the WordPress way.  
- **Flexible** → easily extendable by developers.  

Perfect for **theme & plugin developers** who want powerful custom fields without unnecessary overhead.

---
## Installation
There are multiple ways to install Wapic Fields:
- Instalasion via composer (recommended) ```composer require wapiclo/wapic-fields```
- As a WordPress Plugin
[Installation instructions](https://github.com/wapiclo/wapic-fields/wiki/Installation?utm_source=chatgpt.com#as-wordpress-plugin)
- Include via File
[Include instructions](https://github.com/wapiclo/wapic-fields/wiki/Installation?utm_source=chatgpt.com#as-wordpress-plugin)

## For Usage
Here are examples to help you get started:

- Options Example: [Usage Example](https://github.com/wapiclo/wapic-fields/wiki/Usage#options-page-example)
- Metabox Example: [Usage Example](https://github.com/wapiclo/wapic-fields/wiki/Usage?#meta-box-example)
- Taxonomy Example: [Usage Example](https://github.com/wapiclo/wapic-fields/wiki/Usage#taxonomy-example)

## Field
You can use various field types provided by Wapic Fields:
[Field Types Documentation](https://github.com/wapiclo/wapic-fields/wiki/Field-Types)

## Example

We provide examples for Meta, Options, and Term Meta. The example files are included in the Examples/ folder.

To load the examples in the WordPress admin, add the following code to your main theme, plugin file or wp-config.php:

```define('WAPIC_FIELDS_LOAD_EXAMPLES', true);```

## Repeater fields

Repeaters render ordinary Wapic Fields controls and submit one nested array. The
Options Example includes a **Repeaters** tab. Repeater items always use a block layout;
no layout option is needed.
With `confirm_delete` enabled, the delete button opens a dark confirmation popover
with **Remove** and **Cancel**. The message is configurable with
`confirm_delete_message`; Escape or a click outside also cancels deletion.

```php
$features = [
    'id' => 'features',
    'type' => 'repeater',
    'label' => 'Features',
    'sortable' => true,
    'confirm_delete' => false,
    'confirm_delete_message' => 'Are you sure you want to delete this row?',
    'button_label' => 'Add Row',
    'min_rows' => 0,
    'max_rows' => 0, // Unlimited.
    'default_rows' => 0, // Used only when no saved array exists.
    'row_label' => '{{title}}', // Empty titles fall back to Features - 1, Features - 2, etc.
    'fields' => [
        ['id' => 'title', 'type' => 'text', 'label' => 'Title'],
        ['id' => 'show_link', 'type' => 'toggle', 'label' => 'Add link'],
        ['id' => 'link', 'type' => 'url', 'label' => 'URL',
         'condition' => ['field' => 'show_link', 'value' => 'yes']],
    ],
];

register_setting('my_settings', 'features', [
    'sanitize_callback' => static function ($value) use ($features) {
        return \Wapic_Fields\Field::sanitize_value('repeater', $value, $features);
    },
]);
$features['value'] = get_option('features');
\Wapic_Fields\Field::add_control($features);
```

Register settings on `admin_init` and render controls inside your existing settings
form. For post/term metadata, use the same sanitizer with the complete definition
before `update_post_meta()` or `update_term_meta()`; unslash values read directly
from `$_POST` before sanitizing. Settings API callbacks receive unslashed values.
An outer option array can use `name => 'my_options[features]'`.

Conditions inside a row refer to sibling logical IDs (`show_link` above). They
never read another row or a global field. Conditions on the repeater itself retain
the normal outer scope. Empty/missing label placeholders use the repeater label, or `Row` without a label.
The header displays the drag handle, collapse arrow, and row label. A label generated
from a field has no number; the fallback label appends a 1-based number. Existing rows load collapsed; newly added rows
open for editing. Collapsing keeps values enabled for saving;
validation expands a collapsed row when a field needs attention and shows a warning
icon after its heading. Failed submissions display a WordPress error notice above
the form instead of a browser alert.
Tabs with invalid fields display a warning and error count after submission.
Each failed submission opens the first invalid tab in navigation order; once its
fields are corrected, the next submission opens the next tab needing attention.
If a positive `max_rows` is smaller than `min_rows`, the minimum takes precedence.
Saved empty arrays remain empty, unless `min_rows` requires rows.

Dynamic controls reuse the `wcf_fields_initialized` document event with
`event.detail.root` identifying the inserted row. Media and slider handlers use
delegation; Select2, color, date, rich text and code editors initialize in that scope.

Integration checks on a WordPress installation:

```sh
wp --skip-plugins --skip-themes eval-file tests/repeater.php
npm run gulp
```

## Credits & Third-Party Libraries

Wapic Fields is built to stay lightweight, so it bundles as little third-party code as possible.

| Library | Version | License | Used for |
|---|---|---|---|
| [Select2](https://github.com/select2/select2) | 4.1.0 | [MIT](https://github.com/select2/select2/blob/master/LICENSE.md) | Powers the **Select2** field (searchable, multi-select dropdowns) |

All other scripts and styles (validation, conditional logic, tabs, sliders, image-select, etc.) are custom-written for this plugin — see `assets/source/`.

## Changelog
[Changelog](https://github.com/wapiclo/wapic-fields/wiki/Changelog)
