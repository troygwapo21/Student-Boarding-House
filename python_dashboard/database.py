from app import execute_query, execute_one
from datetime import datetime, date
import logging

logger = logging.getLogger(__name__)

def init_database():
    pass


# User & Authentication
def get_user_by_id(user_id):
    return execute_one(
        "SELECT * FROM users WHERE id = %s", (user_id,)
    )

def get_user_by_email(email):
    return execute_one(
        "SELECT * FROM users WHERE email = %s", (email.lower().strip(),)
    )

def verify_user_session(user_id):
    user = get_user_by_id(user_id)
    if not user:
        return None
    if user['status'] not in ('active',):
        return None
    return user


# Manager queries
def get_manager_by_user_id(user_id):
    return execute_one(
        "SELECT m.*, u.email, u.role FROM managers m JOIN users u ON m.user_id = u.id WHERE m.user_id = %s",
        (user_id,)
    )

def get_manager_boarding_house_id(manager_id):
    return execute_one(
        "SELECT id FROM managers WHERE id = %s", (manager_id,)
    )


# Student queries
def get_student_by_user_id(user_id):
    return execute_one(
        "SELECT s.*, u.email, u.role FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = %s",
        (user_id,)
    )

def get_student_by_id(student_id):
    return execute_one(
        "SELECT s.*, u.email, u.role FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = %s",
        (student_id,)
    )


# Room queries
def get_room_stats():
    return execute_one("""
        SELECT 
            COUNT(*) as total_rooms,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_rooms,
            SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_rooms,
            SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved_rooms,
            SUM(CASE WHEN status = 'under_maintenance' THEN 1 ELSE 0 END) as maintenance_rooms,
            SUM(CASE WHEN status = 'occupied' AND current_occupancy >= max_capacity THEN 1 ELSE 0 END) as full_rooms,
            SUM(max_capacity) as total_capacity,
            SUM(current_occupancy) as total_occupied_beds
        FROM rooms
    """)


def get_room_status_distribution():
    return execute_query("""
        SELECT status, COUNT(*) as count
        FROM rooms
        GROUP BY status
    """)


def get_room_type_stats():
    return execute_query("""
        SELECT 
            room_type,
            COUNT(*) as total_rooms,
            SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_rooms,
            SUM(current_occupancy) as occupied_beds,
            SUM(max_capacity) as capacity
        FROM rooms
        GROUP BY room_type
    """)


# Reservation queries
def get_reservation_stats():
    return execute_one("""
        SELECT 
            COUNT(*) as total_reservations,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reservations,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_reservations,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_reservations,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_reservations,
            SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_reservations
        FROM reservations
    """)


def get_recent_reservations(limit=5):
    return execute_query("""
        SELECT r.*, s.first_name, s.middle_name, s.last_name, s.suffix,
               rm.room_name, rm.room_number
        FROM reservations r
        JOIN students s ON r.student_id = s.id
        JOIN rooms rm ON r.room_id = rm.id
        ORDER BY r.created_at DESC
        LIMIT %s
    """, (limit,))


# Payment queries
def get_payment_stats():
    return execute_one("""
        SELECT 
            COUNT(*) as total_payments,
            SUM(CASE WHEN status IN ('paid', 'partially_paid', 'refunded') AND amount_paid > 0 
                 THEN amount_paid - COALESCE(refunded_amount, 0) ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
            SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_payments,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_payments
        FROM payments
    """)


def get_monthly_revenue(months=12):
    return execute_query("""
        SELECT 
            DATE_FORMAT(COALESCE(paid_at, created_at), '%%Y-%%m') as month_key,
            DATE_FORMAT(COALESCE(paid_at, created_at), '%%b') as month_label,
            SUM(amount_paid - COALESCE(refunded_amount, 0)) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
          AND COALESCE(paid_at, created_at) >= DATE_SUB(CURDATE(), INTERVAL %s MONTH)
        GROUP BY month_key, month_label
        ORDER BY month_key ASC
    """, (months,))


def get_revenue_by_method():
    return execute_query("""
        SELECT 
            COALESCE(NULLIF(payment_method, ''), 'cash') as method,
            SUM(amount_paid - COALESCE(refunded_amount, 0)) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
        GROUP BY COALESCE(NULLIF(payment_method, ''), 'cash')
    """)


def get_revenue_by_type(months=1):
    return execute_query("""
        SELECT 
            payment_type,
            SUM(amount_paid - COALESCE(refunded_amount, 0)) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
          AND MONTH(COALESCE(paid_at, created_at)) = MONTH(CURDATE())
          AND YEAR(COALESCE(paid_at, created_at)) = YEAR(CURDATE())
        GROUP BY payment_type
    """)


def get_recent_payments(limit=5):
    return execute_query("""
        SELECT p.*, s.first_name, s.middle_name, s.last_name, s.suffix
        FROM payments p
        JOIN students s ON p.student_id = s.id
        ORDER BY p.created_at DESC
        LIMIT %s
    """, (limit,))


