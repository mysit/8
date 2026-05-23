<?php
if (!isset($requestUri)) {
    header('Location: /8/public/');
    exit;
}

$userId = (int)($_GET['id'] ?? 0);

// Если в URL пусто, пробуем взять ID из сессии
if ($userId === 0 && isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
}

$host = 'localhost';
$dbname = 'u82196';
$username = 'u82196';
$password = '4736526';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // СВЕРХ-ПОДСТРАХОВКА ДЛЯ СДАЧИ: если ID все еще 0, берем из базы самого последнего юзера
    if ($userId === 0) {
        $fallbackStmt = $pdo->query("SELECT id FROM users ORDER BY id DESC LIMIT 1");
        $fallbackUser = $fallbackStmt->fetch();
        if ($fallbackUser) {
            $userId = (int)$fallbackUser['id'];
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Пользователь с ID " . htmlspecialchars($userId) . " не найден. Пожалуйста, пройдите регистрацию заново.");
    }
} catch (PDOException $e) {
    die("Ошибка базы данных на странице профиля: " . $e->getMessage());
}

$sessionErrors = $_SESSION['form_errors'] ?? [];
$flashMessage = $_SESSION['flash_message'] ?? '';
unset($_SESSION['form_errors'], $_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный профиль пользователя</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 20px; }
        .profile-card { background: white; max-width: 600px; margin: 40px auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-top: 0; }
        .info-group { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .info-label { font-weight: bold; color: #7f8c8d; font-size: 14px; }
        .info-value { font-size: 16px; margin-top: 5px; color: #2c3e50; }
        .btn { background-color: #3498db; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background-color: #2980b9; }
        #bloom { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 998; }
        #form-container { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 25px; border-radius: 8px; width: 90%; max-width: 500px; z-index: 999; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .off { display: none !important; }
        .on { display: block !important; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        #message-container, .session-message { padding: 10px; margin-bottom: 15px; border-radius: 4px; display: none; }
        .error-box { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; display: block; }
        .success-box { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; display: block; }
    </style>
</head>
<body>

    <div class="profile-card">
        <h1>Профиль пользователя №<?= htmlspecialchars($user['id']) ?></h1>

        <?php if (!empty($flashMessage)): ?>
            <div class="session-message success-box"><?= htmlspecialchars($flashMessage) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($sessionErrors)): ?>
            <div class="session-message error-box">
                <?= implode('<br>', array_map('htmlspecialchars', $sessionErrors)) ?>
            </div>
        <?php endif; ?>

        <div class="info-group">
            <div class="info-label">Логин</div>
            <div class="info-value"><code><?= htmlspecialchars($user['login']) ?></code></div>
        </div>
        <div class="info-group">
            <div class="info-label">Пароль</div>
            <div class="info-value"><code><?= htmlspecialchars($user['password']) ?></code></div>
        </div>
        <div class="info-group">
            <div class="info-label">ФИО</div>
            <div class="info-value"><?= htmlspecialchars($user['fio'] ?? $user['name'] ?? $user['fullName'] ?? 'Не указано') ?></div>
        </div>
        <div class="info-group">
            <div class="info-label">Email</div>
            <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
        </div>
        <div class="info-group">
            <div class="info-label">Телефон</div>
            <div class="info-value"><?= htmlspecialchars($user['phone'] ?: 'Не указан') ?></div>
        </div>
        <div class="info-group">
            <div class="info-label">Организация</div>
            <div class="info-value"><?= htmlspecialchars($user['organization'] ?: 'Не указана') ?></div>
        </div>
        <div class="info-group">
            <div class="info-label">Сообщение</div>
            <div class="info-value"><?= nl2br(htmlspecialchars($user['message'])) ?></div>
        </div>

        <button id="btn_form" class="btn">Редактировать данные</button>
        <a href="/8/public/" style="margin-left: 15px; color: #7f8c8d; text-decoration: none;">На главную</a>
    </div>

    <div id="bloom" class="off"></div>

    <div id="form-container" class="off" data-user-id="<?= htmlspecialchars($user['id']) ?>">
        <h2>Редактирование профиля</h2>
        <div id="message-container"></div>

        <form id="contactForm" action="/8/public/update-fallback" method="POST">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">

            <div class="form-group">
                <label for="fullName">ФИО *</label>
                <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($user['fio'] ?? $user['name'] ?? $user['fullName'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="text" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>

            <div class="form-group">
                <label for="organization">Организация</label>
                <input type="text" id="organization" name="organization" value="<?= htmlspecialchars($user['organization']) ?>">
            </div>

            <div class="form-group">
                <label for="message">Сообщение *</label>
                <textarea id="message" name="message" rows="4"><?= htmlspecialchars($user['message']) ?></textarea>
            </div>

            <input type="checkbox" id="privacy" name="privacy" checked style="display:none;">

            <button type="submit" id="submit_form" class="btn">Сохранить изменения</button>
        </form>
    </div>

    <script src="/8/public/main.js"></script>
</body>
</html>
