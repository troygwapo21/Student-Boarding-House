# PROJECT PROPOSAL

## Student Boarding House Management System
### "Alondes Dorm — Your Home Away From Home"

---

**Prepared by:** [Your Name / Team Name]
**Date:** July 20, 2026
**Version:** 2.0

---

## TABLE OF CONTENTS

I. Introduction
II. Background of the Study
III. Statement of the Problem
IV. Objectives
V. Scope of the Study
VI. Limitations
VII. Significance of the Study
VIII. Proposed Features
IX. Functional Requirements
X. Non-Functional Requirements
XI. System Modules
XII. Database Tables
XIII. Development Methodology
XIV. Technology Stack
XV. Expected Output
XVI. Future Enhancements
Appendices

---

## I. INTRODUCTION

The rapid growth of the student population in urban areas has led to an increasing demand for affordable and accessible boarding house accommodations. Traditional boarding house management — relying on paper-based records, manual payment collection, verbal communication, and in-person coordination — has become inadequate to meet the needs of modern students and property managers.

This proposal presents the design and development of a **Student Boarding House Management System**, a comprehensive web-based platform branded as **"Alondes Dorm"**. The system aims to digitize and streamline all aspects of boarding house operations, including room browsing, online reservations, payment processing, maintenance requests, complaint management, announcements, and administrative oversight.

The platform serves three distinct user roles: **Students (Tenants)**, **Managers (Property Administrators)**, and a **Super Administrator (System Owner)**, each with a dedicated panel offering role-specific functionality. Built using PHP, MySQL, Bootstrap 5, and a clean MVC architecture, the system delivers a modern, responsive, and secure experience across all devices.

By replacing manual, paper-based boarding house operations with an efficient, transparent, and accessible digital system, the proposed platform seeks to improve operational efficiency for management and enhance the overall living experience for student tenants.

---

## II. BACKGROUND OF THE STUDY

### 2.1 About the Client

**Alondes Dorm** is a student-oriented boarding house located at **123 University Avenue, Manila, Philippines**. It provides affordable accommodations for college students from nearby universities such as the University of Santo Tomas, De La Salle University, Far Eastern University, University of the East, and Polytechnic University of the Philippines.

### 2.2 Current Situation

Currently, the boarding house manages its operations through a combination of manual processes:

- Paper-based reservation forms and student registration
- Manual payment collection and receipting without digital records
- Verbal or text-based maintenance request reporting
- Physical bulletin boards for announcements
- In-person complaint filing with no formal tracking system
- Manual room availability tracking using notebooks and spreadsheets

### 2.3 Motivation

The growing number of student tenants and the increasing demand for efficient, contactless, and digital-first services necessitate the development of an integrated management system. With the widespread use of smartphones and internet access among college students, a web-based platform provides the most accessible and cost-effective solution. The system will reduce administrative burden, improve communication, enhance transparency, and provide a better living experience for all students.

---

## III. STATEMENT OF THE PROBLEM

The manual and fragmented management of boarding house operations leads to several critical issues:

1. **Inefficient Room Management** — Tracking room availability, occupancy, and reservations manually is time-consuming and prone to errors, often resulting in double-bookings or missed vacancies.

2. **Payment Tracking Difficulties** — Manual payment recording creates risks of lost receipts, missed payments, lack of financial transparency, and difficulty generating reports for accounting purposes.

3. **Poor Communication** — Important announcements, maintenance updates, policy changes, and reminders are not effectively communicated to all tenants, leading to misunderstandings and missed information.

4. **Delayed Maintenance Response** — Students have no formal channel to report maintenance issues, leading to delayed repairs, escalating damage, and tenant dissatisfaction.

5. **No Centralized Records** — Student information, payment history, reservation records, and room data are scattered across notebooks, files, and personal devices, making data retrieval slow and unreliable.

6. **Lack of Accountability** — Without digital logging, tracking who performed which action and when is nearly impossible, creating potential disputes and compliance issues.

7. **Difficult Refund Processing** — Tenants requesting refunds have no transparent process to follow, leading to delays, disputes, and a lack of accountability for both parties.

**How can technology solve these problems?** By building a centralized web-based management system that provides dedicated interfaces for each user role, automates routine processes, maintains accurate records, ensures real-time communication, and provides a transparent and accountable workflow for all operations including payments and refunds.

---

## IV. OBJECTIVES

### 4.1 General Objective

To design, develop, and deploy a web-based Student Boarding House Management System that digitizes and streamlines the management of student accommodations, improving operational efficiency, financial transparency, and tenant satisfaction.

### 4.2 Specific Objectives

1. Develop a responsive public-facing website for room browsing, information dissemination, and lead generation.
2. Create a Student Panel enabling online reservation, payment submission, maintenance requests, complaints, feedback, and refund requests.
3. Build a Manager Panel for comprehensive room, student, payment, billing, announcement, and facility management.
4. Implement a Super Admin Panel with full system oversight, including manager account management, system settings, database backup, and audit logging.
5. Implement a walk-in payment system for non-tenant students, automatically promoting them to tenant status upon room assignment and payment.
6. Implement a transparent refund request system with eligibility rules, penalty calculations, and admin review workflows.
7. Ensure robust security including CSRF protection, password hashing, login attempt limiting, account lockout, XSS prevention, and role-based access control.
8. Implement a complete database schema with 28+ relational tables to support all system features.
9. Deliver a modern, intuitive user interface with a premium design system using Bootstrap 5.3.
10. Provide real-time sidebar badge notifications across all panels to keep users informed of pending items.

---

## V. SCOPE OF THE STUDY

### 5.1 In Scope

