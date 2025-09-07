# DNIWOO Installation Guide

## Quick Installation

### Method 1: Download from GitHub

1. Go to https://github.com/replantadev/dniwoo
2. Click **Code** → **Download ZIP**
3. Extract the ZIP file
4. Upload the `dniwoo` folder to `/wp-content/plugins/`
5. Activate the plugin in WordPress admin

### Method 2: Git Clone

```bash
cd wp-content/plugins
git clone https://github.com/replantadev/dniwoo.git dniwoo
```

### Method 3: Manual Upload

1. Download the latest release from [GitHub Releases](https://github.com/replantadev/dniwoo/releases)
2. Upload via WordPress admin: **Plugins** → **Add New** → **Upload Plugin**

## Post-Installation Setup

### 1. Configure Settings

Navigate to **WooCommerce** → **Settings** → **DNIWOO**

**Basic Settings:**
- ✅ Enable DNI Field
- ✅ Make field required
- Choose validation mode (real-time or on submit)
- Set field position in checkout

### 2. Test the Integration

1. Go to your WooCommerce checkout page
2. Verify the DNI/NIF field appears
3. Test with sample documents:
   - Spain: `12345678Z` (valid DNI)
   - Portugal: `123456789` (valid NIF)

### 3. Customize (Optional)

**Theme Integration:**
The plugin automatically adapts to your theme. For custom styling:

```css
/* Custom DNI field styling */
#billing_dni {
    border: 2px solid #your-color;
    border-radius: 8px;
}

.dniwoo-feedback.dniwoo-valid {
    color: #your-success-color;
}
```

**Hooks for Developers:**
```php
// Customize field settings
add_filter('dniwoo_field_settings', function($settings) {
    $settings['placeholder'] = 'Enter your ID number';
    return $settings;
});

// Add custom validation
add_filter('dniwoo_validate_document', function($is_valid, $document, $country) {
    // Your custom validation logic
    return $is_valid;
}, 10, 3);
```

## Verification Checklist

After installation, verify:

- [ ] DNI/NIF field appears on checkout
- [ ] Field adapts based on billing country (Spain/Portugal)
- [ ] Real-time validation works (if enabled)
- [ ] Form submission validates documents
- [ ] Error messages appear correctly
- [ ] Field is responsive on mobile devices

## Troubleshooting

### Field Not Appearing?

1. **Check WooCommerce:** Ensure WooCommerce is active and updated
2. **Plugin Settings:** Verify the field is enabled in settings
3. **Theme Compatibility:** Test with a default theme (Twenty Twenty-Four)
4. **Page Builder:** Some page builders may require special configuration

### Validation Not Working?

1. **JavaScript Errors:** Check browser console for errors
2. **AJAX Issues:** Verify WordPress AJAX is working
3. **Cache:** Clear any caching plugins
4. **Conflicts:** Temporarily deactivate other plugins

### Need Help?

- **Documentation:** https://github.com/replantadev/dniwoo/wiki
- **Issues:** https://github.com/replantadev/dniwoo/issues
- **Support:** info@replanta.dev
- **Website:** https://replanta.net

## Advanced Configuration

### Developer Mode

For development and testing, add to `wp-config.php`:

```php
define('DNIWOO_DEBUG', true);
```

This enables additional logging and debug information.

### Custom Document Types

To add custom document validation:

```php
add_filter('dniwoo_validate_document', function($is_valid, $document, $country) {
    if ($country === 'FR') {
        // Add French validation logic
        return your_french_validation($document);
    }
    return $is_valid;
}, 10, 3);
```

### API Integration

Use the REST API endpoint for external validation:

```javascript
fetch('/wp-json/dniwoo/v1/validate', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        document: '12345678Z',
        country: 'ES'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

## Updates

The plugin automatically checks for updates from GitHub. You'll see update notifications in your WordPress admin when new versions are available.

**Manual Update Check:**
Go to **Plugins** page and click **Check for updates** or wait for automatic daily checks.

---

**Need assistance?** Contact us at info@replanta.dev
