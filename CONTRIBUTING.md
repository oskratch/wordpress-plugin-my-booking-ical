# Contributing to My Booking iCal Form

Thank you for your interest in contributing! Here's how to get involved.

## Reporting bugs

Use the [Bug report](.github/ISSUE_TEMPLATE/bug_report.yml) issue template and include your WordPress and PHP versions, steps to reproduce, and any error logs.

For security vulnerabilities, please **do not open a public issue** — see [SECURITY.md](SECURITY.md) instead.

## Suggesting features

Open a [Feature request](.github/ISSUE_TEMPLATE/feature_request.yml) issue describing the problem it solves and the solution you have in mind.

## Submitting a pull request

1. **Fork** the repository and create a branch from `main`:
   ```
   git checkout -b fix/brief-description
   ```

2. **Make your changes.** A few guidelines:
   - Always use `$wpdb->prepare()` for database queries
   - Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`, `esc_js()`
   - Sanitize all input: `sanitize_text_field()`, `sanitize_email()`, `intval()`, etc.
   - Use `__()` / `_e()` for any user-visible strings (text domain: `my_booking_ical_form`)
   - Follow the existing code style (tabs for indentation in PHP)

3. **Commit** using [Conventional Commits](https://www.conventionalcommits.org/):
   ```
   fix: correct price calculation for multi-range stays
   feat: add support for custom currency symbols
   ```

4. **Push** and open a pull request against `main`. Fill in the PR template.

## Development setup

1. Have a local WordPress installation (e.g. LocalWP, DDEV, or Lando)
2. Copy the `my-booking-ical-form` folder into `/wp-content/plugins/`
3. Activate the plugin in the WordPress admin

There is no build step — PHP and JS are served as-is.

## Project structure

```
my-booking-ical-form/
├── my-booking-ical-form.php    # Plugin entry point, DB creation, activation hooks
├── functions.php               # Shortcode, admin menu, enqueue scripts, form submission
├── ical_proxy.php              # Proxy to fetch external iCal feeds (avoids CORS)
├── uninstall.php               # Cleanup on uninstall
├── includes/admin/             # Admin CRUD handlers (forms, requests, prices, settings)
├── views/admin/                # Admin page templates
├── views/public/               # Front-end form template
└── assets/
    ├── js/mbif.js              # Front-end JS (datepicker, iCal parsing, price calc)
    ├── css/styles.css          # Front-end styles
    ├── admin/js/mbif.js        # Admin JS
    └── admin/css/styles.css    # Admin styles
```

## License

By contributing you agree that your code will be released under the [GPL v2](LICENSE) license.
