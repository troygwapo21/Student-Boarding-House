from flask import render_template, request, jsonify, send_file, make_response
from blueprints import admin_required
from database import execute_query, execute_one
from datetime import datetime, date
from dateutil.relativedelta import relativedelta
import csv
import io
import json

try:
    from reportlab.lib.pagesizes import letter, A4
    from reportlab.lib import colors
    from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
    from reportlab.lib.units import inch
    from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer, PageBreak
    REPORTLAB_AVAILABLE = True
except ImportError:
    REPORTLAB_AVAILABLE = False

from . import admin_bp


def generate_pdf_report(title, headers, rows, filters=None):
    if not REPORTLAB_AVAILABLE:
        return None
    
    buffer = io.BytesIO()
    doc = SimpleDocTemplate(buffer, pagesize=A4, topMargin=0.5*inch, bottomMargin=0.5*inch)
    styles = getSampleStyleSheet()
    elements = []
    
    title_style = ParagraphStyle(
        'CustomTitle',
        parent=styles['Heading1'],
        fontSize=16,
        spaceAfter=12,
        alignment=1,
    )
    elements.append(Paragraph(title, title_style))
    
    if filters:
        filter_style = ParagraphStyle(
            'FilterStyle',
            parent=styles['Normal'],
            fontSize=9,
            spaceAfter=6,
            textColor=colors.grey,
        )
        filter_text = ' | '.join([f'{k}: {v}' for k, v in filters.items() if v])
        elements.append(Paragraph(filter_text, filter_style))
    
    elements.append(Paragraph(f'Generated: {datetime.now().strftime("%B %d, %Y %I:%M %p")}', styles['Normal']))
    elements.append(Spacer(1, 12))
    
    table_data = [headers] + rows
    col_count = len(headers)
    col_width = (A4[0] - 1*inch) / col_count
    
    table = Table(table_data, colWidths=[col_width]*col_count, repeatRows=1)
    table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#6366f1')),
        ('TEXTCOLOR', (0, 0), (-1, 0), colors.white),
        ('ALIGN', (0, 0), (-1, 0), 'CENTER'),
        ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
        ('FONTSIZE', (0, 0), (-1, 0), 9),
        ('BOTTOMPADDING', (0, 0), (-1, 0), 8),
        ('BACKGROUND', (0, 1), (-1, -1), colors.white),
        ('TEXTCOLOR', (0, 1), (-1, -1), colors.black),
        ('FONTNAME', (0, 1), (-1, -1), 'Helvetica'),
        ('FONTSIZE', (0, 1), (-1, -1), 8),
        ('ALIGN', (0, 1), (-1, -1), 'LEFT'),
        ('GRID', (0, 0), (-1, -1), 0.5, colors.grey),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor('#f8fafc')]),
    ]))
    
    elements.append(table)
    doc.build(elements)
    buffer.seek(0)
    return buffer


def generate_csv_response(headers, rows, filename):
    output = io.StringIO()
    writer = csv.writer(output)
    writer.writerow(headers)
    writer.writerows(rows)
    
    response = make_response(output.getvalue())
    response.headers['Content-Type'] = 'text/csv'
    response.headers['Content-Disposition'] = f'attachment; filename={filename}'
    return response


@admin_bp.route('/reports/system')
@admin_required
def system_report():
    from database import get_room_stats, get_reservation_stats, get_payment_stats, get_occupancy_rate, get_tenant_count, get_total_students
    
    room_stats = get_room_stats() or {}
    reservation_stats = get_reservation_stats() or {}
    payment_stats = get_payment_stats() or {}
    
    stats = {
        'total_rooms': room_stats.get('total_rooms', 0),
        'available_rooms': room_stats.get('available_rooms', 0),
        'occupied_rooms': room_stats.get('occupied_rooms', 0),
        'reserved_rooms': room_stats.get('reserved_rooms', 0),
        'maintenance_rooms': room_stats.get('maintenance_rooms', 0),
        'full_rooms': room_stats.get('full_rooms', 0),
        'total_capacity': room_stats.get('total_capacity', 0),
        'total_occupied_beds': room_stats.get('total_occupied_beds', 0),
        'occupancy_rate': get_occupancy_rate(),
        'bed_occupancy_rate': round((room_stats.get('total_occupied_beds', 0) / room_stats.get('total_capacity', 1)) * 100) if room_stats.get('total_capacity', 0) > 0 else 0,
        'total_reservations': reservation_stats.get('total_reservations', 0),
        'pending_reservations': reservation_stats.get('pending_reservations', 0),
        'approved_reservations': reservation_stats.get('approved_reservations', 0),
        'cancelled_reservations': reservation_stats.get('cancelled_reservations', 0),
        'total_payments': payment_stats.get('total_payments', 0),
        'total_revenue': float(payment_stats.get('total_revenue', 0) or 0),
        'pending_payments': payment_stats.get('pending_payments', 0),
        'overdue_payments': payment_stats.get('overdue_payments', 0),
        'total_tenants': get_tenant_count(),
        'total_students': get_total_students(),
    }
    
    return render_template('admin/reports/system.html', stats=stats, page_title='System Report')


