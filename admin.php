<?php
declare(strict_types=1);
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    http_response_code(403); exit('Для административной панели требуется HTTPS.');
}
session_name('sat_drive_admin');
session_set_cookie_params(['httponly'=>true,'secure'=>true,'samesite'=>'Strict','path'=>'/']);
session_start();
$configFile = dirname(__DIR__) . '/sat-test-drive-config.php';
if (!is_file($configFile)) { http_response_code(503); exit('Сайт не настроен.'); }
$config = require $configFile;
function esc(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function db(): PDO {
 global $config;
 return new PDO('mysql:host='.$config['db_host'].';dbname='.$config['db_name'].';charset=utf8mb4',
 $config['db_user'],$config['db_password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
}
if (!isset($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if (!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??''))) {http_response_code(403);exit('Неверный CSRF-токен.');}
 if (isset($_POST['logout'])) {$_SESSION=[];session_destroy();header('Location: admin.php');exit;}
 if (empty($_SESSION['admin'])) {
   $hash=(string)($config['admin_password_hash']??'');
   if ($hash!=='' && password_verify((string)($_POST['password']??''),$hash)) {
     session_regenerate_id(true);$_SESSION['admin']=true;header('Location: admin.php');exit;
   }
   usleep(400000);$error='Неверный пароль.';
 } else {
   $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
   $status=(string)($_POST['status']??'');
   if (!$id || !in_array($status,['waiting','invited','completed','cancelled'],true)) $error='Некорректный статус.';
   else {
     $stmt=db()->prepare('UPDATE registrations SET status=? WHERE id=?');
     $stmt->execute([$status,$id]);
     header('Location: admin.php');exit;
   }
 }
}
?><!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Администратор — SAT Test Drive</title><style>
body{margin:0;font:15px Arial,sans-serif;background:#eff0e7;color:#151715}.wrap{max-width:1100px;margin:35px auto;padding:24px}h1{font-size:40px;letter-spacing:-.04em}input,select,button{padding:10px;font:inherit;border:1px solid #bbb;border-radius:8px}button{cursor:pointer;background:#d1e000;border:0;font-weight:700}table{border-collapse:collapse;width:100%;background:white;border-radius:14px;overflow:hidden}th,td{padding:13px;text-align:left;border-bottom:1px solid #ddd}th{font-size:12px;color:#666}form.inline{display:flex;gap:7px;align-items:center}.error{color:#b20d24}@media(max-width:750px){table{display:block;overflow-x:auto;white-space:nowrap}}
</style></head><body><main class="wrap"><h1>SAT / Управление очередью</h1>
<?php if ($error): ?><p class="error"><?=esc($error)?></p><?php endif; ?>
<?php if (empty($_SESSION['admin'])): ?>
<p>Введите пароль администратора.</p><form method="post"><input type="hidden" name="csrf" value="<?=esc($_SESSION['csrf'])?>"><input type="password" name="password" required autocomplete="current-password"><button>Войти</button></form>
<?php else:
$rows=db()->query('SELECT id, model, full_name, phone, email, status, created_at FROM registrations ORDER BY id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
?>
<form method="post"><input type="hidden" name="csrf" value="<?=esc($_SESSION['csrf'])?>"><button name="logout" value="1">Выйти</button></form>
<p>Последние 200 заявок. Изменение статуса пересчитывает позицию ожидающих в соответствующей очереди.</p>
<table><thead><tr><th>№</th><th>Модель</th><th>ФИО</th><th>Телефон</th><th>Почта</th><th>Создана (UTC)</th><th>Статус</th></tr></thead><tbody>
<?php foreach($rows as $row): ?><tr>
<td><?=esc($row['id'])?></td><td><?=esc($row['model'])?></td><td><?=esc($row['full_name'])?></td><td><?=esc($row['phone'])?></td><td><?=esc($row['email'])?></td><td><?=esc($row['created_at'])?></td>
<td><form method="post" class="inline"><input type="hidden" name="csrf" value="<?=esc($_SESSION['csrf'])?>"><input type="hidden" name="id" value="<?=esc($row['id'])?>"><select name="status">
<?php foreach (['waiting'=>'Ожидает','invited'=>'Приглашён','completed'=>'Завершён','cancelled'=>'Отменён'] as $value=>$label): ?>
<option value="<?=esc($value)?>" <?= $row['status']===$value?'selected':'' ?>><?=esc($label)?></option>
<?php endforeach; ?></select><button>Сохранить</button></form></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?></main></body></html>