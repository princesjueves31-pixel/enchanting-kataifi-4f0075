<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $stmt = db()->query(
            'SELECT f.feedback_id, f.user_name, f.comment, f.date_created,
                    b.building_name, b.marker_id
             FROM feedback f
             LEFT JOIN buildings b ON b.building_id = f.building_id
             ORDER BY f.date_created DESC'
        );

        json_response(['ok' => true, 'feedback' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $data = request_json();
        $userName = trim((string)($data['user_name'] ?? 'Guest'));
        $comment = trim((string)($data['comment'] ?? ''));
        $markerId = clean_marker_id((string)($data['marker_id'] ?? ''));

        if ($comment === '') {
            json_response(['ok' => false, 'message' => 'Feedback comment is required.'], 422);
        }

        $buildingId = null;
        if ($markerId !== '') {
            $stmt = db()->prepare('SELECT building_id FROM buildings WHERE marker_id = ? LIMIT 1');
            $stmt->execute([$markerId]);
            $buildingId = $stmt->fetchColumn() ?: null;
        }

        $stmt = db()->prepare('INSERT INTO feedback (user_name, building_id, comment) VALUES (?, ?, ?)');
        $stmt->execute([$userName !== '' ? $userName : 'Guest', $buildingId, $comment]);

        json_response(['ok' => true, 'message' => 'Feedback submitted.']);
    }

    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
} catch (Throwable $error) {
    json_response(['ok' => false, 'message' => $error->getMessage()], 500);
}