@admin_bp.route('/reports/boarding-houses')
@admin_required
def boarding_house_report():
    from database import get_room_type_stats
    room_types = get_room_type_stats()
    
    return render_template('admin/reports/boarding_houses.html', room_types=room_types, page_title='Boarding House Report')


@admin_bp.route('/reports/revenue')
@admin_required
def revenue_report():
    from database import get_monthly_revenue, get_revenue_by_method, get_revenue_by_type, get_this_month_revenue, get_last_month_revenue
    
    monthly_revenue = get_monthly_revenue(24)
    revenue_by_method = get_revenue_by_method()
    revenue_by_type = get_revenue_by_type()
    this_month = get_this_month_revenue()
    last_month = get_last_month_revenue()
    
    return render_template('admin/reports/revenue.html',
        monthly_revenue=monthly_revenue,
        revenue_by_method=revenue_by_method,
        revenue_by_type=revenue_by_type,
        this_month=this_month,
        last_month=last_month,
        page_title='Revenue Report')


@admin_bp.route('/reports/payments')
@admin_required
def payments_report():
    from database import execute_query
    
    status = request.args.get('status', '')
    payment_type = request.args.get('payment_type', '')
    date_from = request.args.get('date_from', '')
    date_to = request.args.get('date_to', '')
    
    where = "WHERE 1=1"
    params = []
    
    if status:
        where += " AND p.status = %s"
        params.append(status)
    if payment_type:
        where += " AND p.payment_type = %s"
        params.append(payment_type)
    if date_from:
        where += " AND DATE(p.created_at) >= %s"
        params.append(date_from)
    if date_to:
        where += " AND DATE(p.created_at) <= %s"
        params.append(date_to)
    
    payments = execute_query(f"""
        SELECT p.*, s.first_name, s.last_name, s.student_id_number, rm.room_number
        FROM payments p
        JOIN students s ON p.student_id = s.id
        LEFT JOIN reservations r ON p.reservation_id = r.id
        LEFT JOIN rooms rm ON r.room_id = rm.id
        {where}
        ORDER BY p.created_at DESC
        LIMIT 1000
    """, tuple(params))
    
    statuses = ['pending', 'upcoming', 'due_today', 'partially_paid', 'paid', 'overdue', 'cancelled', 'refunded']
    payment_types = ['reservation_fee', 'advance_payment', 'monthly_rent', 'electric_bill', 'water_bill', 'other']
    
    if request.args.get('export') == 'csv':
        headers = ['Payment Code', 'Student', 'Student ID', 'Room', 'Type', 'Amount', 'Late Fee', 'Amount Paid', 'Method', 'Status', 'Billing Period', 'Due Date', 'Paid At', 'Created At']
        rows = []
        for p in payments:
            rows.append([
                p['payment_code'],
                f"{p['first_name']} {p['last_name']}",
                p['student_id_number'] or '',
                p['room_number'] or '',
                p['payment_type'].replace('_', ' ').title(),
                f"{float(p['amount']):.2f}",
                f"{float(p['late_fee'] or 0):.2f}",
                f"{float(p['amount_paid'] or 0):.2f}",
                (p['payment_method'] or 'cash').title(),
                p['status'].replace('_', ' ').title(),
                p['billing_period'] or '',
                p['due_date'] or '',
                p['paid_at'].strftime('%Y-%m-%d %H:%M') if p['paid_at'] else '',
                p['created_at'].strftime('%Y-%m-%d %H:%M') if p['created_at'] else '',
            ])
        return generate_csv_response(headers, rows, f'payments_report_{date.today()}.csv')
    
    if request.args.get('export') == 'pdf':
        headers = ['Payment Code', 'Student', 'Type', 'Amount', 'Paid', 'Method', 'Status', 'Date']
        rows = []
        for p in payments[:200]:
            rows.append([
                p['payment_code'],
                f"{p['first_name']} {p['last_name']}",
                p['payment_type'].replace('_', ' ').title(),
                f"₱{float(p['amount']):.2f}",
                f"₱{float(p['amount_paid'] or 0):.2f}",
                (p['payment_method'] or 'cash').title(),
                p['status'].replace('_', ' ').title(),
                p['created_at'].strftime('%Y-%m-%d') if p['created_at'] else '',
            ])
        buffer = generate_pdf_report('Payments Report', headers, rows, {
            'Status': status, 'Type': payment_type, 'From': date_from, 'To': date_to
        })
        if buffer:
            return send_file(buffer, as_attachment=True, download_name=f'payments_report_{date.today()}.pdf', mimetype='application/pdf')
    
    return render_template('admin/reports/payments.html',
        payments=payments,
        statuses=statuses,
        payment_types=payment_types,
        filters={'status': status, 'payment_type': payment_type, 'date_from': date_from, 'date_to': date_to},
        page_title='Payments Report')


