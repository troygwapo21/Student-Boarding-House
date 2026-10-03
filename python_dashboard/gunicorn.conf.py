# Gunicorn configuration for production

import multiprocessing

# Server socket
bind = "0.0.0.0:5000"
backlog = 2048

# Worker processes
workers = multiprocessing.cpu_count() * 2 + 1
worker_class = "sync"
worker_connections = 1000
timeout = 60
keepalive = 5

# Restart workers after this many requests (prevent memory leaks)
max_requests = 1000
max_requests_jitter = 100

# Logging
accesslog = "logs/access.log"
errorlog = "logs/error.log"
loglevel = "info"
access_log_format = '%(h)s %(l)s %(u)s %(t)s "%(r)s" %(s)s %(b)s "%(f)s" "%(a)s" %(D)s'

# Process naming
proc_name = "student_boarding_house_dashboard"

# Daemon mode (set to False when using systemd/supervisor)
daemon = False

# PID file
pidfile = "logs/gunicorn.pid"

# User/group (adjust for your deployment)
# user = "www-data"
# group = "www-data"

# SSL (if not using reverse proxy)
# keyfile = "ssl/key.pem"
# certfile = "ssl/cert.pem"

# Preload app for better memory usage
preload_app = True

# Worker recycling
graceful_timeout = 30

# Limit request fields
limit_request_fields = 100
limit_request_field_size = 8190

# StatsD metrics (optional)
# statsd_host = "localhost:8125"
# statsd_prefix = "flask_dashboard"