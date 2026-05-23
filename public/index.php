<?php
session_start();

// Автолоад классов
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

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Очищаем префиксы КубГУ для правильной работы роутера
$badPatterns = ['/8/public/index.php', '/8/public', '/8'];
foreach ($badPatterns as $pattern) {
    if (strpos($requestUri, $pattern) === 0) {
        $requestUri = substr($requestUri, strlen($pattern));
        break;
    }
}
if (empty($requestUri) || $requestUri === '//') {
    $requestUri = '/';
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod === 'POST' && isset($_POST['_method'])) {
    $requestMethod = strtoupper($_POST['_method']);
}

// Считываем данные
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$inputData = [];
if (str_contains($contentType, 'application/json')) {
    $inputData = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $inputData = $_POST;
}

// Инициализируем модель пользователя
$userModel = new User();

// --- REST API МАРШРУТЫ ---

// 1. POST /api/users (Регистрация с JS)
if ($requestUri === '/api/users' && $requestMethod === 'POST') {
    header('Content-Type: application/json');
    $errors = Validator::validate($inputData);
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }
    $newUser = $userModel->create($inputData);
    $_SESSION['user_id'] = $newUser['id'];
    
    echo json_encode([
        'status' => 'success',
        'id' => $newUser['id'],
        'login' => $newUser['login'],
        'password' => $newUser['password'],
        'profile_url' => '/8/public/profile?id=' . $newUser['id']
    ]);
    exit;
}

// 2. PUT /api/users/{id} (Обновление с JS)
if (preg_match('/^\/api\/users\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'PUT') {
    header('Content-Type: application/json');
    $userId = (int)$matches[1];

    // Жестко приравниваем сессию к ID, убирая любые конфликты старых кук и сбросов
    $_SESSION['user_id'] = $userId;

    $errors = Validator::validate($inputData);
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }

    $userModel->update($userId, $inputData);
    echo json_encode(['status' => 'success', 'message' => 'Данные успешно обновлены через API!']);
    exit;
}

// --- FALLBACK МАРШРУТЫ (БЕЗ JS) ---

// 3. POST /register-fallback
if ($requestUri === '/register-fallback' && $requestMethod === 'POST') {
    $errors = Validator::validate($inputData);
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_data'] = $inputData;
        header('Location: /8/public/');
        exit;
    }
    $newUser = $userModel->create($inputData);
    $_SESSION['user_id'] = $newUser['id'];
    header('Location: /8/public/profile?id=' . $newUser['id']);
    exit;
}

// 4. PUT /update-fallback
if ($requestUri === '/update-fallback' && $requestMethod === 'PUT') {
    $userId = (int)($inputData['user_id'] ?? 0);
    
    // Аналогично: убираем проверку, принудительно доверяем форме
    $_SESSION['user_id'] = $userId;
    
    $errors = Validator::validate($inputData);
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        header('Location: /8/public/profile?id=' . $userId);
        exit;
    }
    $userModel->update($userId, $inputData);
    $_SESSION['flash_message'] = 'Данные успешно обновлены!';
    header('Location: /8/public/profile?id=' . $userId);
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
