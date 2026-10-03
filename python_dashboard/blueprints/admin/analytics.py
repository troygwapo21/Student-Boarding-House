from flask import jsonify, request
from blueprints import admin_required
from database import (
    get_monthly_revenue, get_monthly_reservations, get_revenue_by_method,
    get_revenue_by_type, get_room_status_distribution, get_room_type_stats,
    get_students_by_gender, get_students_by_year, get_occupancy_rate,
    get_recent_activity
)
from . import admin_bp


@admin_bp.route('/api/analytics/revenue')
@admin_required
def api_revenue():
    months = request.args.get('months', 12, type=int)
    data = get_monthly_revenue(months)
    return jsonify({
        'labels': [row['month_label'] for row in data],
        'data': [float(row['total']) for row in data],
    })


@admin_bp.route('/api/analytics/occupancy')
@admin_required
def api_occupancy():
    room_status = get_room_status_distribution()
    room_type_stats = get_room_type_stats()
    occupancy_rate = get_occupancy_rate()
    
    labels = [row['status'].replace('_', ' ').title() for row in room_status]
    data = [int(row['count']) for row in room_status]
    
    room_type_labels = [row['room_type'].title() for row in room_type_stats]
    room_type_occupied = [int(row['occupied_rooms']) for row in room_type_stats]
    room_type_total = [int(row['total_rooms']) for row in room_type_stats]
    room_type_beds = [int(row['occupied_beds'] or 0) for row in room_type_stats]
    room_type_capacity = [int(row['capacity']) for row in room_type_stats]
    
    return jsonify({
        'room_status': {
            'labels': labels,
            'data': data,
        },
        'room_type': {
            'labels': room_type_labels,
            'occupied': room_type_occupied,
            'total': room_type_total,
            'beds': room_type_beds,
            'capacity': room_type_capacity,
        },
        'occupancy_rate': occupancy_rate,
    })


@admin_bp.route('/api/analytics/payments')
@admin_required
def api_payments():
    revenue_by_method = get_revenue_by_method()
    revenue_by_type = get_revenue_by_type()
    
    method_labels = [row['method'].title() for row in revenue_by_method]
    method_data = [float(row['total']) for row in revenue_by_method]
    
    type_labels = [row['payment_type'].replace('_', ' ').title() for row in revenue_by_type]
    type_data = [float(row['total']) for row in revenue_by_type]
    
    return jsonify({
        'by_method': {
            'labels': method_labels,
            'data': method_data,
        },
        'by_type': {
            'labels': type_labels,
            'data': type_data,
        },
    })


@admin_bp.route('/api/analytics/reservations')
@admin_required
def api_reservations():
    months = request.args.get('months', 12, type=int)
    data = get_monthly_reservations(months)
    return jsonify({
        'labels': [row['month_label'] for row in data],
        'data': [int(row['total']) for row in data],
    })


@admin_bp.route('/api/analytics/tenants')
@admin_required
def api_tenants():
    students_by_gender = get_students_by_gender()
    students_by_year = get_students_by_year()
    
    gender_labels = [row['gender'].title() for row in students_by_gender]
    gender_data = [int(row['count']) for row in students_by_gender]
    
    year_labels = [row['year_level'] for row in students_by_year]
    year_data = [int(row['count']) for row in students_by_year]
    
    return jsonify({
        'by_gender': {
            'labels': gender_labels,
            'data': gender_data,
        },
        'by_year': {
            'labels': year_labels,
            'data': year_data,
        },
    })


@admin_bp.route('/api/analytics/boarding-house-performance')
@admin_required
def api_boarding_house_performance():
    room_type_stats = get_room_type_stats()
    
    performance = []
    for row in room_type_stats:
        cap = int(row['capacity'] or 0)
        occupied_beds = int(row['occupied_beds'] or 0)
        occupancy_pct = round((occupied_beds / cap) * 100) if cap > 0 else 0
        
        revenue = 0
        
        performance.append({
            'room_type': row['room_type'].title(),
            'total_rooms': int(row['total_rooms']),
            'occupied_rooms': int(row['occupied_rooms']),
            'occupancy_rate': occupancy_pct,
            'capacity': cap,
            'occupied_beds': occupied_beds,
            'revenue': revenue,
        })
    
    return jsonify({'data': performance})


