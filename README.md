# My Booking iCal Form

![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg) ![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg) ![MySQL](https://img.shields.io/badge/MySQL-5.6%2B-orange.svg) ![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)

A WordPress plugin designed for property owners who manage apartments on external platforms like Booking.com and Airbnb, enabling direct reservation requests through their website while synchronizing availability calendars via iCal URLs.

## Overview

This plugin bridges the gap between external booking platforms and your WordPress website by:
- Synchronizing availability from Booking.com and Airbnb calendars
- Allowing visitors to submit reservation requests directly on your site
- Providing administrators with tools to manage and review requests
- Supporting flexible pricing strategies with special date ranges

## Key Features

### Apartment Management
- Create individual booking forms for each apartment
- Connect to multiple iCal feeds (Booking.com, Airbnb)
- Set apartment-specific configurations:
  - Minimum stay requirements
  - Maximum guest capacity
  - Parking availability
  - Base pricing

### Dynamic Pricing
- Define general pricing for each apartment
- Add unlimited special price ranges (seasonal rates, holidays, etc.)
- Automatic price calculation based on date selection

### Availability Synchronization
- Real-time calendar sync from external platforms
- Display only available dates to users
- One-way synchronization (read-only from external sources)

### Request Management
- Collect detailed booking requests with guest information
- Generate unique reference numbers for each request
- Admin dashboard to view and manage all requests
- Manual validation workflow (no automated bookings)

## How It Works

### 1. Setup Phase
Configure apartments in the WordPress admin panel:
- **Basic Info**: Name, reference code
- **Calendar Integration**: Add iCal URLs from Booking.com/Airbnb
- **Settings**: Minimum days, capacity, parking, base price
- **Special Pricing**: Define date ranges with custom rates

### 2. User Experience
Visitors interact with your booking forms:
- Select check-in and check-out dates from available calendar
- View calculated pricing based on selected dates
- Submit request with contact details and preferences
- Receive confirmation with unique reference number

### 3. Admin Management
Property owners review and process requests:
- View all requests organized by apartment
- Access complete booking details and guest information
- Validate requests manually (phone call, email verification)
- Update external platform calendars as needed

## Example Booking Request

```
Reference: apt01-20250420-00001
Client: John Doe
Email: john.doe@example.com
Phone: +123456789
Check-in: April 20, 2025
Check-out: April 26, 2025
Guests: 4 people
Parking: Required
Total Nights: 6
Total Price: €1,920
```

## Installation

1. **Upload**: Copy plugin files to `/wp-content/plugins/my-booking-ical-form/`
2. **Activate**: Enable the plugin in WordPress admin under Plugins
3. **Configure**: Access "My Booking iCal" in admin menu to set up apartments

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Active internet connection for iCal synchronization

## Important Notes

- **Manual Processing**: This plugin handles requests only - no automatic bookings or payments
- **Read-Only Sync**: Calendar synchronization is one-way from external platforms
- **Validation Required**: All bookings must be manually confirmed by administrators
- **External Updates**: Confirmed bookings must be manually added to external platform calendars

## License

This plugin is licensed under GPL v2 or later. See [LICENSE](LICENSE) for full details.

---

**Developed by Oscar Periche**  
Contributions and feedback welcome!