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

// Получаем ФИО из любой возможной колонки
$nameValue = $user['fio'] ?? $user['full_name'] ?? $user['fullName'] ?? $user['name'] ?? 'Не указано';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <base href="/8/public/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <h2 class="header_hame">Коты!</h2>
        <nav class="header_nav">
            <a href="/8/public/" class="nav_link">Главная</a>
            <a href="/8/public/profile?id=<?= (int)$user['id'] ?>" class="nav_link">Профиль</a>
        </nav>
        <img src="menu.svg" id="menu" alt="Меню">
    </header>

    <div class="mob_nav" id="mobnav">
        <img src="menu.svg" width="30%" id="menu2" alt="Закрыть">
        <a href="/8/public/" class="nav_link_mob">Главная</a>
        <a href="/8/public/profile?id=<?= (int)$user['id'] ?>" class="nav_link_mob">Профиль</a>
    </div>

    <div class="third_screen">
        <div class="profile-card">
            <h1 class="screen_name">Профиль #<?= (int)$user['id'] ?></h1>
            
            <?php if ($flash): ?>
                <div class="success-box"><?= htmlspecialchars($flash) ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="error-box"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
            <?php endif; ?>

            <div class="profile-info">
                <div class="info-row"><span class="info-label">Логин:</span> <code><?= htmlspecialchars($user['login'] ?? '—') ?></code></div>
                <div class="info-row"><span class="info-label">Пароль:</span> <code><?= htmlspecialchars($user['password'] ?? '—') ?></code></div>
                <div class="info-row"><span class="info-label">ФИО:</span> <?= htmlspecialchars($nameValue) ?></div>
                <div class="info-row"><span class="info-label">Email:</span> <?= htmlspecialchars($user['email'] ?? '—') ?></div>
                <div class="info-row"><span class="info-label">Телефон:</span> <?= htmlspecialchars($user['phone'] ?? '—') ?></div>
                <div class="info-row"><span class="info-label">Организация:</span> <?= htmlspecialchars($user['organization'] ?? '—') ?></div>
                <div class="info-row"><span class="info-label">Сообщение:</span><br><p><?= nl2br(htmlspecialchars($user['message'] ?? '')) ?></p></div>
            </div>

            <?php if ($canEdit): ?>
                <button id="btn_form" class="form_btn">Редактировать</button>
                <a href="/8/public/" class="back-link">← На главную</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($canEdit): ?>
    <div id="bloom"></div>
    <div id="edit-modal" data-user-id="<?= (int)$user['id'] ?>">
        <h3 class="screen_name">Редактирование</h3>
        <div id="message-container"></div>
        <form id="contactForm">
            <div class="form-group">
                <label>ФИО *</label>
                <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($nameValue) ?>" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            <div class="form-group">
                <label>Организация</label>
                <input type="text" id="organization" name="organization" value="<?= htmlspecialchars($user['organization']) ?>">
            </div>
            <div class="form-group">
                <label>Сообщение *</label>
                <textarea id="message" name="message" rows="4" required><?= htmlspecialchars($user['message']) ?></textarea>
            </div>
            <!-- Чекбокс для единообразия, но не валидируется при обновлении -->
            <div class="checkbox-container">
                <input type="checkbox" id="privacy" name="privacy" checked disabled>
                <label for="privacy">Согласие на обработку данных (сохранено при регистрации)</label>
            </div>
            <button type="submit" id="submit_form" class="form_btn">Сохранить изменения</button>
        </form>
    </div>
    <script src="main.js"></script>
    <?php endif; ?>

    <script src="menu.js"></script>
</body>
</html>
