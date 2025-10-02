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

To load the examples in the WordPress admin, add the following code to your main theme or plugin file:

```add_filter('wapic_fields_load_examples', '__return_true');```

## Changelog
[Changelog](https://github.com/wapiclo/wapic-fields/wiki/Changelog)