def get_this_month_revenue():
    result = execute_one("""
        SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
          AND MONTH(COALESCE(paid_at, created_at)) = MONTH(CURDATE())
          AND YEAR(COALESCE(paid_at, created_at)) = YEAR(CURDATE())
    """)
    return float(result['total']) if result else 0.0


def get_last_month_revenue():
    result = execute_one("""
        SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
          AND MONTH(COALESCE(paid_at, created_at)) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
          AND YEAR(COALESCE(paid_at, created_at)) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    """)
    return float(result['total']) if result else 0.0


def get_today_revenue():
    result = execute_one("""
        SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
          AND DATE(COALESCE(paid_at, created_at)) = CURDATE()
    """)
    return float(result['total']) if result else 0.0


def get_this_week_revenue():
    result = execute_one("""
        SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
          AND YEARWEEK(COALESCE(paid_at, created_at), 1) = YEARWEEK(CURDATE(), 1)
    """)
    return float(result['total']) if result else 0.0


def get_last_week_revenue():
    result = execute_one("""
        SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
          AND YEARWEEK(COALESCE(paid_at, created_at), 1) = YEARWEEK(CURDATE(), 1) - 1
    """)
    return float(result['total']) if result else 0.0


def get_avg_monthly_revenue():
    result = execute_one("""
        SELECT COALESCE(AVG(m.total), 0) as avg_total
        FROM (
            SELECT DATE_FORMAT(COALESCE(paid_at, created_at), '%%Y-%%m') as ym,
                   SUM(amount_paid - COALESCE(refunded_amount, 0)) as total
            FROM payments
            WHERE status IN ('paid', 'partially_paid', 'refunded')
              AND amount_paid > 0
            GROUP BY ym
        ) m
    """)
    return float(result['avg_total']) if result else 0.0


def get_best_month():
    return execute_one("""
        SELECT DATE_FORMAT(COALESCE(paid_at, created_at), '%%M %%Y') as label,
               SUM(amount_paid - COALESCE(refunded_amount, 0)) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
        GROUP BY DATE_FORMAT(COALESCE(paid_at, created_at), '%%Y-%%m')
        ORDER BY total DESC
        LIMIT 1
    """)


# Student/Tenant queries
def get_student_active_reservation(student_id):
    return execute_one("""
        SELECT r.*, rm.room_name, rm.room_number, rm.monthly_rent,
               (SELECT ri.image_path FROM room_images ri 
                WHERE ri.room_id = r.room_id AND ri.is_primary = 1 LIMIT 1) as primary_image
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.id
        WHERE r.student_id = %s AND r.status = 'approved'
        ORDER BY r.created_at DESC
        LIMIT 1
    """, (student_id,))


def get_student_payments(student_id, status=None):
    where = "WHERE p.student_id = %s"
    params = [student_id]
    if status:
        where += " AND p.status = %s"
        params.append(status)
    return execute_query(f"""
        SELECT p.*, r.reservation_code, rm.room_name, rm.room_number
        FROM payments p
        LEFT JOIN reservations r ON p.reservation_id = r.id
        LEFT JOIN rooms rm ON r.room_id = rm.id
        {where}
        ORDER BY p.created_at DESC
    """, tuple(params))


def get_student_payment_summary(student_id):
    return execute_one("""
        SELECT 
            COUNT(*) as total_payments,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
            SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_payments,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_payments,
            COALESCE(SUM(CASE WHEN status IN ('paid', 'partially_paid', 'refunded') AND amount_paid > 0
                        THEN amount_paid - COALESCE(refunded_amount, 0) ELSE 0 END), 0) as total_paid
        FROM payments
        WHERE student_id = %s
    """, (student_id,))


def get_student_monthly_trend(student_id, months=6):
    return execute_query("""
        SELECT 
            DATE_FORMAT(created_at, '%%Y-%%m') as month_key,
            DATE_FORMAT(created_at, '%%b') as month_label,
            SUM(amount) as total,
            status
        FROM payments
        WHERE student_id = %s
          AND created_at >= DATE_SUB(CURDATE(), INTERVAL %s MONTH)
        GROUP BY month_key, month_label, status
        ORDER BY month_key ASC
    """, (student_id, months))


def get_upcoming_payments(student_id, limit=5):
    return execute_query("""
        SELECT p.*, rm.room_number
        FROM payments p
        LEFT JOIN reservations r ON p.reservation_id = r.id
        LEFT JOIN rooms rm ON r.room_id = rm.id
        WHERE p.student_id = %s
          AND p.status = 'pending'
          AND p.due_date IS NOT NULL
        ORDER BY p.due_date ASC
        LIMIT %s
    """, (student_id, limit))


# Analytics queries for API endpoints
def get_occupancy_rate():
    room_stats = get_room_stats()
    if not room_stats or not room_stats['total_capacity']:
        return 0
    return round((room_stats['total_occupied_beds'] / room_stats['total_capacity']) * 100)


