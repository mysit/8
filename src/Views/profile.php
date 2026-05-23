<?php
use App\Models\User;
$userModel = new User();
$userId = (int)($_GET['id'] ?? 0);
$user = $userModel->findById($userId);

if (!$user) {
    die("Пользователь не найден.");
}

$isAuthorized = isset($_SESSION['user_id']) && $_SESSION['user_id'] === $userId;
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <base href="/8/public/">
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="<?= $scriptName ?? '/8/public' ?>/css/style.css">
</head>
<body>
    <div style="max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
        <h2>Профиль нового пользователя</h2>
        
        <?php if (isset($_SESSION['just_registered']) && $_SESSION['just_registered']['id'] == $userId): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <strong>Важные данные для входа (показаны 1 раз):</strong><br>
                Логин: <code><?= htmlspecialchars($_SESSION['just_registered']['login']) ?></code><br>
                Пароль: <code><?= htmlspecialchars($_SESSION['just_registered']['password']) ?></code>
            </div>
            <?php unset($_SESSION['just_registered']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;">
                <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            </div>
        <?php endif; ?>

        <p><strong>Ваш Логин:</strong> <?= htmlspecialchars($user['login']) ?></p>
        <p><strong>Адрес профиля:</strong> <code>/profile?id=<?= $user['id'] ?></code></p>
        
        <hr style="margin:20px 0;">

        <h3>Изменить данные (Кроме логина и пароля)</h3>
        
        <?php if ($isAuthorized): ?>
            <form id="contactForm" action="/update-fallback" method="POST">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                <div id="message-container" style="margin: 10px 0; padding: 10px; border-radius: 5px; display: <?= !empty($errors) ? 'block' : 'none' ?>; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                    <?php if(!empty($errors)) echo implode('<br>', $errors); ?>
                </div>

                <div class="form-group">
                    <label for="fullName">ФИО</label><br>
                    <input type="text" id="fullName" name="fullName" required value="<?= htmlspecialchars($user['full_name']) ?>" style="width:100%; padding:8px;">
                </div>

                <div class="form-group">
                    <label for="email">Email</label><br>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>" style="width:100%; padding:8px;">
                </div>

                <div class="form-group">
                    <label for="phone">Телефон</label><br>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" style="width:100%; padding:8px;">
                </div>

                <div class="form-group">
                    <label for="organization">Организация</label><br>
                    <input type="text" id="organization" name="organization" value="<?= htmlspecialchars($user['organization']) ?>" style="width:100%; padding:8px;">
                </div>

                <div class="form-group">
                    <label for="message">Сообщение</label><br>
                    <textarea id="message" name="message" required style="width:100%; height:100px; padding:8px;"><?= htmlspecialchars($user['message']) ?></textarea>
                </div>

                <button type="submit" id="submit_form" class="form_btn" style="margin-top:15px;">Сохранить изменения</button>
            </form>
        <?php else: ?>
            <p style="color:red;">Вы не авторизованы как владелец этого профиля, редактирование запрещено.</p>
        <?php endif; ?>
        
        <p style="margin-top:20px;"><a href="/">На главную форму</a></p>
    </div>

    <script src="<?= $scriptName ?? '/8/public' ?>/js/main.js"></script>
</body>
</html>