**Public Website:**
- Landing page with hero section, room statistics, featured rooms, testimonials, and announcements
- About Us page with company story, mission, vision, team members, and core values
- Room listing with search, price filtering, and pagination
- Room detail pages with image gallery, amenities, house rules, and similar room suggestions
- Photo gallery with category-filtered image browsing
- Amenities display with icons and descriptions
- Student testimonials and reviews
- Frequently asked questions organized by category
- Contact form with name, email, phone, subject, and message
- Newsletter email subscription
- Privacy Policy and Terms & Conditions pages

**Student Panel:**
- Dashboard with room info, payment summary, recent activity, and quick actions
- Profile management including personal information, emergency contact, and profile picture
- Room reservation with move-in date and duration selection
- Reservation history with status tracking
- Payment submission with GCash and cash support, receipt upload
- Payment history with detailed status tracking
- Walk-in registration for new tenants (room assignment + first payment)
- Maintenance request submission with category, priority, and status tracking
- Complaint filing with category and severity tracking
- Announcement viewing with type and priority indicators
- In-app notification center with read/unread status
- Feedback and suggestion submission with rating system
- Refund request submission with penalty-aware eligibility calculation
- Refund request history with status tracking
- Password change and account settings

**Manager Panel:**
- Dashboard with analytics, charts, occupancy rates, and recent activity
- Room management with CRUD, multi-image support, and amenity assignment
- Reservation management with approve/reject workflow and student details
- Student management with detailed profile viewing
- Payment management with verification, rejection, and receipt generation
- Billing management for creating and tracking tenant bills
- Walk-in payment processing with duration-based billing
- Refund request review with approve/reject workflow and deduction processing
- Announcement management with type and priority
- Gallery image management with categories
- Amenity management with icons
- Maintenance request handling with status updates and admin responses
- Complaint handling with resolution tracking
- Feedback management with reply functionality
- Contact message management with reply functionality
- Financial and occupancy reports with Chart.js visualizations

**Super Admin Panel:**
- All Manager Panel features
- Manager account management (create, edit, delete)
- System settings configuration (site info, payment policies, SMTP, social media)
- Database backup and restore functionality
- Activity and audit logging with detailed change tracking
- About page content management
- Archive and recovery system for soft-deleted records
- Password history tracking for security compliance

### 5.2 Target Users

| User Role | Description | Age Range | Tech Level |
|-----------|-------------|-----------|------------|
| **Students (Tenants)** | College students looking for or currently staying in boarding house accommodations | 18–25 | High (mobile-first) |
| **Managers** | Boarding house staff responsible for day-to-day operations | 25–50 | Moderate to High |
| **Super Administrator** | Boarding house owner or head administrator with full system access | 30–60 | Moderate |
| **General Public** | Prospective students and parents browsing for accommodation options | All ages | Varies |

---

## VI. LIMITATIONS

1. **No Integrated Online Payment Gateway** — Payments are submitted as proof (screenshot/receipt upload) and verified manually by admin or manager. The system does not process real financial transactions through third-party payment processors (e.g., PayMaya, PayPal).

2. **No SMS or Email Notification System** — While SMTP is configured, the system does not currently send real email or SMS notifications. All notifications are delivered in-app only.

3. **No Mobile Application** — The system is web-based only and accessed through web browsers. No native iOS or Android application is developed.

4. **No Real-Time Chat** — Communication between students and management is handled through messages and feedback forms, not through a real-time chat interface.

5. **No Third-Party ID Verification** — Student identity verification is handled manually. No integration with government or school ID verification APIs exists.

6. **Single Boarding House Instance** — The system is designed for a single boarding house property and does not support multi-property or franchise management.

7. **Manual Admin Intervention Required** — Key workflows such as reservation approval, payment verification, and refund processing require manual review and action by a manager or administrator.

8. **Local Server Deployment** — The system is currently configured for local development (XAMPP) and has not been deployed to a production cloud server.

---

## VII. SIGNIFICANCE OF THE STUDY

### 7.1 For Students (Tenants)
- **Convenience** — Browse rooms, submit reservations, make payments, and file requests online without visiting the office.
- **Transparency** — Real-time access to payment history, reservation status, and billing information.
- **Faster Service** — Maintenance requests and complaints are logged and tracked digitally, reducing response times.
- **Accountability** — All transactions are recorded and auditable, protecting tenant rights.

### 7.2 For Managers (Property Administrators)
- **Efficiency** — Automates routine tasks such as bill creation, reservation processing, and payment tracking.
- **Organization** — Centralized management of rooms, students, payments, and communications in one platform.
- **Reporting** — Built-in charts and analytics for financial and occupancy reporting.
- **Reduced Workload** — Digital workflows replace paper-based processes, saving time and reducing errors.

### 7.3 For the Super Administrator (System Owner)
- **Full Oversight** — Complete visibility into all operations, financials, and system activities.
- **Security** — Audit logs and activity tracking ensure accountability for all system actions.
- **Control** — System settings, manager account management, and database backup provide full operational control.
- **Scalability** — Clean MVC architecture allows easy extension with new features.

### 7.4 For the Research Community
- **Reference System** — Serves as a reference implementation for web-based property management systems.
- **Academic Value** — Demonstrates practical application of MVC architecture, database design, and security practices in a real-world context.

---

## VIII. PROPOSED FEATURES

### 8.1 Public Website Features

| Feature | Description |
|---------|-------------|
| Landing Page | Hero section, room statistics counter, featured rooms, testimonials, latest announcements |
| Room Listing | Browse all rooms with search, type filtering, price range, and pagination |
| Room Detail | Full room information, multi-image gallery, amenities, house rules, similar rooms |
| Photo Gallery | Category-filtered image gallery showcasing facilities and rooms |
| Amenities Page | Display of all available amenities with icons and descriptions |
| Testimonials | Student reviews and ratings |
| FAQs | Frequently asked questions organized by category |
| Contact Us | Contact form with validation and server-side storage |
| Newsletter | Email subscription for updates and announcements |
| About Us | Company story, mission, vision, team members, and core values |

