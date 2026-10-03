from flask import render_template, request, jsonify
from blueprints import student_required, get_current_user, get_current_student
from database import (
    get_student_by_user_id, get_student_active_reservation, get_student_payments,
    get_student_payment_summary, get_student_monthly_trend, get_upcoming_payments,
    get_student_receipts, get_recent_announcements, get_user_notifications,
    get_unread_notification_count, get_student_maintenance, get_student_complaints
)
from . import student_bp


@student_bp.route('/')
@student_bp.route('/dashboard')
@student_required
def dashboard():
    user = get_current_user()
    student = get_current_student()
    
    active_reservation = get_student_active_reservation(student['id'])
    pending_payments_count = get_student_payment_summary(student['id']) or {}
    recent_payments = get_student_payments(student['id'], 'paid')[:5]
    overdue_payments_count = get_student_payments(student['id'], 'overdue')
    monthly_trend = get_student_monthly_trend(student['id'])
    upcoming_payments = get_upcoming_payments(student['id'])
    receipts = get_student_receipts(student['id'])
    announcements = get_recent_announcements(5)
    notifications = get_user_notifications(user['id'], 8)
    unread_count = get_unread_notification_count(user['id'])
    maintenance_requests = get_student_maintenance(student['id'], 3)
    complaints = get_student_complaints(student['id'], 3)
    
    payment_summary = get_student_payment_summary(student['id']) or {}
    
    return render_template('student/dashboard.html',
        page_title='My Dashboard',
        user=user,
        student=student,
        active_reservation=active_reservation,
        pending_payments=payment_summary.get('pending_payments', 0),
        total_payments=payment_summary.get('paid_payments', 0),
        total_paid=float(payment_summary.get('total_paid', 0) or 0),
        overdue_payments=len(overdue_payments_count) if isinstance(overdue_payments_count, list) else (overdue_payments_count or 0),
        recent_payments=recent_payments,
        monthly_trend=monthly_trend,
        upcoming_payments=upcoming_payments,
        receipts=receipts,
        announcements=announcements,
        notifications=notifications,
        unread_notifications=unread_count,
        maintenance_requests=maintenance_requests,
        complaints=complaints,
    )


@student_bp.route('/payments')
@student_required
def payments():
    user = get_current_user()
    student = get_current_student()
    
    status = request.args.get('status', '')
    all_payments = get_student_payments(student['id'], status if status else None)
    payment_summary = get_student_payment_summary(student['id']) or {}
    
    return render_template('student/payments.html',
        page_title='My Payments',
        user=user,
        student=student,
        payments=all_payments,
        payment_summary=payment_summary,
        current_status=status,
    )


@student_bp.route('/reservations')
@student_required
def reservations():
    user = get_current_user()
    student = get_current_student()
    
    active_reservation = get_student_active_reservation(student['id'])
    all_reservations = get_student_payments(student['id'])  # Reusing for reservation list
    
    from database import execute_query
    reservations = execute_query("""
        SELECT r.*, rm.room_name, rm.room_number, rm.monthly_rent, rm.room_type, rm.advance_payment,
               rm.floor, rm.size_sqm, rm.has_bathroom, rm.has_balcony, rm.has_aircon,
               rm.description, rm.house_rules, rm.furniture,
               (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.room_id AND ri.is_primary = 1 LIMIT 1) as primary_image
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.id
        WHERE r.student_id = %s
        ORDER BY r.created_at DESC
    """, (student['id'],))
    
    return render_template('student/reservations.html',
        page_title='My Reservations',
        user=user,
        student=student,
        active_reservation=active_reservation,
        reservations=reservations,
    )


@student_bp.route('/receipts')
@student_required
def receipts():
    user = get_current_user()
    student = get_current_student()
    
    receipts = get_student_receipts(student['id'])
    
    return render_template('student/receipts.html',
        page_title='My Receipts',
        user=user,
        student=student,
        receipts=receipts,
    )


@student_bp.route('/announcements')
@student_required
def announcements():
    user = get_current_user()
    student = get_current_student()
    
    announcements = get_recent_announcements(20)
    
    return render_template('student/announcements.html',
        page_title='Announcements',
        user=user,
        student=student,
        announcements=announcements,
    )


@student_bp.route('/maintenance')
@student_required
def maintenance():
    user = get_current_user()
    student = get_current_student()
    
    maintenance_requests = get_student_maintenance(student['id'], 20)
    
    return render_template('student/maintenance.html',
        page_title='Maintenance Requests',
        user=user,
        student=student,
        maintenance_requests=maintenance_requests,
    )


@student_bp.route('/complaints')
@student_required
def complaints():
    user = get_current_user()
    student = get_current_student()
    
    complaints = get_student_complaints(student['id'], 20)
    
    return render_template('student/complaints.html',
        page_title='Complaints',
        user=user,
        student=student,
        complaints=complaints,
    )


@student_bp.route('/notifications')
@student_required
def notifications():
    user = get_current_user()
    student = get_current_student()
    
    notifications = get_user_notifications(user['id'], 50)
    
    return render_template('student/notifications.html',
        page_title='Notifications',
        user=user,
        student=student,
        notifications=notifications,
    )


@student_bp.route('/profile')
@student_required
def profile():
    user = get_current_user()
    student = get_current_student()
    
    return render_template('student/profile.html',
        page_title='My Profile',
        user=user,
        student=student,
    )