@admin_bp.route('/api/analytics/payment-method-stats')
@admin_required
def api_payment_method_stats():
    revenue_by_method = get_revenue_by_method()
    
    labels = [row['method'].title() for row in revenue_by_method]
    data = [float(row['total']) for row in revenue_by_method]
    total = sum(data)
    
    return jsonify({
        'labels': labels,
        'data': data,
        'percentages': [round((d / total * 100) if total > 0 else 0, 1) for d in data],
    })


@admin_bp.route('/api/analytics/outstanding-payments')
@admin_required
def api_outstanding_payments():
    from database import execute_query
    
    overdue = execute_query("""
        SELECT COUNT(*) as count, COALESCE(SUM(amount - amount_paid), 0) as total
        FROM payments
        WHERE status = 'overdue'
    """)
    
    pending = execute_query("""
        SELECT COUNT(*) as count, COALESCE(SUM(amount - amount_paid), 0) as total
        FROM payments
        WHERE status = 'pending'
    """)
    
    partially_paid = execute_query("""
        SELECT COUNT(*) as count, COALESCE(SUM(amount - amount_paid), 0) as total
        FROM payments
        WHERE status = 'partially_paid'
    """)
    
    return jsonify({
        'overdue': {'count': overdue[0]['count'], 'total': float(overdue[0]['total'])} if overdue else {'count': 0, 'total': 0},
        'pending': {'count': pending[0]['count'], 'total': float(pending[0]['total'])} if pending else {'count': 0, 'total': 0},
        'partially_paid': {'count': partially_paid[0]['count'], 'total': float(partially_paid[0]['total'])} if partially_paid else {'count': 0, 'total': 0},
    })


@admin_bp.route('/api/analytics/room-status-distribution')
@admin_required
def api_room_status_distribution():
    room_status = get_room_status_distribution()
    
    labels = [row['status'].replace('_', ' ').title() for row in room_status]
    data = [int(row['count']) for row in room_status]
    total = sum(data)
    
    return jsonify({
        'labels': labels,
        'data': data,
        'percentages': [round((d / total * 100) if total > 0 else 0, 1) for d in data],
    })


@admin_bp.route('/api/analytics/dashboard-summary')
@admin_required
def api_dashboard_summary():
    from database import (
        get_room_stats, get_reservation_stats, get_payment_stats,
        get_occupancy_rate, get_tenant_count, get_total_students,
        get_total_managers, get_this_month_revenue, get_today_revenue
    )
    
    room_stats = get_room_stats() or {}
    reservation_stats = get_reservation_stats() or {}
    payment_stats = get_payment_stats() or {}
    
    return jsonify({
        'total_rooms': room_stats.get('total_rooms', 0),
        'available_rooms': room_stats.get('available_rooms', 0),
        'occupied_rooms': room_stats.get('occupied_rooms', 0),
        'reserved_rooms': room_stats.get('reserved_rooms', 0),
        'maintenance_rooms': room_stats.get('maintenance_rooms', 0),
        'full_rooms': room_stats.get('full_rooms', 0),
        'total_tenants': get_tenant_count(),
        'total_students': get_total_students(),
        'total_managers': get_total_managers(),
        'pending_reservations': reservation_stats.get('pending_reservations', 0),
        'approved_reservations': reservation_stats.get('approved_reservations', 0),
        'cancelled_reservations': reservation_stats.get('cancelled_reservations', 0),
        'total_revenue': float(payment_stats.get('total_revenue', 0) or 0),
        'this_month_revenue': get_this_month_revenue(),
        'today_revenue': get_today_revenue(),
        'occupancy_rate': get_occupancy_rate(),
        'bed_occupancy_rate': round((room_stats.get('total_occupied_beds', 0) / room_stats.get('total_capacity', 1)) * 100) if room_stats.get('total_capacity', 0) > 0 else 0,
    })