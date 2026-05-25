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
    <base href="/8/public/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Коты — форма заявки</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Unbounded:wght@200..900&display=swap" rel="stylesheet">
</head>
<body>
    <video autoplay muted loop playsinline class="video-bg">
        <source src="https://www.shutterstock.com/shutterstock/videos/3810007647/preview/stock-footage-adorable-striped-kittens-relaxing-on-a-summer-day-kittens-with-blue-eyes-in-a-basket-amidst.webm" type="video/mp4">
    </video>

    <div class="mob_nav" id="mobnav">
        <img src="menu.svg" width="33%" id="menu2" alt="">
        <a href="/8/public/" class="nav_link_mob">Главная</a>
        <a href="#fsc" class="nav_link_mob">Котики</a>
        <a href="#ss" class="nav_link_mob">Галерея</a>
        <a href="#ts" class="nav_link_mob">Контакты</a>
    </div>

    <div class="header">
        <h2 class="header_hame">Коты!</h2>
        <nav id="nav_head" class="header_nav">
            <a href="/8/public/" class="nav_link">Главная</a>
            <a href="#fsc" class="nav_link">Котики</a>
            <a href="#ss" class="nav_link">Галерея</a>
            <a href="#ts" class="nav_link">Контакты</a>
        </nav>
        <img src="menu.svg" id="menu" alt="">
    </div>

    <div class="fs">
        <div class="first_screen">
            <h1 class="main_header">Коты</h1>
            <p class="main_subheader">Возьмите котенка из приюта и пусть ваш дом станет радостнее!</p>
            <button onclick="scrollToAnchor('ts')" class="main_btn">Оставить заявку</button>
        </div>
    </div>

    <div class="sec_screen" id="fsc">
        <h2 class="screen_name">Наши котики</h2>
        <div class="cards">
            <div class="card"><h2 class="screen_name">Разные породы</h2><p class="card_text">Мы имеем более 50 кошек разных пород, обязательно найдете то, что нужно!</p></div>
            <div class="card"><h2 class="screen_name">Здоровые котики</h2><p class="card_text">Наши котики проходят все медицинские услуги, необходимые для комфортной жизни здорового котика!</p></div>
            <div class="card"><h2 class="screen_name">Поддержка клиентов</h2><p class="card_text">Наша команда поможет вам со всеми вопросами касательно котиков!!!</p></div>
            <div class="card"><h2 class="screen_name">Быстрая доставка</h2><p class="card_text">Самая быстрая доставка в пункты выдачи или курьером</p></div>
        </div>
        <div class="card" id="card5"><h2 class="screen_name">Лучшие котики</h2><p class="card_text">Наши котики самые лучшие на свете!</p></div>
    </div>

    <div class="slider-container" id="ss">
        <h2 class="screen_name">Самые красивые лапки!!!</h2>
        <div class="slider">
            <div class="slide"><img src="https://avatars.mds.yandex.net/i?id=ac607ce1d2fab58317c5ad3c2d312266_l-5480701-images-thumbs&n=13" alt="cat"></div>
            <div class="slide"><img src="https://avatars.mds.yandex.net/i?id=93c45f1acea97c9a643c2bb932e6372f8a06209d-8439108-images-thumbs&n=13" alt="cat"></div>
            <div class="slide"><img src="https://i.pinimg.com/originals/c1/bf/93/c1bf937c4d9297010114467a93ebd67e.jpg" alt="cat"></div>
            <div class="slide"><img src="https://i.pinimg.com/736x/9c/0c/76/9c0c764aa9649ee19fa67e9d6d0f7a48.jpg" alt="cat"></div>
            <div class="slide"><img src="https://i.pinimg.com/originals/02/ac/a9/02aca9d3af07aa8118e4e60d37af94d0.jpg" alt="cat"></div>
            <div class="slide"><img src="https://i.pinimg.com/originals/bd/36/21/bd3621523dfe8fe653ba09ce70948218.jpg" alt="cat"></div>
            <div class="slide"><img src="https://www.shutterstock.com/image-photo/ginger-cat-walks-on-white-600nw-2135552857.jpg" alt="cat"></div>
            <div class="slide"><img src="https://i.pinimg.com/originals/0d/57/4d/0d574dee69b01c818f2d973510d7dd90.jpg" alt="cat"></div>
        </div>
        <button class="btn btn-prev">&#10094;</button>
        <button class="btn btn-next">&#10095;</button>
        <div class="pager">
            <div class="pager-text">Страница <span id="current-page">1</span> из <span id="total-pages">3</span></div>
            <div class="pager-dots"></div>
        </div>
    </div>

    <div class="third_screen" id="ts">
        <h2 class="screen_name">Оставьте заявку на получение животного</h2>
        <div class="form" id="form-container">
            <form id="contactForm" action="/8/public/register-fallback" method="POST">
                <?php if (!empty($errors)): ?>
                    <div class="error-box"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="fullName">ФИО *</label>
                    <input type="text" id="fullName" name="fullName" required placeholder="Введите ваше полное имя" value="<?= htmlspecialchars($old['fullName'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required placeholder="example@domain.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" id="phone" name="phone" placeholder="+7 (XXX) XXX-XX-XX" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="organization">Организация</label>
                    <input type="text" id="organization" name="organization" placeholder="Название организации" value="<?= htmlspecialchars($old['organization'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="message">Сообщение *</label>
                    <textarea id="message" name="message" required placeholder="Опишите ваш вопрос или предложение..."><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                </div>
                
                <div class="checkbox-container">
                    <input type="checkbox" id="privacy" name="privacy" required <?= isset($old['privacy']) ? 'checked' : '' ?>>
                    <label for="privacy">Я согласен с политикой обработки персональных данных</label>
                </div>
                
                <button type="submit" id="submit_form" class="form_btn">отправить форму</button>
            </form>
        </div>
    </div>

    <footer>
        <div class="foo_block"><h3>Коты</h3><p>служба доставки котиков по всей россии</p></div>
        <div class="foo_block"><h3>Контакты</h3><p>г.Краснодар, ул.Котовского</p><p>+7 (495) 123-45-67</p><p>info@kotiki.ru</p></div>
    </footer>

    <script src="menu.js"></script>
    <script src="slider.js"></script>
    <script src="main.js"></script>
    <script>
    function scrollToAnchor(id){const el=document.getElementById(id);if(el)el.scrollIntoView({behavior:'smooth',block:'start'});}
    </script>
</body>
</html>
