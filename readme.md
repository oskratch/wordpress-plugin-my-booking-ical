# My Booking iCal Form

A custom WordPress plugin developed for property owners who manage apartments listed on external platforms like Booking.com and Airbnb, and want to allow users to **request reservations directly through their website**.

This plugin synchronizes availability calendars from Booking.com and Airbnb using **iCal URLs**, lets users submit reservation requests, and provides administrators with a simple way to review and manage those requests.

---

## Features

- **Create Booking Forms** for individual apartments.
- **Connect to Booking.com and Airbnb calendars** through iCal URLs.
- **Display available dates** based on synchronized calendars.
- **Set minimum booking days**, **general pricing**, **maximum capacity**, and **parking options** for each apartment.
- **Collect reservation requests** through a customizable form.
- **View and manage all received booking requests** per apartment from the WordPress dashboard.
- **No direct online payment**: Admins validate requests manually (e.g., by phone) and manage booking status externally.

---

## How it Works

1. **Add Apartments**:  
   Create a booking form for each apartment by providing:
   - Apartment name
   - Reference
   - Booking.com iCal URL (optional)
   - Airbnb iCal URL (optional)
   - Minimum days of stay
   - General price (€)
   - Maximum capacity (number of people)
   - Parking availability (yes/no)

2. **Display Availability**:  
   The form will automatically check the iCal feeds to display available dates.

3. **Users Submit Requests**:  
   Site visitors select their check-in and check-out dates and fill out a simple form with their contact information.

4. **Administrators Receive and Manage Requests**:  
   - Admins view received booking requests per apartment.
   - Each request shows key information: reservation dates, client details, number of people, parking needs, and comments.
   - Admins validate bookings manually (e.g., by phone) and then update external platforms like Booking.com or Airbnb accordingly.

---

## Example Request Details (Admin View)

- **Reference**: apt01-20250420-00001  
- **Client Name**: John Doe  
- **Check-in**: 20-04-2025  
- **Check-out**: 26-04-2025  
- **Email**: john.doe@example.com  
- **Phone**: +123456789  
- **Parking**: Yes  
- **Guests**: 4  
- **Total Nights**: 6  
- **Total Price**: 1920 €

> *Note: Each day is priced based on the general price defined for the apartment.*

---

## Installation

1. Upload the plugin folder to your WordPress `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to the **My Booking iCal** panel in the WordPress back office to start adding your apartments and configure the booking forms.

## Notes

- This plugin is designed for manual booking validation. No payment gateway or automated booking confirmation is included.
- iCal synchronization is one-way: the plugin only **reads** availability from the external iCal feeds.

---

## License

This plugin is licensed under the **MIT License**.  
You are free to use, modify, and distribute it, provided that proper credit is given to the original author.  

> *Note: This plugin uses third-party libraries, such as Datepicker, which may have their own licensing terms. Please review and comply with their respective licenses.*

---

## Author

Developed by Oscar Periche.