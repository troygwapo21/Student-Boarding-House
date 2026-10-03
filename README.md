# Student Boarding House Management System

A complete, production-ready web-based boarding house management platform built with PHP 8.3, MySQL 8, and Bootstrap 5.3.

## Features

### Public Website
- Modern responsive landing page
- Room browsing with search and filters
- Room details with image gallery
- Gallery, Amenities, Testimonials, FAQs
- Contact form with validation

### Student Panel
- Dashboard with statistics
- Profile management
- Online room reservation
- Payment tracking and receipts
- Maintenance request submission
- Complaint filing
- Announcements viewer
- Notification center
- Feedback submission

### Manager Panel
- Dashboard with analytics
- Room management (CRUD)
- Reservation management (approve/reject)
- Student management
- Payment verification
- Announcement management
- Gallery management
- Maintenance and complaint handling
- Feedback management
- Reports with charts

### Super Admin Panel
- All manager features
- Manager account management
- System settings configuration
- Database backup and restore
- Activity and audit logs
- Full system oversight

### Security
- PDO Prepared Statements
- password_hash() / password_verify()
- CSRF Protection
- Session Regeneration
- Login Attempt Limiter
- Account Lockout
- XSS Protection
- Role-Based Access Control (RBAC)

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or MySQL 8
- XAMPP (Apache + MySQL)
- Modern web browser

## Installation

### 1. Copy Project Files

Copy the `student_boarding_house` folder to your XAMPP htdocs directory:

```
C:\xampp\htdocs\student_boarding_house\
```

### 2. Import Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click **Import** tab
3. Click **Choose File** and select `database/student_boarding_house.sql`
4. Click **Go** to import

### 3. Configuration

Edit `config/database.php` if needed:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_boarding_house');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_URL', 'http://localhost/student_boarding_house');
```

### 4. Access the Application

Open your browser and navigate to:

```
http://localhost/student_boarding_house/
```

## Default Login Credentials

### Super Administrator
- **Email:** alondes@gmail.com
- **Password:** password123

### Manager
- **Email:** manager@gmail.com
- **Password:** password123

### Student
- **Email:** juan.delacruz@student.edu
- **Password:** password123

## Project Structure

```
student_boarding_house/
├── app/
│   ├── Controllers/          # Application controllers
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── StudentController.php
│   │   ├── ManagerController.php
│   │   └── AdminController.php
│   ├── Models/               # Data models
│   ├── Helpers/              # Helper functions
│   │   └── helpers.php
│   ├── views/
│   │   ├── layouts/          # Layout templates
│   │   │   ├── public.php
│   │   │   ├── student.php
│   │   │   ├── manager.php
│   │   │   └── admin.php
│   │   ├── home/             # Public pages
│   │   ├── auth/             # Authentication pages
│   │   ├── student/          # Student panel views
│   │   ├── manager/          # Manager panel views
│   │   ├── admin/            # Admin panel views
│   │   └── errors/           # Error pages
│   ├── Controller.php        # Base controller
│   ├── Model.php             # Base model
│   ├── Database.php          # Database connection
│   └── Router.php            # URL routing
├── config/
│   └── database.php          # Configuration
├── database/
│   └── student_boarding_house.sql  # Database schema & seed data
├── public/                   # Public assets
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   └── js/
│       └── main.js           # Main JavaScript
├── uploads/                  # User uploads
│   ├── profiles/
│   ├── ids/
│   ├── payments/
│   ├── rooms/
│   └── gallery/
├── routes/
│   └── web.php               # Application routes
├── storage/                  # Logs, cache, backups
├── .htaccess                 # URL rewriting
├── index.php                 # Entry point
└── README.md
```

## Database Tables

| Table | Description |
|-------|-------------|
| users | User accounts with roles |
| managers | Manager profiles |
| students | Student profiles |
| rooms | Room listings |
| room_images | Room photos |
| amenities | Available amenities |
| room_amenities | Room-amenity relationships |
| reservations | Room reservations |
| payments | Payment records |
| payment_history | Payment audit trail |
| receipts | Payment receipts |
| announcements | System announcements |
| gallery | Photo gallery |
| testimonials | Student testimonials |
| faqs | Frequently asked questions |
| maintenance_requests | Maintenance requests |
| complaints | Student complaints |
| feedback | Student feedback |
| notifications | User notifications |
| contact_messages | Contact form messages |
| activity_logs | User activity logs |
| audit_logs | Data change audit trail |
| login_attempts | Login security |
| password_resets | Password reset tokens |
| system_settings | System configuration |

## Technology Stack

- **Backend:** PHP 8.3
- **Database:** MySQL 8
- **Frontend:** HTML5, CSS3, Bootstrap 5.3
- **JavaScript:** ES6, jQuery, AJAX
- **Libraries:** Font Awesome 6, SweetAlert2, Chart.js
- **Architecture:** MVC (Model-View-Controller)
- **Server:** Apache (XAMPP Compatible)

## URL Routes

### Public
- `/` - Home
- `/about` - About Us
- `/rooms` - Available Rooms
- `/room/{id}` - Room Details
- `/gallery` - Photo Gallery
- `/amenities` - Amenities
- `/testimonials` - Testimonials
- `/faqs` - FAQs
- `/contact` - Contact Us
- `/privacy` - Privacy Policy
- `/terms` - Terms & Conditions

### Authentication
- `/login` - Login
- `/register` - Register
- `/logout` - Logout
- `/forgot-password` - Forgot Password
- `/reset-password` - Reset Password

### Student Panel
- `/student/dashboard` - Dashboard
- `/student/profile` - Profile
- `/student/reservations` - Reservations
- `/student/payments` - Payments
- `/student/receipts` - Receipts
- `/student/announcements` - Announcements
- `/student/maintenance` - Maintenance Requests
- `/student/complaints` - Complaints
- `/student/notifications` - Notifications
- `/student/feedback` - Feedback
- `/student/settings` - Settings

### Manager Panel
- `/manager/dashboard` - Dashboard
- `/manager/rooms` - Manage Rooms
- `/manager/reservations` - Manage Reservations
- `/manager/students` - Manage Students
- `/manager/payments` - Manage Payments
- `/manager/announcements` - Manage Announcements
- `/manager/gallery` - Manage Gallery
- `/manager/amenities` - Manage Amenities
- `/manager/maintenance` - Maintenance Requests
- `/manager/complaints` - Complaints
- `/manager/feedback` - Feedback
- `/manager/reports` - Reports
- `/manager/profile` - Profile
- `/manager/settings` - Settings

### Admin Panel
- `/admin/dashboard` - Dashboard
- `/admin/rooms` - Manage Rooms
- `/admin/reservations` - Manage Reservations
- `/admin/students` - Manage Students
- `/admin/payments` - Manage Payments
- `/admin/announcements` - Manage Announcements
- `/admin/gallery` - Manage Gallery
- `/admin/amenities` - Manage Amenities
- `/admin/maintenance` - Maintenance Requests
- `/admin/complaints` - Complaints
- `/admin/feedback` - Feedback
- `/admin/reports` - Reports
- `/admin/manage-managers` - Manage Managers
- `/admin/system-settings` - System Settings
- `/admin/backup` - Database Backup
- `/admin/activity-logs` - Activity Logs
- `/admin/audit-logs` - Audit Logs
- `/admin/profile` - Profile
- `/admin/settings` - Settings

## License

This project is for educational and commercial use.
