<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $stmt = db()->query(
            'SELECT building_id, marker_id AS id, building_name AS name, category,
                    latitude AS lat, longitude AS lng, description,
                    image, info_status AS status, source_note AS source
             FROM buildings
             ORDER BY building_id'
        );

        json_response(['ok' => true, 'buildings' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $data = request_json();
        $id = clean_marker_id((string)($data['id'] ?? ''));
        $previousId = clean_marker_id((string)($data['previousId'] ?? ''));
        $name = trim((string)($data['name'] ?? ''));
        $category = trim((string)($data['category'] ?? 'Prototype'));
        $description = trim((string)($data['description'] ?? ''));
        $status = trim((string)($data['status'] ?? 'Prototype'));
        $source = trim((string)($data['source'] ?? ''));
        $lat = filter_var($data['lat'] ?? null, FILTER_VALIDATE_FLOAT);
        $lng = filter_var($data['lng'] ?? null, FILTER_VALIDATE_FLOAT);

        if ($id === '' || $name === '' || $description === '' || $lat === false || $lng === false) {
            json_response(['ok' => false, 'message' => 'Complete the building name, marker ID, coordinates, and description.'], 422);
        }

        $pdo = db();
        $pdo->beginTransaction();

        if ($previousId !== '') {
            $stmt = $pdo->prepare(
                'UPDATE buildings
                 SET marker_id = ?, building_name = ?, category = ?, latitude = ?, longitude = ?,
                     description = ?, image = ?, info_status = ?, source_note = ?
                 WHERE marker_id = ?'
            );
            $stmt->execute([$id, $name, $category, $lat, $lng, $description, 'markers/' . $id . '.png', $status, $source, $previousId]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO buildings
                    (marker_id, building_name, category, latitude, longitude, description, image, info_status, source_note)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$id, $name, $category, $lat, $lng, $description, 'markers/' . $id . '.png', $status, $source]);
        }

        $buildingId = (int)$pdo->query('SELECT building_id FROM buildings WHERE marker_id = ' . $pdo->quote($id))->fetchColumn();
        $info = $pdo->prepare(
            'INSERT INTO building_info (building_id, marker_image, ar_payload, description)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE marker_image = VALUES(marker_image), description = VALUES(description)'
        );
        $info->execute([$buildingId, 'markers/' . $id . '.png', 'msu-mcest:' . $id, $description]);
        $pdo->commit();

        json_response(['ok' => true, 'message' => 'Building saved in ar_campus.']);
    }

    if ($method === 'DELETE') {
        $id = clean_marker_id((string)($_GET['id'] ?? ''));
        if ($id === '') {
            json_response(['ok' => false, 'message' => 'Missing marker ID.'], 422);
        }

        $stmt = db()->prepare('DELETE FROM buildings WHERE marker_id = ?');
        $stmt->execute([$id]);

        json_response(['ok' => true, 'message' => 'Building deleted from ar_campus.']);
    }

    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['ok' => false, 'message' => $error->getMessage()], 500);
}
