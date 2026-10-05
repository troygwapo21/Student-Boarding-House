import json
import hmac
import os
import sys
from collections import defaultdict
from datetime import date, datetime


PAID_STATUSES = {"paid", "partially_paid", "refunded"}


def parse_date(value):
    if not value:
        return None
    if isinstance(value, (date, datetime)):
        return value.date() if isinstance(value, datetime) else value
    text = str(value).strip()
    if not text:
        return None
    try:
        return datetime.fromisoformat(text.replace("Z", "+00:00")).date()
    except ValueError:
        try:
            return date.fromisoformat(text[:10])
        except ValueError:
            return None


def as_number(value):
    try:
        return float(value or 0)
    except (TypeError, ValueError):
        return 0.0


def month_sequence(last_month, count):
    months = []
    year, month = last_month.year, last_month.month
    for _ in range(count):
        months.append(date(year, month, 1))
        month -= 1
        if month == 0:
            year -= 1
            month = 12
    return list(reversed(months))


def percent(numerator, denominator):
    if denominator <= 0:
        return 0
    return int((numerator / denominator) * 100 + 0.5)


def calculate(data):
    as_of_date = parse_date(data.get("as_of_date"))
    if as_of_date is None:
        raise ValueError("as_of_date must be a valid YYYY-MM-DD date")

    months = month_sequence(as_of_date.replace(day=1), 12)
    month_keys = [month.strftime("%Y-%m") for month in months]
    month_set = set(month_keys)
    revenue_by_month = defaultdict(float)
    reservations_by_month = defaultdict(int)
    revenue_by_type = defaultdict(float)

    for payment in data.get("payments", []):
        if str(payment.get("status", "")).lower() not in PAID_STATUSES:
            continue
        if as_number(payment.get("amount_paid")) <= 0:
            continue
        payment_date = parse_date(payment.get("paid_at"))
        if payment_date is None:
            continue
        month_key = payment_date.strftime("%Y-%m")
        if month_key not in month_set:
            continue
        net_amount = as_number(payment.get("amount_paid")) - as_number(payment.get("refunded_amount"))
        revenue_by_month[month_key] += net_amount
        if month_key == month_keys[-1]:
            payment_type = str(payment.get("payment_type") or "other").strip() or "other"
            revenue_by_type[payment_type] += net_amount

    for reservation in data.get("reservations", []):
        created_date = parse_date(reservation.get("created_at"))
        if created_date is None:
            continue
        month_key = created_date.strftime("%Y-%m")
        if month_key in month_set:
            reservations_by_month[month_key] += 1

    methods = defaultdict(float)
    for row in data.get("payment_methods", []):
        if str(row.get("status", "")).lower() not in PAID_STATUSES:
            continue
        amount_paid = as_number(row.get("amount_paid"))
        if amount_paid <= 0:
            continue
        method = str(row.get("method") or "cash").strip().lower() or "cash"
        methods[method] += amount_paid - as_number(row.get("refunded_amount"))

    room_types = {}
    total_rooms = 0
    occupied_rooms = 0
    total_capacity = 0
    occupied_beds = 0
    for room in data.get("rooms", []):
        room_type = str(room.get("room_type") or "other").strip().lower() or "other"
        status = str(room.get("status") or "").strip().lower()
        capacity = int(as_number(room.get("max_capacity")))
        beds = int(as_number(room.get("current_occupancy")))
        if room_type not in room_types:
            room_types[room_type] = {
                "room_type": room_type,
                "total_rooms": 0,
                "occupied": 0,
                "occupied_beds": 0,
                "capacity": 0,
            }
        stats = room_types[room_type]
        stats["total_rooms"] += 1
        stats["occupied"] += int(status == "occupied")
        stats["occupied_beds"] += beds
        stats["capacity"] += capacity
        total_rooms += 1
        occupied_rooms += int(status == "occupied")
        total_capacity += capacity
        occupied_beds += beds

    genders = defaultdict(int)
    students_by_year = defaultdict(int)
    for student in data.get("students", []):
        gender = str(student.get("gender") or "other").strip().lower() or "other"
        genders[gender] += 1
        year_level = str(student.get("year_level") or "N/A").strip() or "N/A"
        students_by_year[year_level] += 1

    revenue_type_rows = [
        {"payment_type": key, "total": round(total, 2)}
        for key, total in sorted(revenue_by_type.items())
    ]
    revenue_method_rows = [
        {"method": key, "total": round(total, 2)}
        for key, total in sorted(methods.items())
    ]
    room_type_rows = [room_types[key] for key in sorted(room_types)]
    gender_rows = [
        {"gender": key, "c": count}
        for key, count in sorted(genders.items())
    ]
    year_rows = [
        {"year_level": key, "c": count}
        for key, count in sorted(students_by_year.items(), key=lambda row: (-row[1], row[0]))[:6]
    ]

    return {
        "charts": {
            "monthly": {
                "labels": [month.strftime("%b") for month in months],
                "revenue": [round(revenue_by_month[key], 2) for key in month_keys],
                "reservations": [reservations_by_month[key] for key in month_keys],
            }
        },
        "revenue_by_type": revenue_type_rows,
        "revenue_by_method": revenue_method_rows,
        "room_type_stats": room_type_rows,
        "students_by_gender": gender_rows,
        "students_by_year": year_rows,
        "total_capacity": total_capacity,
        "total_occupied_beds": occupied_beds,
        "bed_occupancy_rate": percent(occupied_beds, total_capacity),
        "occupancy_rate": percent(occupied_rooms, total_rooms),
    }


