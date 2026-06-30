# My Booking iCal Form

![WordPress](https://img.shields.io/badge/WordPress-6.3%2B-blue.svg) ![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg) ![MySQL](https://img.shields.io/badge/MySQL-5.6%2B-orange.svg) ![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)

A WordPress plugin for property owners who manage apartments on external platforms like Booking.com and Airbnb. It lets visitors submit reservation requests directly on your website, while keeping availability calendars synchronized via iCal URLs.

## Overview

This plugin bridges the gap between external booking platforms and your WordPress website by:

- Synchronizing availability from Booking.com and Airbnb iCal feeds
- Allowing visitors to submit reservation requests directly from your site
- Providing administrators with a dashboard to review and manage incoming requests
- Supporting flexible pricing strategies with date-range overrides

**Note:** This plugin handles requests only — no automatic bookings or payments are processed. All reservations must be confirmed manually by the administrator.

## Key Features

### Apartment Management
- Create individual booking forms per apartment
- Connect to up to two iCal feeds simultaneously (Booking.com + Airbnb)
- Configure per-apartment settings:
  - Minimum stay (in days)
  - Maximum guest capacity
  - Parking availability
  - Base nightly price

### Dynamic Pricing
- Set a base price per apartment
- Add unlimited special pricing ranges (seasonal rates, holidays, etc.)
- Prices shown per day directly in the calendar picker
- Automatic total calculation based on selected dates

### Availability Synchronization
- Real-time calendar sync from Booking.com and Airbnb iCal feeds
- Booked dates are automatically disabled in the calendar
- Read-only synchronization — no data is written back to external platforms

### Request Management
- Collects guest details: name, email, phone, dates, guests, parking, comments
- Generates unique reference numbers per request (`apt01-20250420-00001`)
- Admin dashboard to list, view, and update request status
- Three statuses: **Pending review**, **Validated**, **Denied**
- Optional email notification on new request submission

## How It Works

### 1. Setup
Configure apartments in the WordPress admin panel under **My Booking iCal**:

- **Basic info**: Apartment name and reference code
- **Calendar integration**: Paste iCal URLs from Booking.com and/or Airbnb
- **Settings**: Minimum days, guest capacity, parking option, base price
- **Special pricing**: Add date ranges with custom prices for peak seasons

### 2. Front-end
Embed a booking form on any page or post using the shortcode:

```
[booking_ical_form form_id="1"]
```

Visitors can:
- Browse an inline calendar with disabled booked dates and daily prices shown
- Select check-in and check-out dates
- See a real-time price breakdown
- Submit a request with their contact details

### 3. Admin Management
After submission, the administrator:

- Receives an email notification (if enabled in Settings)
- Views all requests per apartment in the admin dashboard
- Opens individual requests to review guest details and the price summary
- Updates request status to Validated or Denied
- Manually updates external platform calendars for confirmed bookings

## Example Booking Request

```
Reference:      apt01-20250420-00001
Client:         John Doe
Email:          john.doe@example.com
Phone:          +123456789
Check-in:       April 20, 2025
Check-out:      April 26, 2025
Guests:         4 people
Parking:        Required
Total Nights:   6
Total Price:    €1,920
```

## Installation

1. **Upload**: Copy the `my-booking-ical-form` folder to `/wp-content/plugins/`
2. **Activate**: Enable the plugin under **Plugins** in the WordPress admin
3. **Configure**: Go to **My Booking iCal → Settings** to configure email notifications and global defaults
4. **Add forms**: Go to **My Booking iCal → Forms** and click **Add New** to set up each apartment

## Getting iCal URLs

**Booking.com**: Property dashboard → Calendar → Export Calendar → Copy the iCal link

**Airbnb**: Manage listings → Availability → Export calendar → Copy the `.ics` URL

## Requirements

- WordPress 6.3 or higher
- PHP 8.0 or higher
- MySQL 5.6 or higher
- Active internet connection for iCal synchronization

## Database

The plugin creates three tables on activation and removes them on uninstall:

| Table | Description |
|-------|-------------|
| `{prefix}my_booking_ical_forms` | Apartment configurations (iCal URLs, pricing, capacity) |
| `{prefix}my_booking_ical_requests` | Submitted booking requests |
| `{prefix}my_booking_ical_prices` | Special price ranges per apartment |

## Shortcode Reference

```
[booking_ical_form form_id="X"]
```

Replace `X` with the numeric ID shown in the shortcode column of the Forms list.

## Settings

Found under **My Booking iCal → Settings**:

| Option | Description |
|--------|-------------|
| Email notifications | Enable/disable email on new request |
| Notification email | Primary email address for notifications |
| Secondary email | Optional additional recipient |
| Show form labels | Display field labels (vs. placeholders only) |
| Default minimum days | Global default for minimum stay |
| Currency | € Euro or $ Dollar |

## Important Notes

- **Manual processing**: No bookings are confirmed automatically — each request requires manual review
- **Read-only sync**: Calendar sync is one-way from external platforms; confirmed bookings must be manually added to Booking.com/Airbnb
- **Privacy policy**: The form includes a required acceptance of your WordPress Privacy Policy page

## License

Licensed under GPL v2 or later. See [LICENSE](LICENSE) for full details.

---

**Developed by Oscar Periche — [Metalinked](https://metalinked.net/)**  
Contributions and feedback welcome via [GitHub Issues](https://github.com/oskratch/wordpress-my-booking-ical/issues).