### 8.2 Student Panel Features

| Feature | Description |
|---------|-------------|
| Dashboard | Room info, payment summary, recent activity, quick action buttons |
| Profile Management | Edit personal info, student details, emergency contact, profile picture |
| Room Reservation | Browse available rooms and submit reservation with move-in date and duration |
| Reservation History | View all reservations with status tracking |
| Payment Submission | Submit payment with type, amount, method, reference number, receipt upload |
| Payment History | View all payment records with detailed status |
| Walk-In Registration | New student registration with room assignment and initial payment |
| Maintenance Requests | Submit requests with category, priority, description; track status |
| Complaints | File complaints with category and severity; track resolution |
| Announcements | View all announcements with type and priority indicators |
| Notifications | In-app notification center with read/unread status |
| Feedback | Submit suggestions, compliments, complaints, or inquiries with rating |
| Refund Requests | Submit refund requests with eligibility-based automatic amount calculation |
| Refund History | View all refund requests with status tracking and processing details |
| Password Change | Update account password with current password verification |
| Settings | Manage notification preferences and account settings |

### 8.3 Manager Panel Features

| Feature | Description |
|---------|-------------|
| Dashboard | Analytics with charts: occupancy rate, revenue, pending items, recent activity |
| Room Management | Full CRUD for rooms with images, amenities, pricing, capacity, and status |
| Reservation Management | View, approve, or reject reservations with notes and student details |
| Student Management | View all students, detailed profiles, and tenant status |
| Payment Management | View, verify, or reject payments with receipt verification |
| Billing Management | Create and manage tenant bills (rent, electric, water, etc.) |
| Walk-In Payment Processing | Process walk-in students with duration selection, auto-calculated billing, and tenant promotion |
| Refund Request Review | Review, approve, or reject refund requests with penalty calculations and deduction processing |
| Announcement Management | Create, edit, delete announcements with type and priority |
| Gallery Management | Upload, categorize, and delete gallery images |
| Amenity Management | Create, edit, delete amenities with icons |
| Maintenance Handling | View, update status, and respond to maintenance requests |
| Complaint Handling | View, update status, and resolve complaints |
| Feedback Management | View, reply to, and manage student feedback |
| Contact Messages | View, read, reply to, and manage contact form submissions |
| Reports | Financial and occupancy reports with Chart.js visualizations |

### 8.4 Super Admin Panel Features

All Manager Panel features plus:

| Feature | Description |
|---------|-------------|
| Manager Account Management | Create, edit, and delete manager accounts |
| System Settings | Configure site name, contact info, payment policies, SMTP, social media, content |
| Database Backup | One-click database backup with downloadable SQL files |
| Database Restore | Restore database from uploaded backup file |
| Activity Logs | View all user activity logs with user, action, IP, and timestamp |
| Audit Logs | View detailed data change audit trail with old/new JSON values |
| Archive & Recovery | Soft-delete records with recovery capability |
| About Page Management | Edit about page content, team members, and core values |
| Password History | Track password change history for security compliance |

---

## IX. FUNCTIONAL REQUIREMENTS

### 9.1 Authentication & Authorization

| ID | Requirement |
|----|-------------|
| FR-AUTH-01 | The system shall allow users to register with email, password, and personal details |
| FR-AUTH-02 | The system shall authenticate users via email and password |
| FR-AUTH-03 | The system shall limit login attempts to 3 per session, locking the account for 10 minutes after exceeding the limit |
| FR-AUTH-04 | The system shall hash all passwords using bcrypt before storage |
| FR-AUTH-05 | The system shall support "Remember Me" persistent login via secure tokens |
| FR-AUTH-06 | The system shall provide password reset functionality via token-based email links |
| FR-AUTH-07 | The system shall enforce role-based access control (super_admin, manager, student) |
| FR-AUTH-08 | The system shall regenerate session IDs on login to prevent session fixation |
| FR-AUTH-09 | The system shall maintain password history to prevent password reuse |

### 9.2 Room Management

| ID | Requirement |
|----|-------------|
| FR-ROOM-01 | The system shall allow managers/admins to create, edit, and delete room listings |
| FR-ROOM-02 | The system shall support multiple images per room with primary image designation |
| FR-ROOM-03 | The system shall assign amenities to rooms from the amenities catalog |
| FR-ROOM-04 | The system shall track room status (available, occupied, maintenance) |
| FR-ROOM-05 | The system shall display room availability with remaining capacity slots |
| FR-ROOM-06 | The system shall support room pricing with monthly rent and advance payment fields |

### 9.3 Reservation Management

| ID | Requirement |
|----|-------------|
| FR-RES-01 | The system shall allow students to submit reservation requests for available rooms |
| FR-RES-02 | The system shall allow managers/admins to approve or reject reservations with notes |
| FR-RES-03 | The system shall auto-generate unique reservation codes (RES-YYYY-NNN) |
| FR-RES-04 | The system shall create billing records automatically upon reservation approval |
| FR-RES-05 | The system shall send in-app notifications for reservation status changes |
| FR-RES-06 | The system shall expire pending reservations after a configurable number of days |

### 9.4 Payment Management