def get_tenant_count():
    result = execute_one("""
        SELECT COUNT(DISTINCT student_id) as count
        FROM reservations
        WHERE status = 'approved'
    """)
    return result['count'] if result else 0


def get_total_students():
    result = execute_one("SELECT COUNT(*) as count FROM students")
    return result['count'] if result else 0


def get_total_managers():
    result = execute_one("SELECT COUNT(*) as count FROM managers")
    return result['count'] if result else 0


def get_students_by_gender():
    return execute_query("""
        SELECT COALESCE(NULLIF(gender, ''), 'other') as gender, COUNT(*) as count
        FROM students
        GROUP BY COALESCE(NULLIF(gender, ''), 'other')
    """)


def get_students_by_year():
    return execute_query("""
        SELECT COALESCE(NULLIF(year_level, ''), 'N/A') as year_level, COUNT(*) as count
        FROM students
        GROUP BY COALESCE(NULLIF(year_level, ''), 'N/A')
        ORDER BY count DESC
        LIMIT 6
    """)


def get_new_students_this_month():
    result = execute_one("""
        SELECT COUNT(*) as count
        FROM students
        WHERE created_at >= DATE_FORMAT(CURDATE(), '%%Y-%%m-01')
    """)
    return result['count'] if result else 0


def get_new_reservations_this_month():
    result = execute_one("""
        SELECT COUNT(*) as count
        FROM reservations
        WHERE created_at >= DATE_FORMAT(CURDATE(), '%%Y-%%m-01')
    """)
    return result['count'] if result else 0


def get_open_maintenance_count():
    result = execute_one("""
        SELECT COUNT(*) as count
        FROM maintenance_requests
        WHERE status IN ('pending', 'in_progress')
    """)
    return result['count'] if result else 0


def get_open_complaints_count():
    result = execute_one("""
        SELECT COUNT(*) as count
        FROM complaints
        WHERE status IN ('open', 'under_review')
    """)
    return result['count'] if result else 0


def get_unread_feedback_count():
    result = execute_one("""
        SELECT COUNT(*) as count
        FROM feedback
        WHERE status = 'new'
    """)
    return result['count'] if result else 0


def get_new_contact_messages_count():
    result = execute_one("""
        SELECT COUNT(*) as count
        FROM contact_messages
        WHERE status = 'new'
    """)
    return result['count'] if result else 0


def get_recent_activity(limit=8):
    return execute_query("""
        SELECT al.*, u.email
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT %s
    """, (limit,))


def get_monthly_reservations(months=12):
    return execute_query("""
        SELECT 
            DATE_FORMAT(created_at, '%%Y-%%m') as month_key,
            DATE_FORMAT(created_at, '%%b') as month_label,
            COUNT(*) as total
        FROM reservations
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %s MONTH)
        GROUP BY month_key, month_label
        ORDER BY month_key ASC
    """, (months,))


# Manager-specific queries (filtered by boarding house)
# Note: In this system, managers don't have explicit boarding_house_id
# They manage all rooms. If needed, we can add boarding_house_id to managers table later.
# For now, manager sees all data (same as admin but different permissions)
def get_manager_room_stats():
    return get_room_stats()


def get_manager_reservation_stats():
    return get_reservation_stats()


def get_manager_payment_stats():
    return get_payment_stats()


# Announcements
def get_recent_announcements(limit=5):
    return execute_query("""
        SELECT * FROM announcements
        WHERE is_published = 1
        ORDER BY created_at DESC
        LIMIT %s
    """, (limit,))


# Notifications
def get_user_notifications(user_id, limit=8):
    return execute_query("""
        SELECT * FROM notifications
        WHERE user_id = %s
        ORDER BY created_at DESC
        LIMIT %s
    """, (user_id, limit))


def get_unread_notification_count(user_id):
    result = execute_one("""
        SELECT COUNT(*) as count
        FROM notifications
        WHERE user_id = %s AND is_read = 0
    """, (user_id,))
    return result['count'] if result else 0


# Maintenance & Complaints for student
def get_student_maintenance(student_id, limit=3):
    return execute_query("""
        SELECT * FROM maintenance_requests
        WHERE student_id = %s
        ORDER BY created_at DESC
        LIMIT %s
    """, (student_id, limit))


def get_student_complaints(student_id, limit=3):
    return execute_query("""
        SELECT * FROM complaints
        WHERE student_id = %s
        ORDER BY created_at DESC
        LIMIT %s
    """, (student_id, limit))


def get_student_receipts(student_id):
    return execute_query("""
        SELECT rc.*, p.payment_code, p.payment_type, p.amount, p.late_fee, p.amount_paid,
               p.payment_method, p.paid_at, p.billing_period, rm.room_name, rm.room_number
        FROM receipts rc
        JOIN payments p ON rc.payment_id = p.id
        LEFT JOIN reservations r ON p.reservation_id = r.id
        LEFT JOIN rooms rm ON r.room_id = rm.id
        WHERE p.student_id = %s
        ORDER BY rc.issued_date DESC, rc.id DESC
    """, (student_id,))