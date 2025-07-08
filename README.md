# Custom Text Translation Manager

A WordPress plugin that enables translation of hardcoded text using WPML by adding custom strings to WPML's string translation system.

## Description

The Custom Text Translation Manager plugin solves a common problem in WordPress multilingual sites: translating hardcoded text that doesn't go through WordPress's standard translation functions. This plugin allows you to register any hardcoded text with WPML and automatically replace it with translated versions on the frontend.

## Features

- **Easy Text Registration**: Add hardcoded texts through a simple admin interface
- **WPML Integration**: Seamlessly integrates with WPML's string translation system
- **Automatic Frontend Replacement**: Dynamically replaces hardcoded text with translations on the frontend
- **Context Management**: Organize texts with custom contexts for better management
- **Real-time Translation**: Supports dynamic content changes with DOM observation
- **Attribute Translation**: Translates text in HTML attributes (title, alt, placeholder, etc.)
- **Clean Admin Interface**: User-friendly WordPress admin interface

## Requirements

- WordPress 4.0 or higher
- WPML (WordPress Multilingual Plugin)
- PHP 5.6 or higher

## Installation

1. Download the plugin files
2. Upload the plugin folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Make sure WPML is installed and activated
5. Navigate to 'Hardcoded Texts' in your WordPress admin menu

## Usage

### Adding Hardcoded Text for Translation

1. Go to **Hardcoded Texts** in your WordPress admin menu
2. Fill in the form with:
   - **Original Text**: The hardcoded text you want to translate
   - **Context**: A descriptive context (e.g., "header", "footer", "contact-form")
   - **Domain**: Translation domain (default: "wpml-hardcoded-translator")
3. Click **Add Text**

### Translating the Text

1. After adding text, click the **Translate** button next to any entry
2. This will take you to WPML's String Translation page
3. Add translations for your target languages
4. The translations will automatically appear on the frontend

### Managing Existing Texts

- View all registered texts in the admin table
- Delete texts that are no longer needed
- Click **Translate** to modify existing translations

## How It Works

1. **Registration**: The plugin stores hardcoded texts in a custom database table
2. **WPML Integration**: Texts are registered with WPML's string translation system
3. **Frontend Processing**: JavaScript automatically scans and replaces hardcoded text with translations
4. **Dynamic Content**: Uses MutationObserver to handle dynamically loaded content

## File Structure

```
custom-text-translation-manager/
├── custom-text-translation-manager.php  # Main plugin file
├── admin/
│   ├── admin-page.php                   # Admin interface
│   ├── css/
│   │   └── admin.css                    # Admin styles
│   └── js/
│       └── admin.js                     # Admin JavaScript
└── public/
    └── js/
        └── frontend.js                  # Frontend text replacement
```

## Database Schema

The plugin creates a table `wp_wpml_hardcoded_texts` with the following structure:

- `id`: Auto-increment primary key
- `original_text`: The original hardcoded text
- `context`: Context identifier
- `domain`: Translation domain
- `status`: Text status (active/inactive)
- `created_at`: Creation timestamp

## JavaScript API

The frontend script automatically handles text replacement, but you can also manually trigger it:

```javascript
// The translations are available in the global object
wpmlHardcodedTranslations

// Text replacement happens automatically on DOM ready and content changes
```

## Hooks and Filters

The plugin uses standard WPML hooks:

- `wpml_register_single_string`: Registers strings with WPML
- `wpml_translate_single_string`: Retrieves translated strings

## Security Features

- Nonce verification for all admin actions
- Capability checks (`manage_options`)
- Input sanitization and validation
- SQL injection prevention

## Troubleshooting

### WPML Not Found
If you see "WPML Hardcoded Text Translator requires WPML to be installed and activated", make sure WPML is properly installed and activated.

### Text Not Translating
1. Check that the text is registered in the admin panel
2. Verify translations exist in WPML String Translation
3. Clear any caching plugins
4. Check browser console for JavaScript errors

### Performance Considerations
- The plugin uses efficient DOM scanning techniques
- Text replacement only occurs when translations differ from originals
- MutationObserver is optimized for performance

## Contributing

Contributions are welcome! Please feel free to submit issues and pull requests.

## Changelog

### Version 1.1.0
- Initial release
- WPML integration
- Frontend text replacement
- Admin interface for text management
- Support for HTML attributes translation
- Dynamic content observation

## License

This plugin is licensed under the GPL v2 or later.

## Author

Omar Helal

## Support

For support and questions, please create an issue in the GitHub repository.
