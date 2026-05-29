# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Security
- Added proper sanitization for `editor` field type using `wp_kses_post()` instead of defaulting to `sanitize_text_field()` which strips all HTML.
- Added proper sanitization for `color` field type using `sanitize_hex_color()`.
- Added proper sanitization for `file` field type using `esc_url_raw()`.
