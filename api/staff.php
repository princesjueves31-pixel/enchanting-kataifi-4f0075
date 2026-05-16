<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $stmt = db()->query(
            'SELECT s.staff_id, s.name, s.department, s.office,
                    b.marker_id, b.building_name
             FROM staff s
             LEFT JOIN buildings b ON b.building_id = s.building_id
             ORDER BY s.name'
        );

        json_response(['ok' => true, 'staff' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $data = request_json();
        $staffId = (int)($data['staff_id'] ?? 0);
        $name = trim((string)($data['name'] ?? ''));
        $department = trim((string)($data['department'] ?? ''));
        $office = trim((string)($data['office'] ?? ''));
        $markerId = clean_marker_id((string)($data['marker_id'] ?? ''));

        if ($name === '' || $department === '' || $office === '') {
            json_response(['ok' => false, 'message' => 'Complete the staff name, department, and office.'], 422);
        }

        $buildingId = null;
        if ($markerId !== '') {
            $stmt = db()->prepare('SELECT building_id FROM buildings WHERE marker_id = ? LIMIT 1');
            $stmt->execute([$markerId]);
            $buildingId = $stmt->fetchColumn() ?: null;
        }

        if ($staffId > 0) {
            $stmt = db()->prepare('UPDATE staff SET name = ?, department = ?, office = ?, building_id = ? WHERE staff_id = ?');
            $stmt->execute([$name, $department, $office, $buildingId, $staffId]);
        } else {
            $stmt = db()->prepare('INSERT INTO staff (name, department, office, building_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $department, $office, $buildingId]);
        }

        json_response(['ok' => true, 'message' => 'Staff record saved.']);
    }

    if ($method === 'DELETE') {
        $staffId = (int)($_GET['id'] ?? 0);
        if ($staffId <= 0) {
            json_response(['ok' => false, 'message' => 'Missing staff ID.'], 422);
        }
        $stmt = db()->prepare('DELETE FROM staff WHERE staff_id = ?');
        $stmt->execute([$staffId]);

        json_response(['ok' => true, 'message' => 'Staff record deleted.']);
    }

    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
} catch (Throwable $error) {
    json_response(['ok' => false, 'message' => $error->getMessage()], 500);
}
