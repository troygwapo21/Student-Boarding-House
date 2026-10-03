-- ============================================================
-- MIGRATION: Reconcile room capacity + status accuracy
-- Recomputes current_occupancy from approved reservations and
-- derives a truthful room status:
--   * under_maintenance is preserved (manual override)
--   * rooms with boarders (approved reservations) -> occupied
--   * reserved is preserved only when the room is empty
--   * everything else -> available
-- Safe to run multiple times (idempotent).
-- ============================================================

UPDATE rooms r
LEFT JOIN (
    SELECT room_id, COUNT(*) AS cnt
    FROM reservations
    WHERE status = 'approved'
    GROUP BY room_id
) a ON a.room_id = r.id
SET r.current_occupancy = LEAST(r.max_capacity, IFNULL(a.cnt, 0)),
    r.status = IF(
        r.status = 'under_maintenance',
        'under_maintenance',
        IF(
            IFNULL(a.cnt, 0) > 0,
            'occupied',
            IF(r.status = 'reserved', 'reserved', 'available')
        )
    );

SELECT 'Room capacity/status reconciled. See results below:' AS result;
SELECT id, room_number, room_name, max_capacity, current_occupancy, status
FROM rooms
ORDER BY room_number;
