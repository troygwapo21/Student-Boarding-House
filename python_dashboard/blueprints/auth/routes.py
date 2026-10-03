from flask import render_template, request, redirect, url_for, flash, session, current_app
from werkzeug.security import check_password_hash
from functools import wraps
from database import get_user_by_email, verify_user_session, get_manager_by_user_id, get_student_by_user_id

from . import auth_bp


def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session:
            flash('Please log in to access this page.', 'warning')
            return redirect(url_for('auth.login', next=request.url))
        return f(*args, **kwargs)
    return decorated_function


def role_required(*roles):
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            if 'user_id' not in session:
                flash('Please log in to access this page.', 'warning')
                return redirect(url_for('auth.login', next=request.url))
            if session.get('user_role') not in roles:
                flash('You do not have permission to access this page.', 'danger')
                return redirect(url_for('auth.login'))
            return f(*args, **kwargs)
        return decorated_function
    return decorator


def get_current_user():
    if 'user_id' not in session:
        return None
    return verify_user_session(session['user_id'])


@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    if 'user_id' in session:
        return redirect_based_on_role()
    
    if request.method == 'POST':
        email = request.form.get('email', '').lower().strip()
        password = request.form.get('password', '')
        
        if not email or not password:
            flash('Please enter both email and password.', 'danger')
            return render_template('auth/login.html')
        
        user = get_user_by_email(email)
        
        if not user or not check_password_hash(user['password'], password):
            current_app.logger.warning(f'Failed login attempt for email: {email}')
            flash('Invalid email or password.', 'danger')
            return render_template('auth/login.html')
        
        if user['status'] != 'active':
            flash('Your account is not active. Please contact support.', 'danger')
            return render_template('auth/login.html')
        
        if user.get('email_verified') != 1:
            flash('Please verify your email before logging in.', 'warning')
            return redirect(url_for('auth.verify_email', email=email))
        
        session['user_id'] = user['id']
        session['user_email'] = user['email']
        session['user_role'] = user['role']
        session.permanent = True
        
        current_app.logger.info(f'User logged in: {user["email"]} ({user["role"]})')
        
        flash('Welcome back!', 'success')
        return redirect_based_on_role()
    
    return render_template('auth/login.html')


@auth_bp.route('/logout')
def logout():
    user_email = session.get('user_email', 'Unknown')
    session.clear()
    flash('You have been logged out.', 'info')
    current_app.logger.info(f'User logged out: {user_email}')
    return redirect(url_for('auth.login'))


def redirect_based_on_role():
    role = session.get('user_role')
    if role == 'super_admin':
        return redirect(url_for('admin.dashboard'))
    elif role == 'manager':
        return redirect(url_for('manager.dashboard'))
    elif role == 'student':
        return redirect(url_for('student.dashboard'))
    return redirect(url_for('auth.login'))


@auth_bp.route('/verify-email')
def verify_email():
    email = request.args.get('email', '')
    return render_template('auth/verify_email.html', email=email)


@auth_bp.context_processor
def inject_user():
    return dict(current_user=get_current_user())