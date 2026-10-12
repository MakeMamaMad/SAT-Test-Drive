<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(405, ['error'=>'Метод не разрешён.']);
$token = (string)($_GET['token'] ?? '');
if (!preg_match('/^[a-f0-9]{64}$/', $token)) respond(400,['error'=>'Неверная ссылка.']);
$db = database();
$stmt = $db->prepare('SELECT id,model,status,created_at FROM registrations WHERE public_token=? LIMIT 1');
$stmt->execute([$token]);
$row = $stmt->fetch();
if (!$row) respond(404,['error'=>'Запись не найдена.']);
$position = null;
if ($row['status'] === 'waiting') {
    $q = $db->prepare("SELECT COUNT(*) FROM registrations WHERE model=? AND status='waiting' AND id<=?");
    $q->execute([$row['model'], $row['id']]);
    $position = (int)$q->fetchColumn();
}
respond(200,['number'=>(int)$row['id'],'model'=>$row['model'],'status'=>$row['status'],'position'=>$position,'ahead'=>$position === null ? null : $position-1]);
