<?php
namespace App\Models;

use PDO;
use PDOException;

class User {
    private $pdo;

    public function __construct() {
        $host = 'localhost';
        $dbname = 'u82196';
        $username = 'u82196';
        $password = '4736526';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            // Если БД упадет, мы вернем красивый JSON, чтобы JS не ломался от HTML-ошибок
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения к БД: ' . $e->getMessage()]);
            exit;
        }
    }

    public function create($data) {
        // Генерируем случайные доступы для нового пользователя по ТЗ
        $login = 'user_' . rand(1000, 9999);
        $pass = rand(100000, 999999);

        $stmt = $this->pdo->prepare("INSERT INTO users (fullName, email, phone, organization, message, login, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['fullName'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['organization'] ?? '',
            $data['message'] ?? '',
            $login,
            $pass
        ]);

        return [
            'id' => $this->pdo->lastInsertId(),
            'login' => $login,
            'password' => $pass
        ];
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE users SET fullName = ?, email = ?, phone = ?, organization = ?, message = ? WHERE id = ?");
        $stmt->execute([
            $data['fullName'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['organization'] ?? '',
            $data['message'] ?? '',
            $id
        ]);
        return true;
    }
}
