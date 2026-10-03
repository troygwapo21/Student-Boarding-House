from flask import jsonify, request
from blueprints import manager_required
from database import (
    get_monthly_revenue, get_monthly_reservations, get_revenue_by_method,
    get_revenue_by_type, get_room_status_distribution, get_room_type_stats,
    get_students_by_gender, get_students_by_year, get_occupancy_rate,
    get_recent_activity
)
from . import manager_bp


@manager_bp.route('/api/analytics/revenue')
@manager_required
def api_revenue():
    months = request.args.get('months', 12, type=int)
    data = get_monthly_revenue(months)
    return jsonify({
        'labels': [row['month_label'] for row in data],
        'data': [float(row['total']) for row in data],
    })


@manager_bp.route('/api/analytics/occupancy')
@manager_required
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


@manager_bp.route('/api/analytics/payments')
@manager_required
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


@manager_bp.route('/api/analytics/reservations')
@manager_required
def api_reservations():
    months = request.args.get('months', 12, type=int)
    data = get_monthly_reservations(months)
    return jsonify({
        'labels': [row['month_label'] for row in data],
        'data': [int(row['total']) for row in data],
    })


@manager_bp.route('/api/analytics/tenants')
@manager_required
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


@manager_bp.route('/api/analytics/dashboard-summary')
@manager_required
def api_dashboard_summary():
    from database import (
        get_room_stats, get_reservation_stats, get_payment_stats,
        get_occupancy_rate, get_tenant_count, get_total_students,
        get_this_month_revenue, get_today_revenue
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
        'pending_reservations': reservation_stats.get('pending_reservations', 0),
        'approved_reservations': reservation_stats.get('approved_reservations', 0),
        'cancelled_reservations': reservation_stats.get('cancelled_reservations', 0),
        'total_revenue': float(payment_stats.get('total_revenue', 0) or 0),
        'this_month_revenue': get_this_month_revenue(),
        'today_revenue': get_today_revenue(),
        'occupancy_rate': get_occupancy_rate(),
        'bed_occupancy_rate': round((room_stats.get('total_occupied_beds', 0) / room_stats.get('total_capacity', 1)) * 100) if room_stats.get('total_capacity', 0) > 0 else 0,
    })