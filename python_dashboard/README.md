# Student Boarding House - Flask Dashboard

Python Flask dashboard integration for the Student Boarding House Management System.

## Architecture

```
PHP Website (Existing)
    ↓
MySQL Database (Shared)
    ↑
Python Flask Dashboard (New)
    ↓
Admin / Manager / Student Dashboards
Analytics & Charts
Reports (PDF/CSV Export)
```

## Features

- **Admin Dashboard**: System-wide analytics, revenue, occupancy, tenant management
- **Manager Dashboard**: Boarding house specific data, payments, reservations, maintenance
- **Student Dashboard**: Personal payments, reservations, receipts, announcements, maintenance requests
- **Analytics API**: RESTful endpoints for real-time charts (Chart.js)
- **Reports**: PDF/CSV export with date filtering
- **Security**: RBAC, CSRF protection, secure sessions, SQL injection prevention

## Requirements

- Python 3.9+
- MySQL Database (existing)
- See `requirements.txt` for Python packages

## Installation

```bash
cd python_dashboard
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate
pip install -r requirements.txt
cp .env.example .env
# Edit .env with your database credentials
flask run
```

## Configuration

Environment variables in `.env`:

```env
FLASK_ENV=development
FLASK_SECRET_KEY=your-secret-key-min-32-chars
DB_HOST=sql311.infinityfree.com
DB_PORT=3306
DB_NAME=if0_42763980_student_boarding_house
DB_USER=if0_42763980
DB_PASSWORD=your-password
SESSION_COOKIE_SECURE=False
LOG_LEVEL=DEBUG
```

## Project Structure

```
python_dashboard/
├── app.py                 # Flask application factory
├── config.py              # Configuration classes
├── database.py            # Database queries
├── requirements.txt       # Python dependencies
├── .env                   # Environment variables
├── blueprints/
│   ├── auth/              # Authentication routes
│   ├── admin/             # Admin dashboard & API
│   ├── manager/           # Manager dashboard & API
│   ├── student/           # Student dashboard & API
│   └── api/               # Shared API endpoints
├── templates/
│   ├── shared/            # Base templates
│   ├── admin/             # Admin templates
│   ├── manager/           # Manager templates
│   ├── student/           # Student templates
│   └── errors/            # Error pages
├── static/
│   ├── css/               # Custom styles
│   └── js/                # JavaScript modules
└── logs/                  # Application logs
```

## API Endpoints

### Analytics (Admin/Manager)
- `GET /api/analytics/revenue?months=12`
- `GET /api/analytics/occupancy`
- `GET /api/analytics/payments`
- `GET /api/analytics/reservations?months=12`
- `GET /api/analytics/tenants`
- `GET /api/analytics/dashboard-summary`

### Student Analytics
- `GET /student/api/analytics/payment-trend?months=6`
- `GET /student/api/analytics/dashboard-summary`

## Deployment

### Production (Gunicorn)

```bash
gunicorn -w 4 -b 0.0.0.0:5000 app:app
```

### With Nginx Reverse Proxy

```nginx
server {
    listen 80;
    server_name dashboard.yourdomain.com;
    
    location / {
        proxy_pass http://127.0.0.1:5000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## Security Features

- Role-based access control (Super Admin, Manager, Student)
- Server-side authorization verification
- CSRF protection on all forms
- Secure session cookies (HttpOnly, SameSite, Secure)
- Parameterized SQL queries (SQL injection prevention)
- Input validation and sanitization
- Security headers (CSP, HSTS, X-Frame-Options)
- Rate limiting on sensitive endpoints
- Audit logging

## Database

Uses the **existing MySQL database** from the PHP application. No new tables created - reads from existing tables:

- `users`, `managers`, `students`
- `rooms`, `room_images`, `amenities`
- `reservations`, `payments`, `receipts`
- `announcements`, `notifications`
- `maintenance_requests`, `complaints`
- `activity_logs`, `audit_logs`

## Timezone

All dates/times use **Asia/Manila** timezone.

## License

Part of the Student Boarding House Management System.