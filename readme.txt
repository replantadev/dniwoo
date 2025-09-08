=== DNIWOO - DNI/NIF for WooCommerce ===
Contributors: replanta
Donate link: https://replanta.net/donate
Tags: woocommerce, dni, nif, nie, cif, spain, portugal, validation, checkout
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Professional DNI/NIF field for WooCommerce checkout with validation for Spain and Portugal.

== Description ==

DNIWOO adds a professional DNI/NIF validation field to your WooCommerce checkout, supporting Spanish and Portuguese documents with real-time validation.

= Features =

* **Spanish Documents**: DNI, NIE, CIF validation
* **Portuguese Documents**: NIF, NIPC validation  
* **Real-time validation** with AJAX
* **Client-side validation** for instant feedback
* **Seamless WooCommerce integration**
* **Theme compatibility** - works with all major themes
* **Responsive design** - mobile-friendly
* **Accessibility compliant** - WCAG standards
* **Auto-updates** via GitHub
* **WordPress standards compliant**

= Supported Documents =

**Spain:**
* DNI (Documento Nacional de Identidad) - 8 digits + letter
* NIE (Número de Identidad de Extranjero) - X/Y/Z + 7 digits + letter  
* CIF (Código de Identificación Fiscal) - Letter + 7 digits + control

**Portugal:**
* NIF (Número de Identificação Fiscal) - 9 digits
* NIPC (Número de Identificação de Pessoa Coletiva) - 9 digits

= Theme Compatibility =

DNIWOO works seamlessly with all WordPress themes including:
* Astra
* Storefront 
* GeneratePress
* OceanWP
* Divi
* Avada
* And many more

= Privacy & Security =

* No personal data stored by default
* GDPR compliant
* No external API calls for validation
* Local validation algorithms
* All inputs sanitized and validated

== Installation ==

= Automatic Installation =

1. Go to your WordPress admin dashboard
2. Navigate to Plugins > Add New
3. Search for "DNIWOO"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Go to Plugins > Add New > Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Activate the plugin

= Configuration =

1. Go to WooCommerce > Settings > DNIWOO
2. Enable the DNI field
3. Configure validation settings
4. Customize error messages
5. Choose field position

== Frequently Asked Questions ==

= Which documents are supported? =

Spain: DNI, NIE, CIF
Portugal: NIF, NIPC

= Is real-time validation available? =

Yes, the plugin includes both client-side and server-side validation with optional real-time AJAX validation.

= Does it work with my theme? =

DNIWOO is designed to work with all WordPress themes and includes responsive CSS that adapts to your theme's styling.

= Is it GDPR compliant? =

Yes, the plugin doesn't store personal data by default and uses local validation algorithms without external API calls.

= How do I customize the field position? =

Go to WooCommerce > Settings > DNIWOO and choose from available field positions in the checkout form.

= Can I customize error messages? =

Yes, all error messages can be customized in the plugin settings or using WordPress filters.

== Screenshots ==

1. DNI field in WooCommerce checkout
2. Real-time validation in action
3. Plugin settings panel
4. Field position options
5. Error message customization

== Changelog ==

= 1.0.0 =
* Initial release
* Spanish document validation (DNI, NIE, CIF)
* Portuguese document validation (NIF, NIPC)
* Real-time AJAX validation
* WooCommerce integration
* Theme compatibility
* Accessibility compliance
* Auto-update system

== Upgrade Notice ==

= 1.0.0 =
Initial release with complete Spanish and Portuguese document validation.

== Developer Information ==

= Hooks & Filters =

**Actions:**
* `dniwoo_before_validation` - Before document validation
* `dniwoo_after_validation` - After document validation
* `dniwoo_before_field` - Before field output
* `dniwoo_after_field` - After field output

**Filters:**
* `dniwoo_field_settings` - Modify field settings
* `dniwoo_validate_document` - Customize validation
* `dniwoo_error_message` - Custom error messages
* `dniwoo_field_position` - Field position

= REST API =

**Endpoint:** `POST /wp-json/dniwoo/v1/validate`
**Parameters:** document, country
**Response:** JSON with validation result

= JavaScript API =

```javascript
// Validate document
const isValid = DNIWOOCheckout.isValidDocument('12345678A');

// Country-specific validation
const isValidSpain = DNIWOOCheckout.validateSpain('12345678A');
const isValidPortugal = DNIWOOCheckout.validatePortugal('123456789');
```

== Support ==

* GitHub: https://github.com/replantadev/dniwoo
* Email: info@replanta.dev
* Website: https://replanta.net

== Credits ==

Developed by Replanta - Sustainable WordPress solutions.