| ID | Requirement |
|----|-------------|
| FR-PAY-01 | The system shall allow students to submit payments with type, amount, method, and reference number |
| FR-PAY-02 | The system shall support GCash and cash payment methods |
| FR-PAY-03 | The system shall require GCash reference numbers for GCash payments |
| FR-PAY-04 | The system shall allow managers/admins to verify or reject payment submissions |
| FR-PAY-05 | The system shall auto-generate receipts upon payment verification |
| FR-PAY-06 | The system shall track payment history with status changes and timestamps |
| FR-PAY-07 | The system shall support walk-in payment processing with auto-calculated amounts based on duration |
| FR-PAY-08 | The system shall constrain walk-in payment duration to a maximum of 3 months |
| FR-PAY-09 | The system shall auto-calculate walk-in payment amounts as (rent x duration) + advance |

### 9.5 Billing Management

| ID | Requirement |
|----|-------------|
| FR-BILL-01 | The system shall auto-create billing records upon reservation approval |
| FR-BILL-02 | The system shall support custom billing types (rent, electric, water, etc.) |
| FR-BILL-03 | The system shall allow managers/admins to create, edit, and delete bills |
| FR-BILL-04 | The system shall track billing status (pending, paid, overdue) |

### 9.6 Walk-In Student Payment

| ID | Requirement |
|----|-------------|
| FR-WALK-01 | The system shall allow managers/admins to register walk-in students who are not yet tenants |
| FR-WALK-02 | The system shall auto-assign rooms and create approved reservations upon walk-in payment |
| FR-WALK-03 | The system shall auto-create billing records for walk-in tenants |
| FR-WALK-04 | The system shall display room status badges (available, limited, full) in room selection |
| FR-WALK-05 | The system shall auto-calculate total due as (rent x duration) + advance payment |
| FR-WALK-06 | The system shall display a monthly breakdown summary before submission |

### 9.7 Refund Request Management

| ID | Requirement |
|----|-------------|
| FR-REFUND-01 | The system shall allow tenants to submit refund requests for monthly rent, advance payment, or all payments |
| FR-REFUND-02 | The system shall auto-calculate refund amounts based on payment history and penalty rules |
| FR-REFUND-03 | The system shall require tenants to have paid more than 2 months of rent to be eligible for monthly rent refund |
| FR-REFUND-04 | The system shall deduct a 1-month penalty from monthly rent refund calculations |
| FR-REFUND-05 | The system shall require tenants to have more than 2 months of rent paid to be eligible for All Payments Refund |
| FR-REFUND-06 | The system shall display the full calculation breakdown showing total paid, penalty, pending, and final refund amount |
| FR-REFUND-07 | The system shall auto-fill and lock the refund amount field to prevent manual tampering |
| FR-REFUND-08 | The system shall allow managers/admins to approve or reject refund requests with notes |
| FR-REFUND-09 | The system shall auto-deduct approved refund amounts from payment records |
| FR-REFUND-10 | The system shall prevent duplicate pending refund requests |
| FR-REFUND-11 | The system shall allow tenants to cancel their own pending refund requests |

### 9.8 Maintenance & Complaint Management

| ID | Requirement |
|----|-------------|
| FR-MAINT-01 | The system shall allow students to submit maintenance requests with category, priority, and description |
| FR-MAINT-02 | The system shall allow managers/admins to update maintenance status and add responses |
| FR-MAINT-03 | The system shall auto-generate unique maintenance codes (MNT-YYYY-NNN) |
| FR-COMP-01 | The system shall allow students to file complaints with category and severity |
| FR-COMP-02 | The system shall allow managers/admins to update complaint status and resolve complaints |
| FR-COMP-03 | The system shall auto-generate unique complaint codes (CMP-YYYY-NNN) |

### 9.9 Communication Features

| ID | Requirement |
|----|-------------|
| FR-COMM-01 | The system shall allow managers/admins to create announcements with type and priority |
| FR-COMM-02 | The system shall display announcements to all students based on publish status |
| FR-COMM-03 | The system shall provide an in-app notification center with read/unread status |
| FR-COMM-04 | The system shall allow students to submit feedback with ratings |
| FR-COMM-05 | The system shall allow the public to submit contact messages |
| FR-COMM-06 | The system shall support sidebar badge notifications with real-time polling updates |

### 9.10 Administrative Features

| ID | Requirement |
|----|-------------|
| FR-ADMIN-01 | The system shall provide a dashboard with analytics, charts, and key metrics |
| FR-ADMIN-02 | The system shall support one-click database backup with downloadable SQL files |
| FR-ADMIN-03 | The system shall support database restore from uploaded backup files |
| FR-ADMIN-04 | The system shall maintain activity logs recording user actions with IP and timestamp |
| FR-ADMIN-05 | The system shall maintain audit logs with old/new value changes in JSON format |
| FR-ADMIN-06 | The system shall support soft-delete with archive and recovery functionality |
| FR-ADMIN-07 | The system shall allow configurable system settings via admin panel |

---

## X. NON-FUNCTIONAL REQUIREMENTS

### 10.1 Performance

| ID | Requirement |
|----|-------------|
| NFR-PERF-01 | The system shall load pages within 3 seconds on a standard broadband connection |
| NFR-PERF-02 | The system shall support at least 50 concurrent users without performance degradation |
| NFR-PERF-03 | Database queries shall execute within 500ms for standard operations |
| NFR-PERF-04 | Sidebar badge polling shall execute every 30 seconds without blocking the UI |

### 10.2 Security

