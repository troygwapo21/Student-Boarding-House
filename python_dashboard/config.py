import os
from datetime import timedelta
from dotenv import load_dotenv

load_dotenv()

class Config:
    SECRET_KEY = os.environ.get('FLASK_SECRET_KEY', 'iv@suPrD7e%sF.g,')
    
    DB_HOST = os.environ.get('DB_HOST', 'localhost')
    DB_PORT = int(os.environ.get('DB_PORT', 3306))
    DB_NAME = os.environ.get('DB_NAME', 'despierisXcN_HAHA')
    DB_USER = os.environ.get('DB_USER', 'despierisXcN_HAHA')
    DB_PASSWORD = os.environ.get('DB_PASSWORD', '8 Ball Pool')
    
    MYSQL_DSN = f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_NAME}?charset=utf8mb4"
    
    SESSION_COOKIE_SECURE = os.environ.get('SESSION_COOKIE_SECURE', 'True').lower() == 'true'
    SESSION_COOKIE_HTTPONLY = True
    SESSION_COOKIE_SAMESITE = 'Lax'
    SESSION_COOKIE_NAME = 'flask_session'
    PERMANENT_SESSION_LIFETIME = timedelta(hours=2)
    
    WTF_CSRF_ENABLED = True
    WTF_CSRF_TIME_LIMIT = None
    WTF_CSRF_SSL_STRICT = True
    
    TIMEZONE = 'Asia/Manila'        
    
    LOG_LEVEL = os.environ.get('LOG_LEVEL', 'INFO')
    LOG_FILE = os.path.join(os.path.dirname(__file__), 'logs', 'app.log')
    
    REPORTS_DIR = os.path.join(os.path.dirname(__file__), 'reports', 'generated')
    
    PAGINATION_PER_PAGE = 25
    
    CHART_COLORS = {
        'primary': '#6366f1',
        'secondary': '#8b5cf6',
        'success': '#10b981',
        'warning': '#f59e0b',
        'danger': '#ef4444',
        'info': '#0ea5e9',
        'light': '#f8fafc',
        'dark': '#0f172a',
    }
    
    CHART_PALETTE = [
        '#6366f1', '#8b5cf6', '#0ea5e9', '#10b981', '#f59e0b',
        '#ef4444', '#ec4899', '#14b8a6', '#f97316', '#84cc16'
    ]


class DevelopmentConfig(Config):
    DEBUG = True
    SESSION_COOKIE_SECURE = False


class ProductionConfig(Config):
    DEBUG = False


class TestingConfig(Config):
    TESTING = True
    DB_NAME = 'test_student_boarding_house'
    WTF_CSRF_ENABLED = False


config = {
    'development': DevelopmentConfig,
    'production': ProductionConfig,
    'testing': TestingConfig,
    'default': DevelopmentConfig,
}