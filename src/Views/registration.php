<?php
if (!isset($_SESSION)) session_start();
if (!defined('ENTRY_POINT')) {
    $target = '/8/public/index.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
    header('Location: ' . $target);
    exit;
}
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_data']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форма обратной связи</title>
    <link rel="stylesheet" href="/8/public/style.css">
</head>
<body>
<div class="main_screen">
    <h1>Форма обратной связи</h1>
    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Вы авторизованы. <a href="/8/public/profile?id=<?= $_SESSION['user_id'] ?>">Перейти в профиль</a></p>
    <?php endif; ?>
</div>

<div class="bloom" id="bloom"></div>
<div class="form" id="form-container">
    <form id="contactForm" action="/8/public/register-fallback" method="POST">
        <?php if (!empty($errors)): ?>
            <div style="background:#f8d7da;color:#721c24;padding:10px;margin:10px 0;border-radius:4px">
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>ФИО *</label>
            <input type="text" name="fullName" id="fullName" required value="<?= htmlspecialchars($old['fullName'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" id="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Телефон</label>
            <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Организация</label>
            <input type="text" name="organization" id="organization" value="<?= htmlspecialchars($old['organization'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Сообщение *</label>
            <textarea name="message" id="message" required><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
        </div>
        <div class="checkbox-container">
            <input type="checkbox" name="privacy" id="privacy" required <?= isset($old['privacy']) ? 'checked' : '' ?>>
            <label for="privacy">Согласие на обработку персональных данных</label>
        </div>
        <button type="submit" id="submit_form" class="form_btn">Отправить форму</button>
    </form>
</div>

<script src="/8/public/main.js"></script>
</body>
</html>