| ID | Requirement |
|----|-------------|
| NFR-SEC-01 | All passwords shall be hashed using bcrypt before storage |
| NFR-SEC-02 | All forms shall include CSRF token validation |
| NFR-SEC-03 | All user output shall be escaped using htmlspecialchars() to prevent XSS |
| NFR-SEC-04 | All database queries shall use PDO prepared statements to prevent SQL injection |
| NFR-SEC-05 | Role-based access control shall be enforced on all routes |
| NFR-SEC-06 | Session IDs shall be regenerated on authentication state changes |
| NFR-SEC-07 | Login attempts shall be limited and accounts locked after exceeding thresholds |
| NFR-SEC-08 | File uploads shall validate file types and sizes |
| NFR-SEC-09 | Sensitive configuration data shall not be exposed in client-side code |

### 10.3 Usability

| ID | Requirement |
|----|-------------|
| NFR-USE-01 | The system shall be fully responsive, supporting mobile (320px+), tablet, and desktop screens |
| NFR-USE-02 | The system shall use consistent typography (Poppins for headings, Inter for body) and color scheme across all pages |
| NFR-USE-03 | The system shall provide flash message notifications for all user actions (success, error, warning) |
| NFR-USE-04 | The system shall provide loading indicators for asynchronous operations |
| NFR-USE-05 | The system shall support touch-friendly UI elements on mobile devices |
| NFR-USE-06 | The system shall provide form validation feedback on both client and server side |

### 10.4 Reliability

| ID | Requirement |
|----|-------------|
| NFR-REL-01 | The system shall maintain data integrity through foreign key constraints and transaction support |
| NFR-REL-02 | The system shall handle errors gracefully with appropriate user-facing messages |
| NFR-REL-03 | The system shall backup data automatically or provide manual backup functionality |
| NFR-REL-04 | The system shall maintain audit trails for all data modifications |

### 10.5 Compatibility

| ID | Requirement |
|----|-------------|
| NFR-COMP-01 | The system shall be compatible with Chrome 90+, Firefox 90+, Safari 14+, and Edge 90+ |
| NFR-COMP-02 | The system shall be compatible with PHP 8.0+ and MySQL 5.7+ / MySQL 8.0 |
| NFR-COMP-03 | The system shall be compatible with XAMPP (Apache) development environment |
| NFR-COMP-04 | The system shall function with JavaScript disabled for core navigation (progressive enhancement) |

### 10.6 Maintainability

| ID | Requirement |
|----|-------------|
| NFR-MAINT-01 | The system shall follow MVC architecture for separation of concerns |
| NFR-MAINT-02 | The system shall use a custom URL router for clean URL management |
| NFR-MAINT-03 | The system shall organize views by role and module for easy navigation |
| NFR-MAINT-04 | The system shall use helper functions for common operations (formatting, validation, output escaping) |
| NFR-MAINT-05 | The system shall use CSS custom properties (variables) for consistent theming |

---

## XI. SYSTEM MODULES

The system is organized into the following modules, each corresponding to a functional area of the application:

### 11.1 Module Overview

| Module | Description | Panels |
|--------|-------------|--------|
| **Authentication** | User registration, login, logout, password reset, session management | All |
| **Public Website** | Landing page, rooms, gallery, testimonials, FAQs, contact, newsletter | Public |
| **Room Management** | CRUD operations, multi-image support, amenity assignment, capacity tracking | Manager, Admin |
| **Reservation Management** | Student booking, approval workflow, status tracking, auto-billing | Student, Manager, Admin |
| **Payment Management** | Payment submission, verification, rejection, receipt generation | Student, Manager, Admin |
| **Billing Management** | Bill creation, tracking, payment association | Student, Manager, Admin |
| **Walk-In Payment** | Walk-in student registration, room assignment, payment processing | Manager, Admin |
| **Walk-In Student Payment** | Non-tenant student walk-in with auto-promotion to tenant | Manager, Admin |
| **Refund Requests** | Refund eligibility, penalty calculation, submission, review, approval | Student, Manager, Admin |
| **Maintenance** | Request submission, status tracking, admin response | Student, Manager, Admin |
| **Complaints** | Complaint filing, resolution tracking, admin response | Student, Manager, Admin |
| **Announcements** | Creation, publishing, student viewing | Student, Manager, Admin |
| **Notifications** | In-app notification delivery, read/unread tracking, sidebar badges | All (except Public) |
| **Feedback** | Student feedback submission with rating, admin reply | Student, Manager, Admin |
| **Gallery** | Image upload, categorization, display | Public, Manager, Admin |
| **Amenities** | Amenity management, room assignment | Manager, Admin |
| **Reports** | Financial reports, occupancy analytics, Chart.js visualizations | Manager, Admin |
| **System Settings** | Site configuration, payment policies, SMTP, social media | Admin |
| **Manager Accounts** | Manager CRUD, profile management | Admin |
| **Database Management** | Backup, restore, archive & recovery | Admin |
| **Activity & Audit Logs** | User action tracking, data change audit trail | Admin |
| **Sidebar Badges** | Real-time notification counters with 30-second polling | All Panels |

### 11.2 Module Interactions

```
Public Website ──> Registration ──> Student Panel
                                       │
                                       ├──> Room Reservation ──> Manager Approval ──> Billing Creation
                                       ├──> Payment Submission ──> Manager Verification ──> Receipt Generation
                                       ├──> Walk-In Registration ──> Room Assignment ──> Auto-Tenant Promotion
                                       ├──> Refund Request ──> Penalty Calculation ──> Manager Review
                                       ├──> Maintenance Request ──> Manager Handling ──> Resolution
                                       └──> Complaint Filing ──> Manager Handling ──> Resolution

Manager Panel ──> Room/Student/Payment/Billing Management
               ──> Walk-In Processing ──> Tenant Auto-Creation
               ──> Refund Review ──> Approval/Rejection ──> Deduction Processing
               ──> Announcement Publishing ──> Student Notifications

Admin Panel ──> All Manager Features
            ──> Manager Account Management
            ──> System Settings
            ──> Database Backup/Restore
            ──> Activity & Audit Logging
```

