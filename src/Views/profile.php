<?php
if (!isset($_SESSION)) session_start();

$userId = (int)($_GET['id'] ?? $_SESSION['user_id'] ?? 0);
if (!$userId) {
    header('Location: /8/public/');
    exit;
}

require_once __DIR__ . '/../Models/User.php';
use App\Models\User;

$userModel = new User();
$user = $userModel->getById($userId);

if (!$user) {
    die("Пользователь не найден. <a href='/8/public/'>На главную</a>");
}

$canEdit = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId;

$flash = $_SESSION['flash_message'] ?? '';
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['flash_message'], $_SESSION['form_errors']);

$nameValue = $user['fio'] ?? $user['full_name'] ?? $user['fullName'] ?? $user['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="/8/public/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .card { background: #fff; max-width: 600px; margin: 0 auto; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.1); }
        .field { margin: 12px 0; padding-bottom: 12px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #666; font-size: 14px; }
        .value { margin-top: 4px; word-break: break-word; }
        .btn { background: #3498db; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        #bloom, #form-container { display: none; }
        #bloom.on { display: block; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 998; }
        #form-container.on { display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); background: #fff; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; z-index: 999; box-shadow: 0 5px 20px rgba(0,0,0,.3); }
        .form-group { margin: 12px 0; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
    </style>
</head>
<body>
<div class="card">
    <h1>Профиль #<?= (int)$user['id'] ?></h1>
    
    <?php if ($flash): ?>
        <div class="alert success"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <div class="field"><div class="label">Логин</div><div class="value"><code><?= htmlspecialchars($user['login']) ?></code></div></div>
    <div class="field"><div class="label">Пароль</div><div class="value"><code><?= htmlspecialchars($user['password']) ?></code></div></div>
    <div class="field"><div class="label">ФИО</div><div class="value"><?= htmlspecialchars($nameValue) ?></div></div>
    <div class="field"><div class="label">Email</div><div class="value"><?= htmlspecialchars($user['email']) ?></div></div>
    <div class="field"><div class="label">Телефон</div><div class="value"><?= htmlspecialchars($user['phone'] ?: '—') ?></div></div>
    <div class="field"><div class="label">Организация</div><div class="value"><?= htmlspecialchars($user['organization'] ?: '—') ?></div></div>
    <div class="field"><div class="label">Сообщение</div><div class="value"><?= nl2br(htmlspecialchars($user['message'])) ?></div></div>

    <?php if ($canEdit): ?>
        <button id="btn_form" class="btn">Редактировать</button>
    <?php endif; ?>
    <a href="/8/public/" style="margin-left: 15px; color: #666">← На главную</a>
</div>

<?php if ($canEdit): ?>
<div id="bloom"></div>
<div id="form-container" data-user-id="<?= (int)$user['id'] ?>">
    <h3>Редактирование</h3>
    <div id="message-container"></div>
    <form id="contactForm">
        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
        <div class="form-group"><label>ФИО *</label><input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($nameValue) ?>" required></div>
        <div class="form-group"><label>Email *</label><input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></div>
        <div class="form-group"><label>Телефон</label><input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"></div>
        <div class="form-group"><label>Организация</label><input type="text" id="organization" name="organization" value="<?= htmlspecialchars($user['organization']) ?>"></div>
        <div class="form-group"><label>Сообщение *</label><textarea id="message" name="message" rows="4" required><?= htmlspecialchars($user['message']) ?></textarea></div>
        <button type="submit" id="submit_form" class="btn">Сохранить</button>
    </form>
</div>
<script src="/8/public/main.js"></script>
<?php endif; ?>
</body>
</html>
