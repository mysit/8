<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ENTRY_POINT', true);

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
?>
<?php
use App\Models\User;
use App\Services\Validator;

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Очищаем префиксы
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

// Парсинг входных данных
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

$userModel =
