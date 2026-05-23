<?php
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_data']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Страница</title>
    <link rel="stylesheet" href="/8/public/style.css">
</head>
<body>
    <div class="main_screen">
        <h1 class="title">Форма обратной связи</h1>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <p style="margin-top:20px;">Вы уже авторизованы. <a href="/profile?id=<?= $_SESSION['user_id'] ?>">Перейти в профиль</a></p>
        <?php endif; ?>
    </div>
    
    <div class="bloom" id="bloom"></div>
    
    <div class="form" id="form-container" data-user-id="<?= $_SESSION['user_id'] ?? '' ?>">
        <form action="/8/public/update-fallback" method="POST">
<input type="hidden" name="_method" value="PUT">
            
            <div id="message-container" style="margin: 10px 0; padding: 10px; border-radius: 5px; display: <?= !empty($errors) ? 'block' : 'none' ?>; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                <?php if(!empty($errors)) echo implode('<br>', $errors); ?>
            </div>

            <div class="form-group">
                <label for="fullName" class="required">ФИО</label>
                <input type="text" id="fullName" name="fullName" required placeholder="Введите ваше полное имя" value="<?= htmlspecialchars($old['fullName'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email" class="required">Email</label>
                <input type="email" id="email" name="email" required placeholder="example@domain.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" placeholder="+7 (XXX) XXX-XX-XX" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="organization">Организация</label>
                <input type="text" id="organization" name="organization" placeholder="Название вашей организации" value="<?= htmlspecialchars($old['organization'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="message" class="required">Сообщение</label>
                <textarea id="message" name="message" required placeholder="Опишите ваш вопрос или предложение..."><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
            </div>

            <div class="checkbox-container">
                <input type="checkbox" id="privacy" name="privacy" required <?= isset($old['privacy']) ? 'checked' : '' ?>>
                <label for="privacy">Я согласен с политикой обработки персональных данных</label>
            </div>
            
            <button type="submit" id="submit_form" class="form_btn">отправить форму</button>
        </form>
    </div>
    <script src="/8/public/main.js"></script>
</body>
</html>
