import os
import sys
import logging
from logging.handlers import RotatingFileHandler
from datetime import datetime
import pymysql
pymysql.install_as_MySQLdb()

from flask import Flask, session, g, request, redirect, url_for, flash, jsonify
from flask_wtf.csrf import CSRFProtect, generate_csrf
from werkzeug.middleware.proxy_fix import ProxyFix
from flask_compress import Compress

from config import config

csrf = CSRFProtect()
compress = Compress()

def create_app(config_name=None):
    if config_name is None:
        config_name = os.environ.get('FLASK_ENV', 'default')
    
    app = Flask(__name__)
    app.config.from_object(config[config_name])
    
    app.wsgi_app = ProxyFix(app.wsgi_app, x_for=1, x_proto=1, x_host=1, x_prefix=1)
    
    csrf.init_app(app)
    compress.init_app(app)
    
    configure_logging(app)
    register_blueprints(app)
    register_template_globals(app)
    register_request_handlers(app)
    register_error_handlers(app)
    register_cli_commands(app)
    
    with app.app_context():
        init_db_pool()
    
    return app


def configure_logging(app):
    if not os.path.exists('logs'):
        os.makedirs('logs')
    
    log_level = getattr(logging, app.config['LOG_LEVEL'].upper(), logging.INFO)
    
    formatter = logging.Formatter(
        '%(asctime)s %(levelname)s: %(message)s [in %(pathname)s:%(lineno)d]'
    )
    
    file_handler = RotatingFileHandler(
        app.config['LOG_FILE'],
        maxBytes=10240000,
        backupCount=10
    )
    file_handler.setFormatter(formatter)
    file_handler.setLevel(log_level)
    
    console_handler = logging.StreamHandler(sys.stdout)
    console_handler.setFormatter(formatter)
    console_handler.setLevel(log_level)
    
    app.logger.addHandler(file_handler)
    app.logger.addHandler(console_handler)
    app.logger.setLevel(log_level)
    
    app.logger.info('Student Boarding House Flask Dashboard starting up')


def register_blueprints(app):
    from blueprints.admin import admin_bp
    from blueprints.manager import manager_bp
    from blueprints.student import student_bp
    from blueprints.auth import auth_bp
    from blueprints.api import api_bp
    
    app.register_blueprint(auth_bp)
    app.register_blueprint(admin_bp, url_prefix='/admin')
    app.register_blueprint(manager_bp, url_prefix='/manager')
    app.register_blueprint(student_bp, url_prefix='/student')
    app.register_blueprint(api_bp, url_prefix='/api')


def register_template_globals(app):
    @app.context_processor
    def inject_globals():
        return {
            'now': datetime.now(),
            'app_name': 'Student Boarding House Dashboard',
            'csrf_token': generate_csrf,
            'config': app.config,
        }
    
    @app.template_filter('currency')
    def currency_filter(value):
        if value is None:
            return '₱0.00'
        return f'₱{float(value):,.2f}'
    
    @app.template_filter('date_format')
    def date_format_filter(value, format='%b %d, %Y'):
        if value is None:
            return ''
        if isinstance(value, str):
            try:
                value = datetime.fromisoformat(value.replace('Z', '+00:00'))
            except:
                return value
        return value.strftime(format)
    
    @app.template_filter('datetime_format')
    def datetime_format_filter(value, format='%b %d, %Y %I:%M %p'):
        if value is None:
            return ''
        if isinstance(value, str):
            try:
                value = datetime.fromisoformat(value.replace('Z', '+00:00'))
            except:
                return value
        return value.strftime(format)
    
    @app.template_filter('timeago')
    def timeago_filter(value):
        if value is None:
            return ''
        if isinstance(value, str):
            try:
                value = datetime.fromisoformat(value.replace('Z', '+00:00'))
            except:
                return value
        now = datetime.now()
        diff = now - value
        if diff.days > 0:
            return f'{diff.days}d ago'
        elif diff.seconds > 3600:
            return f'{diff.seconds // 3600}h ago'
        elif diff.seconds > 60:
            return f'{diff.seconds // 60}m ago'
        else:
            return 'Just now'
    
    @app.template_filter('status_badge')
    def status_badge_filter(status):
        badges = {
            'available': ('success', 'fa-check-circle'),
            'occupied': ('warning', 'fa-bed'),
            'reserved': ('info', 'fa-calendar-check'),
            'under_maintenance': ('danger', 'fa-tools'),
            'pending': ('warning', 'fa-clock'),
            'approved': ('success', 'fa-check-circle'),
            'rejected': ('danger', 'fa-times-circle'),
            'cancelled': ('secondary', 'fa-ban'),
            'expired': ('secondary', 'fa-hourglass-end'),
            'paid': ('success', 'fa-check-circle'),
            'overdue': ('danger', 'fa-exclamation-triangle'),
            'partially_paid': ('info', 'fa-circle-half-stroke'),
            'upcoming': ('info', 'fa-calendar-plus'),
            'due_today': ('warning', 'fa-calendar-day'),
            'refunded': ('secondary', 'fa-rotate-left'),
            'open': ('info', 'fa-folder-open'),
            'under_review': ('warning', 'fa-magnifying-glass'),
            'resolved': ('success', 'fa-check-circle'),
            'closed': ('secondary', 'fa-folder-closed'),
            'in_progress': ('info', 'fa-spinner fa-spin'),
            'new': ('info', 'fa-envelope'),
            'read': ('secondary', 'fa-envelope-open'),
            'replied': ('success', 'fa-reply'),
            'archived': ('secondary', 'fa-box-archive'),
            'active': ('success', 'fa-circle'),
            'inactive': ('secondary', 'fa-circle'),
            'suspended': ('danger', 'fa-ban'),
            'locked': ('warning', 'fa-lock'),
        }
        badge_class, icon = badges.get(status, ('secondary', 'fa-question'))
        return f'<span class="badge bg-{badge_class}"><i class="fas {icon} me-1"></i>{status.replace("_", " ").title()}</span>'