---

## XII. DATABASE TABLES

### 12.1 Database Overview

| Property | Value |
|----------|-------|
| **Engine** | InnoDB (for foreign key support) |
| **Character Set** | utf8mb4 (full Unicode support) |
| **Total Tables** | 28+ (including migration-created tables) |
| **Seed Data** | Pre-populated with sample data for testing |

### 12.2 Complete Table List

| # | Table Name | Purpose | Key Relationships |
|---|-----------|---------|-------------------|
| 1 | `users` | User accounts with roles (super_admin, manager, student) | Referenced by all profile tables |
| 2 | `managers` | Manager profile data | FK → users.id |
| 3 | `students` | Student profile data | FK → users.id |
| 4 | `rooms` | Room listings with pricing and capacity | Independent |
| 5 | `room_images` | Room photos with primary flag | FK → rooms.id |
| 6 | `amenities` | Facility amenity definitions | Independent |
| 7 | `room_amenities` | Room-to-amenity many-to-many mapping | FK → rooms.id, amenities.id |
| 8 | `reservations` | Room reservation records | FK → students.id, rooms.id |
| 9 | `payments` | Payment records with verification | FK → students.id, reservations.id |
| 10 | `payment_history` | Payment audit trail | FK → payments.id |
| 11 | `receipts` | Generated payment receipts | FK → payments.id |
| 12 | `announcements` | System announcements | FK → users.id (created_by) |
| 13 | `gallery` | Gallery images by category | Independent |
| 14 | `testimonials` | Student reviews | Independent |
| 15 | `faqs` | Frequently asked questions | Independent |
| 16 | `maintenance_requests` | Maintenance tickets | FK → students.id, rooms.id |
| 17 | `complaints` | Student complaints | FK → students.id |
| 18 | `feedback` | Student feedback | FK → students.id |
| 19 | `notifications` | In-app notifications | FK → users.id |
| 20 | `contact_messages` | Contact form messages | Independent |
| 21 | `activity_logs` | User activity tracking | FK → users.id |
| 22 | `audit_logs` | Data change audit trail | FK → users.id |
| 23 | `login_attempts` | Login security tracking | Independent |
| 24 | `password_resets` | Password reset tokens | Independent |
| 25 | `user_module_reads` | Sidebar badge last-read tracking | FK → users.id |
| 26 | `system_settings` | Key-value site configuration | Independent |
| 27 | `team_members` | About page team data | Independent |
| 28 | `about_values` | About page core values | Independent |
| 29 | `refund_requests` | Refund request records | FK → students.id, reservations.id |
| 30 | `archived_records` | Soft-deleted records for recovery | Independent |
| 31 | `password_history` | Password change history | FK → users.id |
| 32 | `guardians` | Tenant guardian information | FK → students.id |

### 12.3 Key Database Features

- **Auto-generated codes:** Reservation codes (RES-YYYY-NNN), payment codes (PAY-YYYY-NNN), maintenance codes (MNT-YYYY-NNN), complaint codes (CMP-YYYY-NNN), refund codes (RFD-YYYY-NNN)
- **Timestamps:** All tables include `created_at` and `updated_at` columns
- **Soft status tracking:** Enum-based status fields for all workflow entities
- **JSON audit trail:** `old_values` and `new_values` stored as JSON in audit_logs
- **Proper indexing:** Indexes on frequently queried columns (status, foreign keys, dates)
- **Referential integrity:** Foreign key constraints between related tables

### 12.4 Entity-Relationship Diagram (Key Relationships)

```
users ─────┬──────> managers
           ├──────> students ──────┬──────> reservations ──────> rooms
           │                       ├──────> payments ──────> receipts
           │                       ├──────> maintenance_requests
           │                       ├──────> complaints
           │                       ├──────> feedback
           │                       └──────> refund_requests
           ├──────> notifications
           ├──────> activity_logs
           └──────> audit_logs

rooms ─────┬──────> room_images
           └──────> room_amenities ──> amenities
```

---

## XIII. DEVELOPMENT METHODOLOGY

### 13.1 Agile Iterative Development

The project follows an **Agile Iterative Development** methodology, emphasizing incremental delivery, continuous feedback, and adaptive planning. The development process is organized into the following phases:

### 13.2 Development Phases

| Phase | Duration | Activities |
|-------|----------|------------|
| **Phase 1: Planning & Analysis** | 1 week | Requirements gathering, stakeholder interviews, system analysis, proposal documentation |
| **Phase 2: Design** | 1 week | Database design and schema creation, UI/UX wireframing, mockup creation, design system development |
| **Phase 3: Backend Development** | 3 weeks | MVC architecture setup, database implementation, controller development, routing system, helper functions |
| **Phase 4: Frontend Development** | 2 weeks | Layout templates, public pages, student panel views, manager panel views, admin panel views |
| **Phase 5: Integration** | 1 week | Frontend-backend integration, AJAX functionality, file upload system, chart implementation |
| **Phase 6: Security Implementation** | 1 week | CSRF protection, XSS prevention, session management, login security, RBAC, audit logging |
| **Phase 7: Testing** | 1 week | Unit testing, integration testing, user acceptance testing, security testing, responsive testing |
| **Phase 8: Deployment & Documentation** | 1 week | Server setup, database import, configuration, user documentation, technical documentation |

**Total Estimated Duration:** 11 weeks (approximately 3 months)

### 13.3 Development Practices

- **Modular Development** — Each feature is developed as an independent module, tested, and then integrated
- **MVC Separation** — Strict separation of Model (data), View (presentation), and Controller (logic) layers
- **Incremental Testing** — Each phase includes testing before proceeding to the next
- **Code Organization** — Consistent file naming, directory structure, and coding conventions
- **Version Control** — Git-based version control for tracking changes and enabling rollback

