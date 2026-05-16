<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
    }

    $data = request_json();
    $username = trim((string)($data['username'] ?? ''));
    $password = (string)($data['password'] ?? '');

    $stmt = db()->prepare('SELECT admin_id, username, password_hash, full_name FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, (string)$admin['password_hash'])) {
        json_response(['ok' => false, 'message' => 'Invalid username or password.'], 401);
    }

    json_response([
        'ok' => true,
        'admin' => [
            'admin_id' => (int)$admin['admin_id'],
            'username' => $admin['username'],
            'full_name' => $admin['full_name'],
        ],
    ]);
} catch (Throwable $error) {
    json_response(['ok' => false, 'message' => $error->getMessage()], 500);
}
