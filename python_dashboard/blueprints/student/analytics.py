from flask import jsonify, request
from blueprints import student_required, get_current_student
from database import get_student_monthly_trend
from . import student_bp


@student_bp.route('/api/analytics/payment-trend')
@student_required
def api_payment_trend():
    student = get_current_student()
    months = request.args.get('months', 6, type=int)
    data = get_student_monthly_trend(student['id'], months)
    
    months_map = {}
    for row in data:
        key = row['month_key']
        if key not in months_map:
            months_map[key] = {'label': row['month_label'], 'paid': 0, 'pending': 0, 'overdue': 0}
        months_map[key][row['status']] = float(row['total'])
    
    sorted_months = sorted(months_map.keys())
    
    return jsonify({
        'labels': [months_map[k]['label'] for k in sorted_months],
        'paid': [months_map[k]['paid'] for k in sorted_months],
        'pending': [months_map[k]['pending'] for k in sorted_months],
        'overdue': [months_map[k]['overdue'] for k in sorted_months],
    })


@student_bp.route('/api/analytics/dashboard-summary')
@student_required
def api_dashboard_summary():
    from database import get_student_payment_summary, get_student_active_reservation
    student = get_current_student()
    
    payment_summary = get_student_payment_summary(student['id']) or {}
    active_reservation = get_student_active_reservation(student['id'])
    
    return jsonify({
        'pending_payments': payment_summary.get('pending_payments', 0),
        'overdue_payments': payment_summary.get('overdue_payments', 0),
        'total_paid': float(payment_summary.get('total_paid', 0) or 0),
        'has_active_reservation': active_reservation is not None,
        'room_number': active_reservation['room_number'] if active_reservation else None,
        'room_name': active_reservation['room_name'] if active_reservation else None,
        'monthly_rent': float(active_reservation['monthly_rent']) if active_reservation else 0,
    })