---

## XIV. TECHNOLOGY STACK

### 14.1 Core Technologies

| Layer | Technology | Version |
|-------|-----------|---------|
| **Backend Language** | PHP | 8.0+ (recommended: 8.3) |
| **Database** | MySQL | 5.7+ / 8.0 |
| **Web Server** | Apache | 2.4+ (XAMPP) |
| **Frontend Markup** | HTML5 | — |
| **Styling** | CSS3 + Bootstrap | 5.3 |
| **Client-Side Scripting** | JavaScript | ES6 |

### 14.2 Libraries & Frameworks

| Category | Library | Purpose |
|----------|---------|---------|
| **CSS Framework** | Bootstrap 5.3 | Responsive grid, components, utilities |
| **Design System** | Custom CSS (2,324 lines) | Premium design with CSS custom properties |
| **JavaScript** | Vanilla ES6 + jQuery | DOM manipulation, AJAX, event handling |
| **Charts** | Chart.js | Financial and occupancy data visualizations |
| **Icons** | Font Awesome 6 | Icon library for UI elements |
| **Alerts** | SweetAlert2 | Animated flash messages and confirmations |
| **Animations** | AOS-like scroll animations | Scroll-triggered animations |
| **Badge Polling** | Custom badges.js (194 lines) | Real-time sidebar notification updates |

### 14.3 Architecture

| Component | Technology |
|-----------|-----------|
| **Architecture Pattern** | MVC (Model-View-Controller) |
| **Routing** | Custom URL Router with .htaccess rewriting |
| **Database Access** | PDO with prepared statements |
| **Session Management** | PHP native sessions with security hardening |
| **Authentication** | Custom built-in (bcrypt + session + tokens) |
| **CSRF Protection** | Token-based CSRF on all forms |

### 14.4 Development Tools

| Tool | Purpose |
|------|---------|
| **XAMPP** | Local development environment (Apache + MySQL + PHP) |
| **VS Code / PHPStorm** | Code editor |
| **Chrome DevTools** | Debugging and performance profiling |
| **phpMyAdmin** | Database management |
| **Git** | Version control |

### 14.5 System Architecture Diagram

```
┌─────────────────────────────────────────────────┐
│                   CLIENT BROWSER                │
│  ┌─────────┐  ┌──────────┐  ┌───────────────┐  │
│  │  HTML5   │  │Bootstrap │  │  JavaScript   │  │
│  │  Views   │  │   5.3    │  │  (ES6/jQuery) │  │
│  └─────────┘  └──────────┘  └───────────────┘  │
└───────────────────┬─────────────────────────────┘
                    │ HTTP Request
┌───────────────────▼─────────────────────────────┐
│                APACHE WEB SERVER                 │
│  ┌──────────────────────────────────────────┐   │
│  │              .htaccess URL Rewriting      │   │
│  └──────────────────┬───────────────────────┘   │
│  ┌──────────────────▼───────────────────────┐   │
│  │         Router (app/Router.php)           │   │
│  └──────────────────┬───────────────────────┘   │
│  ┌──────────────────▼───────────────────────┐   │
│  │     Controller (app/Controllers/*.php)    │   │
│  └──────┬────────────────────────┬──────────┘   │
│  ┌──────▼──────┐          ┌──────▼──────┐       │
│  │   Model      │          │    Views     │      │
│  │ (Database)   │          │  (PHP/HTML)  │      │
│  └──────┬──────┘          └─────────────┘       │
└─────────┼───────────────────────────────────────┘
          │ PDO Prepared Statements
┌─────────▼───────────────────────────────────────┐
│              MySQL 8.0 DATABASE                  │
│         (28+ tables, utf8mb4, InnoDB)            │
└─────────────────────────────────────────────────┘
```

---

## XV. EXPECTED OUTPUT

### 15.1 Deliverables

| # | Deliverable | Description |
|---|-------------|-------------|
| 1 | **Public Website** | Fully functional landing page with room browsing, gallery, testimonials, FAQs, contact form, and newsletter |
| 2 | **Student Panel** | Complete student dashboard with reservation, payment, maintenance, complaint, feedback, and refund request capabilities |
| 3 | **Manager Panel** | Full management dashboard with room, student, payment, billing, walk-in, and refund management |
| 4 | **Super Admin Panel** | Complete system administration with manager accounts, settings, backup/restore, and audit logging |
| 5 | **Database Schema** | 28+ relational tables with foreign keys, indexes, and seed data |
| 6 | **MVC Architecture** | Clean separation of concerns with custom routing, base controller, and helper functions |
| 7 | **Security Implementation** | CSRF protection, XSS prevention, password hashing, login limiting, RBAC, and audit trails |
| 8 | **Responsive UI** | Premium design system with mobile-first responsive layout |
| 9 | **Real-Time Notifications** | Sidebar badges with 30-second polling and module-read tracking |
| 10 | **Documentation** | Technical documentation, database schema reference, and user guide |

### 15.2 Expected System Behavior

| Scenario | Expected Output |
|----------|----------------|
| Student registers and logs in | Access to student dashboard with room info, payment summary, and quick actions |
| Student submits reservation | Reservation created with pending status; manager notified via sidebar badge |
| Manager approves reservation | Reservation status changes to approved; billing auto-created; student notified |
| Student submits payment | Payment record created with pending status; manager notified for verification |
| Manager verifies payment | Payment status changes to paid; receipt auto-generated; student notified |
| Walk-in student processes payment | Room assigned, reservation auto-created (approved), billing created, student auto-promoted to tenant |
| Student requests refund (monthly, 4 months paid) | Eligible: refund amount auto-calculated as (4 x rent) - 1 month penalty; shown with full breakdown |
| Student requests refund (monthly, 2 months paid) | Not eligible: "All Payments Refund requires more than 2 months of paid rent" |
| Admin approves refund | Refund approved; deduction processed from payment records; student notified |
| Any sidebar badge count changes | Badge auto-updates within 30 seconds via polling mechanism |

