<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, ['error'=>'Метод не разрешён.']);
if (!isSameOrigin()) respond(403, ['error'=>'Недопустимый источник запроса.']);
if (strlen(file_get_contents('php://input', false, null, 0, 8193)) > 8192) respond(413, ['error'=>'Слишком большой запрос.']);
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) respond(400, ['error'=>'Некорректные данные.']);
$model = (string)($data['model'] ?? '');
$name = trim((string)($data['name'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
if (!in_array($model, ['SAT-500','SAT-545'], true) ||
    mb_strlen($name) < 5 || mb_strlen($name) > 180 ||
    !preg_match('/^[+0-9()\\s-]{10,40}$/', $phone) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 200 ||
    ($data['consent'] ?? false) !== true) {
    respond(422, ['error'=>'Проверьте поля анкеты и согласие.']);
}
$token = bin2hex(random_bytes(32));
$db = database();
try {
    $stmt = $db->prepare('INSERT INTO registrations (public_token,model,full_name,phone,email,consent_at) VALUES (?,?,?,?,?,UTC_TIMESTAMP())');
    $stmt->execute([$token,$model,$name,$phone,$email]);
    $id = (int)$db->lastInsertId();
    respond(201, ['token'=>$token,'number'=>$id,'model'=>$model,'status'=>'waiting','url'=>'queue.html?token='.$token]);
} catch (Throwable $e) {
    error_log('Registration insert failed');
    respond(503, ['error'=>'Не удалось сохранить заявку. Попробуйте позднее.']);
}
