<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
$configFile = dirname(__DIR__, 2) . '/sat-test-drive-config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    echo json_encode(['error' => 'Регистрация пока не подключена.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$config = require $configFile;
function respond(int $status, array $data): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function database(): PDO {
    global $config;
    try {
        return new PDO('mysql:host='.$config['db_host'].';dbname='.$config['db_name'].';charset=utf8mb4',
            $config['db_user'], $config['db_password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
    } catch (Throwable $e) {
        error_log('Database connection error');
        respond(503, ['error'=>'Сервис временно недоступен.']);
    }
}
function isSameOrigin(): bool {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin === '') return true;
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $parts = parse_url($origin);
    return is_array($parts) && strtolower($parts['host'] ?? '') === strtolower(explode(':', $host)[0])
        && ($parts['scheme'] ?? '') === 'https';
}