### 15.3 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Administrator | alondes@gmail.com | password123 |
| Manager | manager@gmail.com | password123 |
| Student | juan.delacruz@student.edu | password123 |

---

## XVI. FUTURE ENHANCEMENTS

### 16.1 Short-Term Enhancements

| # | Enhancement | Description |
|---|-------------|-------------|
| 1 | **Email/SMS Notifications** | Integrate SMTP email and SMS gateway for transactional notifications (payment confirmations, reservation status, reminders) |
| 2 | **Integrated Payment Gateway** | Integrate PayMaya, GCash API, or PayPal for real-time online payment processing |
| 3 | **Advanced Search & Filtering** | Add price range sliders, map-based room search, and advanced availability filters |
| 4 | **Tenant Dashboard Widgets** | Customizable dashboard widgets for quick access to frequently used features |
| 5 | **Multi-Language Support** | Add Filipino/Tagalog language option for broader accessibility |

### 16.2 Medium-Term Enhancements

| # | Enhancement | Description |
|---|-------------|-------------|
| 6 | **Mobile Application** | Develop native iOS and Android applications using React Native or Flutter |
| 7 | **Real-Time Chat** | Implement WebSocket-based real-time chat between students and management |
| 8 | **Digital Contract Signing** | Add e-signature support for rental agreements and policy acknowledgment |
| 9 | **Automated Late Fee Calculation** | Implement cron-based automatic late fee assessment for overdue payments |
| 10 | **Tenant Rating System** | Allow managers to rate tenants and vice versa for mutual accountability |

### 16.3 Long-Term Enhancements

| # | Enhancement | Description |
|---|-------------|-------------|
| 11 | **Multi-Property Support** | Extend the system to manage multiple boarding house properties from a single admin panel |
| 12 | **AI-Powered Analytics** | Implement machine learning for occupancy prediction, pricing optimization, and anomaly detection |
| 13 | **IoT Integration** | Smart room monitoring with IoT sensors for energy management and security |
| 14 | **API Development** | Build RESTful APIs for third-party integrations and mobile app support |
| 15 | **Cloud Deployment** | Deploy to cloud infrastructure (AWS/GCP) with auto-scaling and CDN |
| 16 | **ID Verification Integration** | Integrate with school or government ID verification APIs for automated student verification |
| 17 | **Occupancy Prediction** | Use historical data to predict seasonal demand and optimize pricing |
| 18 | **Document Management** | Digital storage and management of tenant documents (IDs, contracts, receipts) |

---

## APPENDICES

### Appendix A: Available Amenities (18 Total)

| # | Amenity |
|---|---------|
| 1 | Free Wi-Fi |
| 2 | Air Conditioning |
| 3 | Study Desk |
| 4 | Wardrobe |
| 5 | Private Bathroom |
| 6 | Hot Water |
| 7 | Laundry Service |
| 8 | Kitchen Access |
| 9 | Common Area |
| 10 | 24/7 Security |
| 11 | CCTV Surveillance |
| 12 | Parking Space |
| 13 | Water Dispenser |
| 14 | Generator |
| 15 | Cleaning Service |
| 16 | Bed Linens |
| 17 | Electrical Outlet |
| 18 | Balcony |

### Appendix B: Sample Room Types and Pricing

| Room Type | Example | Monthly Rent | Advance Payment | Capacity |
|-----------|---------|-------------|-----------------|----------|
| Bedspace | Sunrise Bedspace | ₱800.00 | ₱800.00 | 1 |
| Bedspace (Shared) | Twin Bedspace | ₱800.00 | ₱800.00 | 2 |
| Bedspace (Group) | Explorer Bedspace | ₱800.00 | ₱800.00 | 3 |
| Bedspace (Dorm) | Community Bedspace | ₱800.00 | ₱800.00 | 6 |
| Single | Premier Bedspace | ₱800.00 | ₱800.00 | 1 |
| Studio | Studio Bedspace | ₱800.00 | ₱800.00 | 2 |

### Appendix C: System Settings (Configurable via Admin)

| Setting Key | Description | Default Value |
|-------------|-------------|---------------|
| site_name | Website name | Alondes Dorm |
| site_tagline | Website tagline | Your Home Away From Home |
| site_email | Contact email | alondes@gmail.com |
| site_phone | Contact phone | +63 912 345 6789 |
| site_address | Physical address | 123 University Avenue, Manila |
| currency | Currency code | PHP |
| currency_symbol | Currency symbol | ₱ |
| late_fee | Late payment fee | ₱100.00 |
| grace_period_days | Payment grace period | 5 days |
| max_login_attempts | Security: max failed logins | 3 |
| lockout_duration | Account lockout time | 10 minutes |
| reservation_expiry_days | Reservation expiry | 5 days |

### Appendix D: Key Routes Summary

| Route Group | Count |
|-------------|-------|
| Public Routes | 12 |
| Authentication Routes | 9 |
| Student Panel Routes | 42 |
| Manager Panel Routes | 90 |
| Super Admin Panel Routes | 99 |
| API Routes | 2 |
| **Total** | **~254** |

---

**End of Proposal**

---

*This document follows the standard project proposal format and can be submitted as a formal academic or professional project proposal.*
