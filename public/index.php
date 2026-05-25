<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

define('ENTRY_POINT', true);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) { require $file; }
});
?>
<?php
use App\Models\User;
use App\Services\Validator;

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$inputData = [];
if (str_contains($contentType, 'application/json')) {
    $inputData = json_decode(file_get_contents('php://input'), true) ?? [];
} elseif (str_contains($contentType, 'application/xml') || str_contains($contentType, 'text/xml')) {
    $xml = simplexml_load_string(file_get_contents('php://input'));
    if ($xml) {
        $inputData = json_decode(json_encode((array)$xml), true);
    }
} else {
    $inputData = $_POST;
}

$userModel = new User();

// POST /api/users
if ($requestUri === '/api/users' && $requestMethod === 'POST') {
    header('Content-Type: application/json');
    try {
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
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Внутренняя ошибка сервера']);
    }
    exit;
}

// PUT /api/users/{id}
if (preg_match('#^/api/users/(\d+)$#', $requestUri, $m) && $requestMethod === 'PUT') {
    header('Content-Type: application/json');
    try {
        $userId = (int)$m[1];
        
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Доступ запрещён']);
            exit;
        }
        
        $errors = Validator::validate($inputData, true);
        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }
        
        $userModel->update($userId, $inputData);
        echo json_encode(['status' => 'success', 'message' => 'Данные обновлены']);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json