@admin_bp.route('/reports/occupancy')
@admin_required
def occupancy_report():
    from database import get_room_status_distribution, get_room_type_stats, get_occupancy_rate
    
    room_status = get_room_status_distribution()
    room_type_stats = get_room_type_stats()
    occupancy_rate = get_occupancy_rate()
    
    return render_template('admin/reports/occupancy.html',
        room_status=room_status,
        room_type_stats=room_type_stats,
        occupancy_rate=occupancy_rate,
        page_title='Occupancy Report')


@admin_bp.route('/reports/reservations')
@admin_required
def reservations_report():
    from database import execute_query
    
    status = request.args.get('status', '')
    date_from = request.args.get('date_from', '')
    date_to = request.args.get('date_to', '')
    
    where = "WHERE 1=1"
    params = []
    
    if status:
        where += " AND r.status = %s"
        params.append(status)
    if date_from:
        where += " AND DATE(r.created_at) >= %s"
        params.append(date_from)
    if date_to:
        where += " AND DATE(r.created_at) <= %s"
        params.append(date_to)
    
    reservations = execute_query(f"""
        SELECT r.*, s.first_name, s.last_name, s.student_id_number, rm.room_number, rm.room_name
        FROM reservations r
        JOIN students s ON r.student_id = s.id
        JOIN rooms rm ON r.room_id = rm.id
        {where}
        ORDER BY r.created_at DESC
        LIMIT 1000
    """, tuple(params))
    
    statuses = ['pending', 'approved', 'rejected', 'cancelled', 'expired']
    
    if request.args.get('export') == 'csv':
        headers = ['Reservation Code', 'Student', 'Student ID', 'Room', 'Move-in Date', 'Status', 'Created At']
        rows = []
        for r in reservations:
            rows.append([
                r['reservation_code'],
                f"{r['first_name']} {r['last_name']}",
                r['student_id_number'] or '',
                f"{r['room_number']} - {r['room_name']}",
                r['move_in_date'] or '',
                r['status'].title(),
                r['created_at'].strftime('%Y-%m-%d %H:%M') if r['created_at'] else '',
            ])
        return generate_csv_response(headers, rows, f'reservations_report_{date.today()}.csv')
    
    return render_template('admin/reports/reservations.html',
        reservations=reservations,
        statuses=statuses,
        filters={'status': status, 'date_from': date_from, 'date_to': date_to},
        page_title='Reservations Report')


@admin_bp.route('/reports/tenants')
@admin_required
def tenants_report():
    from database import execute_query
    
    tenants = execute_query("""
        SELECT s.*, u.email, r.room_number, r.room_name, r.move_in_date, r.status as reservation_status,
               (SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) 
                FROM payments WHERE student_id = s.id AND status IN ('paid', 'partially_paid', 'refunded') AND amount_paid > 0) as total_paid,
               (SELECT COUNT(*) FROM payments WHERE student_id = s.id AND status = 'overdue') as overdue_count,
               (SELECT COALESCE(SUM(amount - amount_paid), 0) FROM payments WHERE student_id = s.id AND status = 'overdue') as overdue_amount
        FROM students s
        JOIN users u ON s.user_id = u.id
        JOIN reservations r ON s.id = r.student_id
        JOIN rooms rm ON r.room_id = rm.id
        WHERE r.status = 'approved'
        ORDER BY r.move_in_date DESC
    """)
    
    if request.args.get('export') == 'csv':
        headers = ['Student ID', 'Name', 'Email', 'Room', 'Move-in Date', 'Total Paid', 'Overdue Count', 'Overdue Amount']
        rows = []
        for t in tenants:
            rows.append([
                t['student_id_number'] or '',
                f"{t['first_name']} {t['last_name']}",
                t['email'],
                f"{t['room_number']} - {t['room_name']}",
                t['move_in_date'] or '',
                f"₱{float(t['total_paid']):.2f}",
                t['overdue_count'],
                f"₱{float(t['overdue_amount']):.2f}",
            ])
        return generate_csv_response(headers, rows, f'tenants_report_{date.today()}.csv')
    
    return render_template('admin/reports/tenants.html',
        tenants=tenants,
        page_title='Tenant Report')