### 2.3.0 - 2026-07-21
- **Updated Select2 Library**: Bundled Select2 library updated from 4.0.13 to 4.1.0.

### 2.2.0 - 2026-05-14
- **Added Slider Number Field**: Interactive numeric input control using a slider.
- **Added Image Select Field**: Allows users to select options using visual images or icons.
- **Added Code Editor Field**: Professional code editor with syntax highlighting using WordPress core libraries (CodeMirror).
- **Added Tab Layout Options**: Supports navigation tab placement at the top (`top`) or on the side (`left`).
- **Added FOUC Fix**: Resolved "Flash of Unstyled Content" for toggles and other UI elements by moving CSS enqueuing to the header.

### 2.1.0
- **Multi-Conditional Logic**: Added support for complex conditional logic using `AND` / `OR` relations (similar to Carbon Fields).
- **Heading Field Type**: New field type for section titles with border-underneath styling.
- **Separator Field Type**: New field type for horizontal line dividers (replaces `divider`).
- **Required Hidden Handling**: Improved JavaScript validation to automatically disable hidden conditional fields, preventing them from blocking form submission.

### 2.0.4
- **Fix:** improve price comparison validation to only run when sale price is filled.
### 2.0.3
- **Fix:** Backward Incompatible for PHP 7.4 

### 2.0.2
- **Improvement:** Allow HTML in descriptions.

### 2.0.1
- **Fix:** Error load composer vendor.

### 2.0.0
- **New:** Refactored field structures by separating each field into its own file for better modularity.

- **New:** Implemented static class usage to simplify field initialization and access.

- **New:** Added an example demonstrating how to store multiple options inside a single array-based option.

### 1.2.2
- **Fix:** Corrected default value handling when the option is not set in the database.

### 1.2.0
- **Fix:** Resolved incorrect default values for toggle, editor, select, select2, radio, and checkbox fields.

### 1.1.0
- **New:** Migrated to **PSR-4** autoloading standard for improved structure and compatibility.

### 1.0.0
- **New:** Initial release.
