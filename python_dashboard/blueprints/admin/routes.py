from flask import render_template, request, jsonify, g
from blueprints import admin_required, get_current_user
from database import (
    get_room_stats, get_room_status_distribution, get_room_type_stats,
    get_reservation_stats, get_recent_reservations,
    get_payment_stats, get_monthly_revenue, get_revenue_by_method,
    get_revenue_by_type, get_recent_payments,
    get_this_month_revenue, get_last_month_revenue, get_today_revenue,
    get_this_week_revenue, get_last_week_revenue, get_avg_monthly_revenue,
    get_best_month, get_occupancy_rate, get_tenant_count,
    get_total_students, get_total_managers, get_students_by_gender,
    get_students_by_year, get_new_students_this_month,
    get_new_reservations_this_month, get_open_maintenance_count,
    get_open_complaints_count, get_unread_feedback_count,
    get_new_contact_messages_count, get_recent_activity,
    get_monthly_reservations, get_user_notifications, get_unread_notification_count
)
from . import admin_bp


@admin_bp.route('/')
@admin_bp.route('/dashboard')
@admin_required
def dashboard():
    user = get_current_user()
    
    room_stats = get_room_stats() or {}
    room_status = get_room_status_distribution()
    room_type_stats = get_room_type_stats()
    reservation_stats = get_reservation_stats() or {}
    payment_stats = get_payment_stats() or {}
    
    monthly_revenue = get_monthly_revenue(12)
    monthly_reservations = get_monthly_reservations(12)
    revenue_by_method = get_revenue_by_method()
    revenue_by_type = get_revenue_by_type()
    
    recent_reservations = get_recent_reservations(5)
    recent_payments = get_recent_payments(5)
    recent_activity = get_recent_activity(8)
    
    notifications = get_user_notifications(user['id'], 8)
    unread_count = get_unread_notification_count(user['id'])
    
    this_month_revenue = get_this_month_revenue()
    last_month_revenue = get_last_month_revenue()
    today_revenue = get_today_revenue()
    this_week_revenue = get_this_week_revenue()
    last_week_revenue = get_last_week_revenue()
    avg_monthly_revenue = get_avg_monthly_revenue()
    best_month = get_best_month()
    
    occupancy_rate = get_occupancy_rate()
    total_tenants = get_tenant_count()
    total_students = get_total_students()
    total_managers = get_total_managers()
    students_by_gender = get_students_by_gender()
    students_by_year = get_students_by_year()
    new_students_this_month = get_new_students_this_month()
    new_reservations_this_month = get_new_reservations_this_month()
    open_maintenance = get_open_maintenance_count()
    open_complaints = get_open_complaints_count()
    unread_feedback = get_unread_feedback_count()
    new_messages = get_new_contact_messages_count()
    
    total_capacity = room_stats.get('total_capacity', 0) or 0
    total_occupied_beds = room_stats.get('total_occupied_beds', 0) or 0
    bed_occupancy_rate = round((total_occupied_beds / total_capacity * 100)) if total_capacity > 0 else 0
    
    last_month_rev = last_month_revenue or 0
    this_month_rev = this_month_revenue or 0
    if last_month_rev > 0:
        rev_trend = round(((this_month_rev - last_month_rev) / last_month_rev) * 100)
    elif this_month_rev > 0:
        rev_trend = 100
    else:
        rev_trend = 0
    
    rev_by_month = {}
    res_by_month = {}
    chart_labels = []
    for i in range(11, -1, -1):
        from datetime import datetime
        from dateutil.relativedelta import relativedelta
        d = datetime.now().replace(day=1) - relativedelta(months=i)
        k = d.strftime('%Y-%m')
        chart_labels.append(d.strftime('%b'))
        rev_by_month[k] = 0
        res_by_month[k] = 0
    
    for row in monthly_revenue:
        if row['month_key'] in rev_by_month:
            rev_by_month[row['month_key']] = float(row['total'])
    
    for row in monthly_reservations:
        if row['month_key'] in res_by_month:
            res_by_month[row['month_key']] = int(row['total'])
    
    chart_revenue = list(rev_by_month.values())
    chart_reservations = list(res_by_month.values())
    
    type_labels_map = {
        'reservation_fee': 'Reservation Fee',
        'advance_payment': 'Advance Payment',
        'monthly_rent': 'Monthly Rent',
        'electric_bill': 'Electric Bill',
        'water_bill': 'Water Bill',
        'other': 'Other',
    }
    type_colors_map = {
        'reservation_fee': '#8b5cf6',
        'advance_payment': '#0ea5e9',
        'monthly_rent': '#6366f1',
        'electric_bill': '#f59e0b',
        'water_bill': '#10b981',
        'other': '#94a3b8',
    }
    rev_type_labels = []
    rev_type_data = []
    rev_type_colors = []
    for rt in revenue_by_type:
        rev_type_labels.append(type_labels_map.get(rt['payment_type'], rt['payment_type'].replace('_', ' ').title()))
        rev_type_colors.append(type_colors_map.get(rt['payment_type'], '#94a3b8'))
        rev_type_data.append(float(rt['total']))
    
    method_map = {'cash': ['Cash', '#10b981'], 'gcash': ['GCash', '#6366f1']}
    method_labels = []
    method_data = []
    method_colors = []
    for rm in revenue_by_method:
        method_labels.append(method_map.get(rm['method'], [rm['method'].title(), '#94a3b8'])[0])
        method_colors.append(method_map.get(rm['method'], ['', '#94a3b8'])[1])
        method_data.append(float(rm['total']))
    
    gender_colors = {'male': '#0ea5e9', 'female': '#ec4899', 'other': '#94a3b8'}
    gender_labels = []
    gender_data = []
    gender_colors_arr = []
    for g in students_by_gender:
        gender_labels.append(g['gender'].title())
        gender_data.append(int(g['count']))
        gender_colors_arr.append(gender_colors.get(g['gender'], '#94a3b8'))
    
    room_type_meta = {
        'bedspacer': {'label': 'Bedspacer', 'icon': 'fa-bed', 'color': '#0ea5e9'},
        'single': {'label': 'Single', 'icon': 'fa-user', 'color': '#8b5cf6'},
        'studio': {'label': 'Studio', 'icon': 'fa-building', 'color': '#f59e0b'},
    }
    room_type_rows = []
    for rts in room_type_stats:
        meta = room_type_meta.get(rts['room_type'], {'label': rts['room_type'].title(), 'icon': 'fa-door-open', 'color': '#94a3b8'})
        cap = int(rts['capacity'] or 0)
        room_type_rows.append({
            'label': meta['label'],
            'icon': meta['icon'],
            'color': meta['color'],
            'rooms_total': int(rts['total_rooms']),
            'occupied': int(rts['occupied_rooms']),
            'beds': int(rts['occupied_beds'] or 0),
            'cap': cap,
            'pct': round((int(rts['occupied_beds'] or 0) / cap) * 100) if cap > 0 else 0,
        })
    
    best_month_label = best_month['label'] if best_month else 'N/A'
    best_month_total = float(best_month['total']) if best_month else 0
    pay_type_total = sum(rev_type_data)
    method_total = sum(method_data)
    needs_attention = open_maintenance + open_complaints + unread_feedback + new_messages
    
    room_counts = {'available': 0, 'occupied': 0, 'reserved': 0, 'under_maintenance': 0}
    for rs in room_status:
        if rs['status'] in room_counts:
            room_counts[rs['status']] = int(rs['count'])
    
    base = max(1, room_stats.get('total_rooms', 1))
    room_pcts = {k: round((v / base) * 100) for k, v in room_counts.items()}
    
    return render_template('admin/dashboard.html',
        page_title='Admin Dashboard',
        user=user,
        total_rooms=room_stats.get('total_rooms', 0),
        available_rooms=room_counts['available'],
        occupied_rooms=room_counts['occupied'],
        reserved_rooms=room_counts['reserved'],
        maintenance_rooms=room_counts['under_maintenance'],
        fully_occupied_rooms=room_stats.get('full_rooms', 0),
        total_students=total_students,
        total_managers=total_managers,
        pending_reservations=reservation_stats.get('pending_reservations', 0),
        total_revenue=float(payment_stats.get('total_revenue', 0) or 0),
        this_month_revenue=this_month_rev,
        last_month_revenue=last_month_rev,
        today_revenue=today_revenue,
        this_week_revenue=this_week_revenue,
        last_week_revenue=last_week_revenue,
        avg_monthly_revenue=avg_monthly_revenue,
        best_month={'label': best_month_label, 'total': best_month_total},
        total_capacity=total_capacity,
        total_occupied_beds=total_occupied_beds,
        bed_occupancy_rate=bed_occupancy_rate,
        occupancy_rate=occupancy_rate,
        active_tenants=total_tenants,
        revenue_by_method=revenue_by_method,
        revenue_by_type=revenue_by_type,
        room_type_stats=room_type_stats,
        students_by_gender=students_by_gender,
        students_by_year=students_by_year,
        new_students_this_month=new_students_this_month,
        new_reservations_this_month=new_reservations_this_month,
        walkin_collections_this_month=0,
        pending_payments=payment_stats.get('pending_payments', 0),
        open_maintenance=open_maintenance,
        open_complaints=open_complaints,
        unread_feedback=unread_feedback,
        new_messages=new_messages,
        rev_trend=rev_trend,
        chart_labels=chart_labels,
        chart_revenue=chart_revenue,
        chart_reservations=chart_reservations,
        rev_type_labels=rev_type_labels,
        rev_type_data=rev_type_data,
        rev_type_colors=rev_type_colors,
        method_labels=method_labels,
        method_data=method_data,
        method_colors=method_colors,
        gender_labels=gender_labels,
        gender_data=gender_data,
        gender_colors_arr=gender_colors_arr,
        room_type_rows=room_type_rows,
        best_month_label=best_month_label,
        best_month_total=best_month_total,
        pay_type_total=pay_type_total,
        method_total=method_total,
        needs_attention=needs_attention,
        room_counts=room_counts,
        room_pcts=room_pcts,
        room_status=room_status,
        monthly_revenue=monthly_revenue,
        monthly_reservations=monthly_reservations,
        recent_activity=recent_activity,
        recent_reservations=recent_reservations,
        recent_payments=recent_payments,
        notifications=notifications,
        unread_notifications=unread_count,
    )


@admin_bp.route('/analytics')
@admin_required
def analytics():
    user = get_current_user()
    return render_template('admin/analytics.html',
        page_title='Analytics',
        user=user,
    )


@admin_bp.route('/reports')
@admin_required
def reports():
    user = get_current_user()
    return render_template('admin/reports.html',
        page_title='Reports',
        user=user,
    )


@admin_bp.route('/settings')
@admin_required
def settings():
    user = get_current_user()
    return render_template('admin/settings.html',
        page_title='Settings',
        user=user,
    )


@admin_bp.route('/profile')
@admin_required
def profile():
    user = get_current_user()
    return render_template('admin/profile.html',
        page_title='Profile',
        user=user,
    )