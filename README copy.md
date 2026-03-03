# TPAIS Accommodation System - PHP Version

A lightweight, production-ready PHP booking system for cPanel hosting with MySQL database support.

## Features

- ✓ Guest booking form with hotel selection
- ✓ Admin dashboard with booking management
- ✓ Hotel management (CRUD operations)
- ✓ Email notifications for bookings
- ✓ CSV and TXT export functionality
- ✓ Admin authentication (email/password)
- ✓ Responsive design with TPAIS blue color scheme
- ✓ No external dependencies - pure PHP & MySQL

## Quick Start

### 1. Upload Files to cPanel

1. Download/compress the entire `accommodation_php` directory
2. Login to cPanel
3. Go to File Manager
4. Navigate to your domain's public folder (e.g., `/home3/tpaisde3/accommodation.tpais-events.com/`)
5. Upload and extract the files

### 2. Create Database

1. In cPanel, go to **MySQL Databases**
2. Create a new database (e.g., `tpaisde3_accommodation`)
3. Create a new MySQL user (e.g., `tpaisde3_accommodation_user`)
4. Assign the user to the database with all privileges
5. Note the database name, username, and password

### 3. Import Database Tables

1. In cPanel, go to **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Upload `DATABASE_SETUP.sql`
5. Click **Go**

### 4. Configure Database Connection

1. Edit `index.php`
2. Update these lines with your database credentials:
   ```php
   $db_host = 'localhost';
   $db_user = 'YOUR_DB_USER';
   $db_pass = 'YOUR_DB_PASSWORD';
   $db_name = 'YOUR_DB_NAME';
   ```

### 5. Set File Permissions

Via SSH Terminal:
```bash
chmod -R 755 /path/to/accommodation_php
```

### 6. Access the Application

- **Guest Booking**: `https://accommodation.tpais-events.com/`
- **Admin Login**: `https://accommodation.tpais-events.com/?page=login`

## Default Admin Credentials

- **Email**: contactus@tpais-events.com
- **Password**: Sunk@nmi84!@

**Important**: Change the password after first login!

## File Structure

```
accommodation_php/
├── index.php                 # Main entry point
├── .htaccess                 # URL rewriting
├── DATABASE_SETUP.sql        # Database schema
├── includes/
│   ├── header.php           # Header & styling
│   ├── footer.php           # Footer
│   └── functions.php        # Helper functions
├── pages/
│   ├── home.php             # Home page
│   ├── booking.php          # Guest booking form
│   ├── confirmation.php     # Booking confirmation
│   ├── login.php            # Admin login
│   └── admin/
│       ├── dashboard.php    # Admin dashboard
│       ├── bookings.php     # Manage bookings
│       └── hotels.php       # Manage hotels
└── README.md                # This file
```

## Features Overview

### Guest Booking
- Select hotel from available options
- Enter guest details
- Automatic price calculation
- Booking confirmation email

### Admin Dashboard
- View booking statistics
- Manage bookings (create, read, update, delete)
- Manage hotels (create, read, update, delete)
- Export bookings to CSV or TXT

### Email Notifications
- Booking confirmation emails sent automatically
- Customizable email templates

### Export Functionality
- **CSV Export**: Download bookings as CSV file
- **TXT Export**: Download bookings as formatted text file

## Database Schema

### Users Table
- id, name, email, password, role, created_at

### Hotels Table
- id, name, location, price_per_night, distance_to_venue, rating, description, created_at

### Bookings Table
- id, hotel_id, guest_name, guest_email, guest_phone, nights, total_price, booking_date, status, payment_status, stripe_payment_id, paystack_reference, created_at

## Troubleshooting

### Database Connection Error
1. Verify database credentials in `index.php`
2. Check that database and user exist in MySQL
3. Ensure user has all privileges on the database

### Page Not Found (404)
1. Verify `.htaccess` file exists in the root directory
2. Check that `mod_rewrite` is enabled on your server
3. Try accessing `index.php?page=booking` directly

### Email Not Sending
1. Verify SMTP settings in your cPanel
2. Check that PHP mail() function is enabled
3. Review server error logs for mail-related errors

### Permission Denied Errors
```bash
chmod -R 755 /path/to/accommodation_php
```

## Security Recommendations

1. **Change Admin Password**: After first login, change the default password
2. **Use HTTPS**: Ensure your domain has an SSL certificate
3. **Regular Backups**: Set up automatic backups in cPanel
4. **Update Passwords**: Periodically update database credentials
5. **Monitor Logs**: Check server logs for suspicious activity

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review server error logs
3. Contact your hosting provider for server-level issues

## License

All rights reserved © TPAIS 2024
