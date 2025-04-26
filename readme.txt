=== My Booking iCal Form ===
Contributors: oscarperiche
Tags: booking, iCal, reservation, calendar, airbnb, booking.com
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT
Description: A custom WordPress plugin for property owners who manage apartments listed on external platforms like Booking.com and Airbnb, allowing users to request reservations directly through their website. Synchronizes availability calendars from Booking.com and Airbnb using iCal URLs and lets users submit reservation requests.
Requires PHP: 7.0

== Description ==

A custom WordPress plugin developed for property owners who manage apartments listed on external platforms like Booking.com and Airbnb, and want to allow users to request reservations directly through their website.

This plugin synchronizes availability calendars from Booking.com and Airbnb using iCal URLs, lets users submit reservation requests, and provides administrators with a simple way to review and manage those requests.

== Features ==

- Create booking forms for individual apartments.
- Connect to Booking.com and Airbnb calendars through iCal URLs.
- Display available dates based on synchronized calendars.
- Set minimum booking days, general pricing, maximum capacity, and parking options for each apartment.
- Collect reservation requests through a customizable form.
- View and manage all received booking requests per apartment from the WordPress dashboard.
- No direct online payment: Admins validate requests manually and manage booking status externally.

== Installation ==

1. Upload the plugin folder to your WordPress `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to the **My Booking iCal** panel in the WordPress back office to start adding your apartments and configure the booking forms.

== Screenshots ==

1. Screenshot of the booking form.
2. Screenshot of the WordPress back office with booking request management.

== Changelog ==

= 1.0.0 =
* Initial release.

== Frequently Asked Questions ==

= How does the iCal synchronization work? =

The plugin reads availability data from Booking.com and Airbnb calendars via iCal URLs, and displays available dates in the booking form. The synchronization is one-way; it does not update the external calendars.

= Is there any payment integration with this plugin? =

No, this plugin is designed for manual booking validation. You must handle payments and confirmations externally, for example by phone or email.

== License ==

This plugin is licensed under the **MIT License**.  
You are free to use, modify, and distribute it, provided that proper credit is given to the original author.

== Author ==

Developed by Oscar Periche.
