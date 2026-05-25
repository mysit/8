<?php
if (!isset($_SESSION)) session_start();
$userId = (int)($_GET['id'] ?? $_SESSION['user_id'] ?? 0);
if (!$userId) { header('Location: /8/public/'); exit; }

require_once __DIR__ . '/../Models/User.php';
use App\Models\User;
$userModel = new User();
$user = $userModel->getById($userId);
if (!$user) { die("Пользователь не найден. <a href='/8/public/'>На главную</a>"); }

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
    <base href="/8/public/">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card" style="background:#fff;max-width:600px;margin:40px auto;padding:25px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1)">
        <h1>Профиль #<?= (int)$user['id'] ?></h1>
        <?php if ($flash): ?><div class="success-box"><?= htmlspecialchars($flash) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="error-box"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div><?php endif; ?>
        <div style="margin:12px 0;padding-bottom:12px;border-bottom:1px solid #eee"><strong>Логин:</strong> <code><?= htmlspecialchars($user['login']) ?></code></div>
        <div style="margin:12px 0;padding-bottom:12px;border-bottom:1px solid #eee"><strong>Пароль:</strong> <code><?= htmlspecialchars($user['password']) ?></code></div>
        <div style="margin:12px 0;padding-bottom:12px;border-bottom:1px solid #eee"><strong>ФИО:</strong> <?= htmlspecialchars($nameValue) ?></div>
        <div style="margin:12px 0;padding-bottom:12px;border-bottom:1px solid #eee"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
        <div style="margin:12px 0;padding-bottom:12px;border-bottom:1px solid #eee"><strong>Телефон:</strong> <?= htmlspecialchars($user['phone'] ?: '—') ?></div>
        <div style="margin:12px 0;padding-bottom:12px;border-bottom:1px solid #eee"><strong>Организация:</strong> <?= htmlspecialchars($user['organization'] ?: '—') ?></div>
        <div style="margin:12px 0;padding-bottom:12px;border-bottom:1px solid #eee"><strong>Сообщение:</strong><br><?= nl2br(htmlspecialchars($user['message'])) ?></div>
        <?php if ($canEdit): ?>
            <button id="btn_form" class="form_btn">Редактировать</button>
            <a href="/8/public/" style="margin-left:15px;color:#666">← На главную</a>
        <?php endif; ?>
    </div>

    <?php if ($canEdit): ?>
    <div id="bloom"></div>
    <div id="form-container" data-user-id="<?= (int)$user['id'] ?>" class="on">
        <h3>Редактирование</h3>
        <div id="message-container"></div>
        <form id="contactForm">
            <div class="form-group"><label>ФИО *</label><input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($nameValue) ?>" required></div>
            <div class="form-group"><label>Email *</label><input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></div>
            <div class="form-group"><label>Телефон</label><input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"></div>
            <div class="form-group"><label>Организация</label><input type="text" id="organization" name="organization" value="<?= htmlspecialchars($user['organization']) ?>"></div>
            <div class="form-group"><label>Сообщение *</label><textarea id="message" name="message" rows="4" required><?= htmlspecialchars($user['message']) ?></textarea></div>
            <button type="submit" id="submit_form" class="form_btn">Сохранить</button>
        </form>
    </div>
    <script src="/8/public/main.js"></script>
    <?php endif; ?>
</body>
</html>