def register_request_handlers(app):
    @app.before_request
    def before_request():
        g.db = get_db()
        g.request_start_time = datetime.now()
        
        if request.endpoint and 'static' not in request.endpoint:
            app.logger.debug(f'{request.method} {request.path}')
    
    @app.after_request
    def after_request(response):
        response.headers['X-Content-Type-Options'] = 'nosniff'
        response.headers['X-Frame-Options'] = 'SAMEORIGIN'
        response.headers['X-XSS-Protection'] = '1; mode=block'
        response.headers['Referrer-Policy'] = 'strict-origin-when-cross-origin'
        
        if app.config['SESSION_COOKIE_SECURE']:
            response.headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains'
        
        if hasattr(g, 'request_start_time'):
            duration = (datetime.now() - g.request_start_time).total_seconds() * 1000
            app.logger.debug(f'Request took {duration:.2f}ms')
        
        return response
    
    @app.teardown_request
    def teardown_request(exception):
        db = g.pop('db', None)
        if db is not None:
            try:
                db.close()
            except:
                pass


def register_error_handlers(app):
    @app.errorhandler(400)
    def bad_request(e):
        if request.is_json or request.path.startswith('/api/'):
            return jsonify(error='Bad request', message=str(e)), 400
        return render_error('400.html', 400, 'Bad Request', 'The request could not be understood.')
    
    @app.errorhandler(401)
    def unauthorized(e):
        if request.is_json or request.path.startswith('/api/'):
            return jsonify(error='Unauthorized', message='Authentication required'), 401
        flash('Please log in to access this page.', 'warning')
        return redirect(url_for('auth.login', next=request.url))
    
    @app.errorhandler(403)
    def forbidden(e):
        if request.is_json or request.path.startswith('/api/'):
            return jsonify(error='Forbidden', message='You do not have permission to access this resource'), 403
        return render_error('403.html', 403, 'Forbidden', 'You do not have permission to access this page.')
    
    @app.errorhandler(404)
    def not_found(e):
        if request.is_json or request.path.startswith('/api/'):
            return jsonify(error='Not found', message='The requested resource was not found'), 404
        return render_error('404.html', 404, 'Page Not Found', 'The page you are looking for does not exist.')
    
    @app.errorhandler(500)
    def internal_error(e):
        app.logger.error(f'Internal server error: {e}', exc_info=True)
        if request.is_json or request.path.startswith('/api/'):
            return jsonify(error='Internal server error', message='An unexpected error occurred'), 500
        return render_error('500.html', 500, 'Server Error', 'An unexpected error occurred. Please try again later.')


def render_error(template, code, title, message):
    from flask import render_template
    try:
        return render_template(f'errors/{template}', error_code=code, error_title=title, error_message=message), code
    except:
        return f'''
        <!DOCTYPE html>
        <html><head><title>{title}</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="d-flex align-items-center justify-content-center vh-100 bg-light">
        <div class="card shadow-sm p-5 text-center" style="max-width: 400px;">
            <div class="display-1 text-danger">{code}</div>
            <h3 class="mt-3">{title}</h3>
            <p class="text-muted">{message}</p>
            <a href="/" class="btn btn-primary">Go Home</a>
        </div></body></html>
        ''', code


def register_cli_commands(app):
    @app.cli.command('init-db')
    def init_db():
        from database import init_database
        init_database()
        print('Database initialized.')


_db_pool = None

def init_db_pool():
    global _db_pool
    from dbutils.pooled_db import PooledDB
    from config import config
    
    cfg = config[os.environ.get('FLASK_ENV', 'default')]
    
    _db_pool = PooledDB(
        creator=pymysql,
        maxconnections=20,
        mincached=2,
        maxcached=5,
        blocking=True,
        host=cfg.DB_HOST,
        port=cfg.DB_PORT,
        user=cfg.DB_USER,
        password=cfg.DB_PASSWORD,
        database=cfg.DB_NAME,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=True,
    )


def get_db():
    global _db_pool
    if _db_pool is None:
        init_db_pool()
    return _db_pool.connection()


def execute_query(query, params=None, fetch=True, commit=False):
    conn = get_db()
    try:
        with conn.cursor() as cursor:
            cursor.execute(query, params or ())
            if commit:
                conn.commit()
            if fetch:
                return cursor.fetchall()
            return cursor.rowcount
    except Exception as e:
        conn.rollback()
        raise
    finally:
        conn.close()


def execute_one(query, params=None):
    results = execute_query(query, params, fetch=True)
    return results[0] if results else None


app = create_app()

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)