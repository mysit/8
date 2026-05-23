<?php
session_start();

// Самодельный автолоад классов (встроенные средства языка)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) { require $file; }
});

use App\Services\Validator;
use App\Models\User;

// --- АДАПТАЦИЯ ПОД СЕРВЕР КУБГУ (Очистка подпапок) ---
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Находим, где в пути находится public/index.php или просто public/
$scriptName = dirname($_SERVER['SCRIPT_NAME']); // Получим "/8/public"
if ($scriptName !== '/' && strpos($requestUri, $scriptName) === 0) {
    // Вырезаем базовый путь из запроса, чтобы получить чистый "/" или "/profile"
    $requestUri = substr($requestUri, strlen($scriptName));
}
if (empty($requestUri)) {
    $requestUri = '/';
}
// -----------------------------------------------------

$requestMethod = $_SERVER['REQUEST_METHOD'];

// Поддержка эмуляции методов PUT через POST формы (для fallback-режима)
if ($requestMethod === 'POST' && isset($_POST['_method'])) {
    $requestMethod = strtoupper($_POST['_method']);
}

// Считываем данные в зависимости от формата (JSON или обычный POST)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$inputData = [];
if (str_contains($contentType, 'application/json')) {
    $inputData = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $inputData = $_POST;
}

try {
    $userModel = new User();
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения к БД: ' . $e->getMessage()]);
    exit;
}

// --- МАРШРУТЫ REST API ---

// 1. POST /api/users — Регистрация нового (неавторизованного) пользователя
if ($requestUri === '/api/users' && $requestMethod === 'POST') {
    header('Content-Type: application/json');
    $errors = Validator::validate($inputData);
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }
    $newUser = $userModel->create($inputData);
    $_SESSION['user_id'] = $newUser['id']; // Имитируем авторизацию
    
    echo json_encode([
        'status' => 'success',
        'login' => $newUser['login'],
        'password' => $newUser['password'],
        'profile_url' => $scriptName . '/profile?id=' . $newUser['id'] // Динамическая ссылка для КубГУ
    ]);
    exit;
}

// 2. PUT /api/users/{id} — Обновление данных авторизованного пользователя
if (preg_match('/^\/api\/users\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'PUT') {
    header('Content-Type: application/json');
    $userId = (int)$matches[1];

    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $userId) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен.']);
        exit;
    }

    $errors = Validator::validate($inputData);
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }

    $userModel->update($userId, $inputData);
    echo json_encode(['status' => 'success', 'message' => 'Данные обновлены по API!']);
    exit;
}

// --- МАРШРУТЫ ДЛЯ FALLBACK-РЕЖИМА (БЕЗ JS) ---

// 3. Синхронная обработка POST /register-fallback (без JS)
if ($requestUri === '/register-fallback' && $requestMethod === 'POST') {
    $errors = Validator::validate($inputData);
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_data'] = $inputData;
        header('Location: ' . $scriptName . '/');
        exit;
    }
    $newUser = $userModel->create($inputData);
    $_SESSION['user_id'] = $newUser['id'];
    $_SESSION['just_registered'] = $newUser;
    header('Location: ' . $scriptName . '/profile?id=' . $newUser['id']);
    exit;
}

// 4. Синхронная обработка PUT /update-fallback (без JS)
if ($requestUri === '/update-fallback' && $requestMethod === 'PUT') {
    $userId = (int)($inputData['user_id'] ?? 0);
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $userId) {
        die("Доступ запрещен");
    }
    $errors = Validator::validate($inputData);
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        header('Location: ' . $scriptName . '/profile?id=' . $userId);
        exit;
    }
    $userModel->update($userId, $inputData);
    $_SESSION['flash_message'] = 'Данные успешно обновлены синхронно!';
    header('Location: ' . $scriptName . '/profile?id=' . $userId);
    exit;
}

// --- СТРАНИЦЫ HTML ---
if ($requestUri === '/' || $requestUri === '/index.html') {
    include __DIR__ . '/../src/Views/registration.php';
    exit;
}

if ($requestUri === '/profile') {
    include __DIR__ . '/../src/Views/profile.php';
    exit;
}

http_response_code(404);
echo "Страница не найдена. Запрошенный путь: " . htmlspecialchars($requestUri);
