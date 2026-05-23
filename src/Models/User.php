<?php
namespace App\Models;

use PDO;
use PDOException;

class User {
    private $pdo;
    private $nameColumn = 'fio';

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

            // Автоматически читаем реальные колонки таблицы, чтобы исключить любые ошибки 1054
            $stmt = $this->pdo->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Сканируем таблицу на предмет того, как назвали поле ФИО
            foreach (['full_name', 'fullName', 'name', 'fio'] as $possibleName) {
                if (in_array($possibleName, $columns)) {
                    $this->nameColumn = $possibleName;
                    break;
                }
            }
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения к БД: ' . $e->getMessage()]);
            exit;
        }
    }

    public function create($data) {
        $login = 'user_' . rand(1000, 9999);
        $pass = rand(100000, 999999);

        $stmt = $this->pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $insertData = [];
        $fields = [];
        $placeholders = [];

        // Карта маппинга полей формы на колонки в БД
        $fieldMapping = [
            $this->nameColumn => $data['fullName'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'organization' => $data['organization'] ?? '',
            'message' => $data['message'] ?? '',
            'login' => $login,
            'password' => $pass
        ];

        // Собираем SQL только из тех колонок, которые реально существуют в твоей БД
        foreach ($fieldMapping as $col => $val) {
            if (in_array($col, $columns)) {
                $fields[] = $col;
                $placeholders[] = '?';
                $insertData[] = $val;
            }
        }

        $sql = "INSERT INTO users (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($insertData);

        $id = $this->pdo->lastInsertId();
        if (!$id || (int)$id === 0) {
            $checkStmt = $this->pdo->prepare("SELECT id FROM users WHERE login = ?");
            $checkStmt->execute([$login]);
            $fetched = $checkStmt->fetch();
            if ($fetched) {
                $id = $fetched['id'];
            }
        }

        return [
            'id' => $id,
            'login' => $login,
            'password' => $pass
        ];
    }

    public function update($id, $data) {
        $stmt = $this->pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $updateData = [];
        $sets = [];

        $fieldMapping = [
            $this->nameColumn => $data['fullName'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'organization' => $data['organization'] ?? '',
            'message' => $data['message'] ?? ''
        ];

        // Защита: обновляем только существующие в базе поля
        foreach ($fieldMapping as $col => $val) {
            if (in_array($col, $columns)) {
                $sets[] = "$col = ?";
                $updateData[] = $val;
            }
        }

        $updateData[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($updateData);
        return true;
    }
}
