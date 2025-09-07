# DNIWOO - DNI/NIF for WooCommerce

Professional DNI/NIF field for WooCommerce checkout with validation for Spain and Portugal.

## Features

- Professional validation for Spanish documents (DNI, NIE, CIF)
- Portuguese document support (NIF, NIPC)
- Real-time validation with AJAX
- Client-side validation for instant feedback
- WooCommerce integration - seamless checkout experience
- Theme compatibility - works with all major themes
- Responsive design - mobile-friendly
- Accessibility - WCAG compliant
- Auto-updates via GitHub
- WordPress standards compliant

## Installation

### Manual Installation

1. Download the plugin from [GitHub releases](https://github.com/replantadev/dniwoo/releases)
2. Upload the `dniwoo` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure settings in WooCommerce > Settings > DNIWOO

### Via GitHub

```bash
cd wp-content/plugins
git clone https://github.com/replantadev/dniwoo.git
```

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher

## Configuration

Navigate to **WooCommerce > Settings > DNIWOO** to configure:

- **Enable/Disable** the DNI field
- **Required field** - make DNI mandatory
- **Validation mode** - real-time or on submit
- **Error messages** - customize validation messages
- **Field position** - choose where to display the field
- **Country detection** - automatic field type switching

## Supported Documents

### Spain
- **DNI** (Documento Nacional de Identidad) - 8 digits + letter
- **NIE** (Número de Identidad de Extranjero) - X/Y/Z + 7 digits + letter  
- **CIF** (Código de Identificación Fiscal) - Letter + 7 digits + control

### Portugal
- **NIF** (Número de Identificação Fiscal) - 9 digits
- **NIPC** (Número de Identificação de Pessoa Coletiva) - 9 digits

## Field Validation

### Client-side (JavaScript)
- Format validation while typing
- Real-time AJAX validation (optional)
- Immediate user feedback
- Prevents form submission with invalid data

### Server-side (PHP)
- Complete validation algorithms
- Double-check on form submission
- Security against tampering

## Theme Compatibility

DNIWOO is designed to work with all WordPress themes:

- **Astra**
- **Storefront** 
- **GeneratePress**
- **OceanWP**
- **Divi**
- **Avada**
- **And many more**

## Styling

The plugin includes comprehensive CSS that adapts to your theme:

- Responsive design for all devices
- Dark mode support
- High contrast mode support
- RTL (right-to-left) language support
- Print-friendly styles

## Hooks & Filters

### Actions

```php
// Before validation
do_action('dniwoo_before_validation', $document, $country);

// After validation
do_action('dniwoo_after_validation', $document, $country, $is_valid);

// Field output
do_action('dniwoo_before_field');
do_action('dniwoo_after_field');
```

### Filters

```php
// Modify field settings
$settings = apply_filters('dniwoo_field_settings', $settings);

// Customize validation
$is_valid = apply_filters('dniwoo_validate_document', $is_valid, $document, $country);

// Custom error messages
$message = apply_filters('dniwoo_error_message', $message, $document, $country);

// Field position
$position = apply_filters('dniwoo_field_position', $position);
```

## JavaScript API

```javascript
// Get validation status
const isValid = DNIWOOCheckout.isValidDocument('12345678A');

// Validate specific country
const isValidSpain = DNIWOOCheckout.validateSpain('12345678A');
const isValidPortugal = DNIWOOCheckout.validatePortugal('123456789');

// Manual validation trigger
DNIWOOCheckout.validateField($('#billing_dni'));
```

## REST API

### Validate Document

**Endpoint:** `POST /wp-json/dniwoo/v1/validate`

**Parameters:**
- `document` (string) - Document to validate
- `country` (string) - Country code (ES/PT)

**Response:**
```json
{
  "valid": true,
  "message": "Valid document",
  "document_type": "DNI",
  "formatted": "12345678-A"
}
```

## Troubleshooting

### Plugin not working?

1. **Check WooCommerce** - Ensure WooCommerce is active and updated
2. **Clear cache** - Clear any caching plugins
3. **Theme conflicts** - Test with a default theme
4. **Plugin conflicts** - Deactivate other plugins temporarily
5. **PHP version** - Ensure PHP 7.4 or higher

### Field not showing?

1. Check if the field is enabled in settings
2. Verify the field position setting
3. Ensure the checkout page is using WooCommerce shortcodes
4. Check for theme customizations that might hide fields

### Validation not working?

1. Test with known valid documents:
   - Spain: `12345678Z`
   - Portugal: `123456789`
2. Check browser console for JavaScript errors
3. Verify AJAX URL is working
4. Test server-side validation independently

## Development

### Local Development

```bash
# Clone repository
git clone https://github.com/replantadev/dniwoo.git

# Install dependencies (if any)
cd dniwoo
composer install --dev

# Run tests
composer test
```

### Coding Standards

This plugin follows WordPress coding standards:

```bash
# Check coding standards
composer phpcs

# Fix coding standards
composer phpcbf
```

## Security

### Validation Security
- All inputs are sanitized
- Server-side validation as final check
- CSRF protection with nonces
- XSS prevention with proper escaping

### Data Privacy
- No personal data stored by default
- GDPR compliant
- No external API calls for validation
- Local validation algorithms

## Performance

- **Lightweight** - Minimal footprint
- **Conditional loading** - Assets only on checkout
- **Optimized CSS** - Minimal styles, theme-adaptive
- **Efficient JS** - ES5 compatible, no dependencies

## Changelog

### 1.0.0 (2024-01-XX)
- Initial release
- Spain and Portugal support
- Real-time validation
- Professional WordPress standards
- Auto-update system
- Complete documentation

## Support

- **GitHub Issues**: [Report bugs](https://github.com/replantadev/dniwoo/issues)
- **Documentation**: [Wiki](https://github.com/replantadev/dniwoo/wiki)
- **Email**: info@replanta.dev
- **Website**: [https://replanta.net](https://replanta.net)

## License

GPL v3 or later - [License](https://www.gnu.org/licenses/gpl-3.0.html)

## Credits

Developed by [Replanta](https://replanta.net) - Sustainable WordPress solutions.
