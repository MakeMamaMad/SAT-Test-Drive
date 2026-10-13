<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, ['error'=>'Метод не разрешён.']);
if (!isSameOrigin()) respond(403, ['error'=>'Недопустимый источник запроса.']);
$rawBody = file_get_contents('php://input', false, null, 0, 8193);
if ($rawBody === false || strlen($rawBody) > 8192) respond(413, ['error'=>'Слишком большой запрос.']);
$data = json_decode($rawBody, true);
if (!is_array($data)) respond(400, ['error'=>'Некорректные данные.']);
$model = (string)($data['model'] ?? '');
$axles = (int)($data['axles'] ?? 0);
$name = trim((string)($data['name'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
if (!in_array($model, ['SAT-500','SAT-545'], true) || !in_array($axles, [3,4], true) ||
    mb_strlen($name) < 5 || mb_strlen($name) > 180 ||
    !preg_match('/^[+0-9()\\s-]{10,40}$/', $phone) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 200 ||
    ($data['consent'] ?? false) !== true) {
    respond(422, ['error'=>'Проверьте поля анкеты и согласие.']);
}
$token = bin2hex(random_bytes(32));
$db = database();
try {
    $stmt = $db->prepare('INSERT INTO registrations (public_token,model,axles,full_name,phone,email,consent_at) VALUES (?,?,?,?,?,?,UTC_TIMESTAMP())');
    $stmt->execute([$token,$model,$axles,$name,$phone,$email]);
    $id = (int)$db->lastInsertId();
    // Email delivery is best effort and must never interrupt a saved registration.
    $recipient = trim((string)($config['notification_email'] ?? ''));
    if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $sender = trim((string)($config['mail_from'] ?? ''));
        if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            $subject = 'SAT Test Drive - новая заявка #' . $id;
            $message = "Новая заявка на тест-драйв SAT\n\n"
                . "Номер: #" . $id . "\n"
                . "Модель: " . $model . "\n"
                . "Количество осей: " . $axles . "\n"
                . "ФИО: " . $name . "\n"
                . "Телефон: " . $phone . "\n"
                . "Email: " . $email . "\n";
            $headers = [
                'From' => $sender,
                'Content-Type' => 'text/plain; charset=UTF-8',
                'MIME-Version' => '1.0',
                'X-Mailer' => 'SAT-Test-Drive'
            ];
            try {
                if (!mail($recipient, $subject, $message, $headers)) {
                    error_log('SAT Test Drive: mail() returned false for registration #' . $id);
                }
            } catch (Throwable $mailError) {
                error_log('SAT Test Drive: mail() failed for registration #' . $id);
            }
        } else {
            error_log('SAT Test Drive: mail_from is missing or invalid');
        }
    }
    respond(201, ['token'=>$token,'number'=>$id,'model'=>$model,'axles'=>$axles,'status'=>'waiting','url'=>'queue.html?token='.$token]);
} catch (Throwable $e) {
    error_log('Registration insert failed');
    respond(503, ['error'=>'Не удалось сохранить заявку. Попробуйте позднее.']);
}
