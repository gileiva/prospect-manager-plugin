# Prospect Manager Plugin

**Prospect Manager Plugin** is a custom WordPress plugin designed to help website administrators manage prospect information. With this plugin, users can capture leads through a frontend form, which is stored as a custom post type for tracking and managing potential clients. It includes integrated reCAPTCHA validation, security measures, and a customizable floating button for easy form access.

## Features

- Custom Post Type (CPT) for **Prospects** to store lead information.
- **Frontend Form** for lead generation, compatible with popular page builders like Gutenberg, Elementor, Divi, and BeBuilder.
- **Customizable Floating Button** to access the form.
- Built-in **reCAPTCHA v2** support to reduce spam.
- Backend settings for enabling reCAPTCHA and setting API keys.
- Email notifications to admins and the assigned sales team members.
- Secure AJAX form submission.
- Anti-spam measures including request rate limiting based on IP addresses.

## Installation

1. Download the plugin files and upload them to the `/wp-content/plugins/prospect-manager-plugin` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Settings > Prospect Manager** to configure reCAPTCHA keys and other settings.

## Configuration

### reCAPTCHA Configuration

To enable reCAPTCHA, navigate to **Settings > Prospect Manager > reCAPTCHA**. Enter your **Site Key** and **Secret Key** from Google’s reCAPTCHA console.

### Floating Button Configuration

To enable or disable the floating button:

1. Go to **Settings > Prospect Manager**.
2. Check **Enable Floating Button** to activate it.

### Git Repository

You can clone or fork the plugin from the [GitHub Repository](https://github.com/gileiva/prospect-manager-plugin).

## Usage

1. **Adding the Form:** Place the shortcode `[prospect_form]` on any page to display the lead capture form.
2. **Custom Post Type (CPT):** Prospects are stored as custom posts for easy tracking and management.
3. **Notifications:** Email notifications are sent to relevant parties upon new prospect submission.

## Security

The plugin includes several security features:

- **reCAPTCHA Verification:** Prevents bot submissions.
- **Nonce Verification:** Ensures secure AJAX requests.
- **Input Validation and Sanitization:** All form inputs are sanitized to prevent malicious data submission.
- **Rate Limiting with Transients:** Limits the number of submissions from a single IP address within a specified period.

## Code Structure

The plugin follows a modular structure with the following core files and classes:

- **`class-handler.php`** - Manages primary plugin functionality and hooks.
- **`class-formhandler.php`** - Manages form generation and AJAX submission handling.
- **`class-admin.php`** - Adds admin pages and submenus.
- **`assets/`** - Contains CSS and JS assets for frontend and backend.
  
## Classes

### `Admin`

Manages the admin menu and settings page. Admin settings include options for reCAPTCHA keys, floating button activation, and general plugin configurations.

### `FormHandler`

Handles frontend form display and form submissions via AJAX. Integrates with reCAPTCHA for spam prevention and performs validation, sanitization, and security checks.

### `GiHandler`

The main handler class that loads and manages the plugin’s components, initiates AJAX requests, and organizes backend operations.

## AJAX Endpoints

- **Form Submission:** Handles form submission via AJAX, validates nonce, reCAPTCHA response, and IP-based rate limiting.

## Development

To contribute, follow these steps:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/gileiva/prospect-manager-plugin.git

2. **Create a new branch for your feature or bug fix:**
     ```bash
     git checkout -b feature-name
3. **Push your changes:**
    ```bash
    git push origin feature-name


## Changelog

### Version 1.0
- Initial release with custom post type, reCAPTCHA integration, and secure form handling.

### Version 1.1
- Enhanced security and rate limiting.


### Contact
For support or inquiries, please reach out through the GitHub repository issues or contact the plugin developer.