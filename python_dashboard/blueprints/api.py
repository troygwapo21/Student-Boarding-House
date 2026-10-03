from flask import Blueprint, jsonify, request
from blueprints import login_required, role_required
from database import execute_query, execute_one

api_bp = Blueprint('api', __name__)


@api_bp.route('/analytics/revenue')
@login_required
@role_required('super_admin', 'manager')
def api_revenue():
    months = request.args.get('months', 12, type=int)
    data = execute_query("""
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
    return jsonify({
        'labels': [row['month_label'] for row in data],
        'data': [float(row['total']) for row in data],
    })


@api_bp.route('/analytics/occupancy')
@login_required
@role_required('super_admin', 'manager')
def api_occupancy():
    room_status = execute_query("""
        SELECT status, COUNT(*) as count
        FROM rooms
        GROUP BY status
    """)
    occupancy_rate = execute_one("""
        SELECT 
            SUM(current_occupancy) * 100.0 / NULLIF(SUM(max_capacity), 0) as rate
        FROM rooms
    """)
    
    labels = [row['status'].replace('_', ' ').title() for row in room_status]
    data = [int(row['count']) for row in room_status]
    
    return jsonify({
        'labels': labels,
        'data': data,
        'occupancy_rate': round(float(occupancy_rate['rate'] or 0), 1) if occupancy_rate else 0,
    })


@api_bp.route('/analytics/payments')
@login_required
@role_required('super_admin', 'manager')
def api_payments():
    by_method = execute_query("""
        SELECT 
            COALESCE(NULLIF(payment_method, ''), 'cash') as method,
            SUM(amount_paid - COALESCE(refunded_amount, 0)) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
        GROUP BY COALESCE(NULLIF(payment_method, ''), 'cash')
    """)
    
    by_type = execute_query("""
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
    
    return jsonify({
        'by_method': {
            'labels': [row['method'].title() for row in by_method],
            'data': [float(row['total']) for row in by_method],
        },
        'by_type': {
            'labels': [row['payment_type'].replace('_', ' ').title() for row in by_type],
            'data': [float(row['total']) for row in by_type],
        },
    })


@api_bp.route('/analytics/reservations')
@login_required
@role_required('super_admin', 'manager')
def api_reservations():
    months = request.args.get('months', 12, type=int)
    data = execute_query("""
        SELECT 
            DATE_FORMAT(created_at, '%%Y-%%m') as month_key,
            DATE_FORMAT(created_at, '%%b') as month_label,
            COUNT(*) as total
        FROM reservations
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %s MONTH)
        GROUP BY month_key, month_label
        ORDER BY month_key ASC
    """, (months,))
    return jsonify({
        'labels': [row['month_label'] for row in data],
        'data': [int(row['total']) for row in data],
    })


@api_bp.route('/analytics/tenants')
@login_required
@role_required('super_admin', 'manager')
def api_tenants():
    by_gender = execute_query("""
        SELECT COALESCE(NULLIF(gender, ''), 'other') as gender, COUNT(*) as count
        FROM students
        GROUP BY COALESCE(NULLIF(gender, ''), 'other')
    """)
    
    by_year = execute_query("""
        SELECT COALESCE(NULLIF(year_level, ''), 'N/A') as year_level, COUNT(*) as count
        FROM students
        GROUP BY COALESCE(NULLIF(year_level, ''), 'N/A')
        ORDER BY count DESC
        LIMIT 6
    """)
    
    return jsonify({
        'by_gender': {
            'labels': [row['gender'].title() for row in by_gender],
            'data': [int(row['count']) for row in by_gender],
        },
        'by_year': {
            'labels': [row['year_level'] for row in by_year],
            'data': [int(row['count']) for row in by_year],
        },
    })


@api_bp.route('/analytics/dashboard-summary')
@login_required
def api_dashboard_summary():
    user_role = request.environ.get('user_role', '')
    
    room_stats = execute_one("""
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
    
    reservation_stats = execute_one("""
        SELECT 
            COUNT(*) as total_reservations,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reservations,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_reservations,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_reservations
        FROM reservations
    """)
    
    payment_stats = execute_one("""
        SELECT 
            COUNT(*) as total_payments,
            SUM(CASE WHEN status IN ('paid', 'partially_paid', 'refunded') AND amount_paid > 0 
                 THEN amount_paid - COALESCE(refunded_amount, 0) ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
            SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_payments
        FROM payments
    """)
    
    this_month_revenue = execute_one("""
        SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
          AND MONTH(COALESCE(paid_at, created_at)) = MONTH(CURDATE())
          AND YEAR(COALESCE(paid_at, created_at)) = YEAR(CURDATE())
    """)
    
    today_revenue = execute_one("""
        SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total
        FROM payments
        WHERE status IN ('paid', 'partially_paid', 'refunded')
          AND amount_paid > 0
          AND DATE(COALESCE(paid_at, created_at)) = CURDATE()
    """)
    
    occupancy_rate = 0
    if room_stats and room_stats['total_capacity']:
        occupancy_rate = round((room_stats['total_occupied_beds'] / room_stats['total_capacity']) * 100)
    
    tenant_count = execute_one("""
        SELECT COUNT(DISTINCT student_id) as count
        FROM reservations
        WHERE status = 'approved'
    """)
    
    return jsonify({
        'total_rooms': room_stats['total_rooms'] if room_stats else 0,
        'available_rooms': room_stats['available_rooms'] if room_stats else 0,
        'occupied_rooms': room_stats['occupied_rooms'] if room_stats else 0,
        'reserved_rooms': room_stats['reserved_rooms'] if room_stats else 0,
        'maintenance_rooms': room_stats['maintenance_rooms'] if room_stats else 0,
        'full_rooms': room_stats['full_rooms'] if room_stats else 0,
        'total_tenants': tenant_count['count'] if tenant_count else 0,
        'pending_reservations': reservation_stats['pending_reservations'] if reservation_stats else 0,
        'approved_reservations': reservation_stats['approved_reservations'] if reservation_stats else 0,
        'cancelled_reservations': reservation_stats['cancelled_reservations'] if reservation_stats else 0,
        'total_revenue': float(payment_stats['total_revenue'] or 0) if payment_stats else 0,
        'this_month_revenue': float(this_month_revenue['total'] or 0) if this_month_revenue else 0,
        'today_revenue': float(today_revenue['total'] or 0) if today_revenue else 0,
        'occupancy_rate': occupancy_rate,
        'bed_occupancy_rate': occupancy_rate,
    })