def application(environ, start_response):
    if environ.get("REQUEST_METHOD") == "GET" and environ.get("PATH_INFO") == "/health":
        body = b'{"status":"ok"}'
        start_response("200 OK", [("Content-Type", "application/json"), ("Content-Length", str(len(body)))])
        return [body]

    if environ.get("REQUEST_METHOD") != "POST" or environ.get("PATH_INFO") != "/analytics":
        body = b'{"error":"not_found"}'
        start_response("404 Not Found", [("Content-Type", "application/json"), ("Content-Length", str(len(body)))])
        return [body]

    expected_token = os.environ.get("DASHBOARD_ANALYTICS_API_TOKEN", "")
    supplied_token = environ.get("HTTP_AUTHORIZATION", "")
    if len(expected_token) < 32 or not hmac.compare_digest(supplied_token, f"Bearer {expected_token}"):
        body = b'{"error":"unauthorized"}'
        start_response("401 Unauthorized", [("Content-Type", "application/json"), ("Content-Length", str(len(body)))])
        return [body]

    try:
        content_length = int(environ.get("CONTENT_LENGTH") or "0")
        if content_length <= 0 or content_length > 16 * 1024 * 1024:
            raise ValueError("request body size is invalid")
        payload = environ["wsgi.input"].read(content_length)
        data = json.loads(payload)
        if not isinstance(data, dict):
            raise ValueError("request body must be a JSON object")
        result = calculate(data)
        body = json.dumps(result, allow_nan=False).encode("utf-8")
        status = "200 OK"
    except (KeyError, TypeError, ValueError, json.JSONDecodeError) as exc:
        print(f"Dashboard analytics API request rejected: {exc}", file=sys.stderr)
        body = b'{"error":"invalid_request"}'
        status = "400 Bad Request"
    except Exception as exc:
        print(f"Dashboard analytics API failed: {exc}", file=sys.stderr)
        body = b'{"error":"analytics_failed"}'
        status = "500 Internal Server Error"

    start_response(status, [("Content-Type", "application/json"), ("Content-Length", str(len(body)))])
    return [body]


if __name__ == "__main__":
    try:
        payload = json.load(sys.stdin)
        result = calculate(payload)
        json.dump(result, sys.stdout, allow_nan=False)
    except Exception as exc:
        print(f"Dashboard analytics error: {exc}", file=sys.stderr)
        sys.exit(1)
