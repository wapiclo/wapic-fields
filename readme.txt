=== Wapic Fields ===
Contributors: wapiclo
Tags: custom-fields, options, post-meta, taxonomy, repeater
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add reusable custom fields to WordPress options pages, post meta, and taxonomy term meta.

== Description ==

Wapic Fields is a developer library for registering and rendering custom fields in WordPress administration screens. It supports options pages, post meta boxes, and taxonomy term meta.

Field types include text, textarea, URL, email, number, phone, checkbox, radio, toggle, select, Select2, image, gallery, file, color, date, editor, code editor, slider, image select, and repeater.

Conditional logic can show or hide fields based on other field values. Repeater fields render standard Wapic Fields controls, support sortable and collapsible rows, and validate nested required fields on the server.

== Installation ==

1. Upload the `wapic-fields` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen.
2. Activate Wapic Fields through the **Plugins** screen.
3. Include the library in your plugin or theme and register field definitions using the Wapic Fields API.

This library is intended for developers. See the project documentation for usage examples and API details.

== Frequently Asked Questions ==

= Is this a fields builder interface for site owners? =

No. Wapic Fields is a developer library. Developers register field definitions in PHP code.

= Does it support repeaters? =

Yes. Repeater fields support nested field definitions, sortable rows, minimum and maximum row limits, and server-side validation.

== Changelog ==

= 2.4.1 =
* Fix: Excluded examples, package metadata, license file, and source maps from Git release archives used for Composer distribution.

= 2.4.0 =
* New: Repeater fields with nested controls, sortable rows, collapsing, row limits, and delete confirmation.
* New: Row-scoped conditional logic and server-side required and repeater limit validation.
* Improvement: Validation links, tab and repeater error indicators, and tab state restoration.
* Fix: Select2 choice spacing and translation loading path; added translator context for placeholder strings.

= 2.3.0 =
* Improvement: Updated the bundled Select2 library from 4.0.13 to 4.1.0.

= 2.2.0 =
* New: Interactive slider number field.
* New: Image select field for visual choices.
* New: Code editor field using the WordPress bundled CodeMirror library.
* New: Top and side tab layout options.
* Fix: Reduced flashes of unstyled fields by moving CSS enqueuing to the header.

= 2.1.0 =
* New: Conditional logic with AND and OR relations.
* New: Heading and separator field types.
* Fix: Hidden conditional fields no longer block form submission through required validation.

= 2.0.4 =
* Fix: Price comparison validation runs only when the sale price is filled.

= 2.0.3 =
* Fix: PHP 7.4 compatibility.

= 2.0.2 =
* Improvement: Allow HTML in field descriptions.

= 2.0.1 =
* Fix: Composer autoloading.

= 2.0.0 =
* New: Static methods for creating fields.
* New: Example for storing multiple settings in one array option.
* Improvement: Refactored field classes into separate files.

= 1.2.2 =
* Fix: Default values when an option has not been saved.

= 1.2.0 =
* Fix: Default values for toggle, editor, select, Select2, radio, and checkbox fields.

= 1.1.0 =
* Improvement: Migrated to PSR-4 autoloading.

= 1.0.0 =
* New: Initial release.
