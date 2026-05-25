<?php
namespace App\Models;

use PDO;
use App\Services\Database;

class User {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function create(array $data): array {
        $login = 'user_' . bin2hex(random_bytes(2));
        $password = (string)random_int(100000, 999999);

        // Фиксированные поля — если колонки нет в БД, запрос упадёт, это честно
        $fields = [
            'fio' => $data['fullName'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'organization' => $data['organization'] ?? '',
            'message' => $data['message'],
            'login' => $login,
            'password' => $password,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT)
        ];

        $columns = $this->getTableColumns();
        $insertFields = [];
        $placeholders = [];
        $values = [];

        foreach ($fields as $col => $val) {
            if (in_array($col, $columns)) {
                $insertFields[] = $col;
                $placeholders[] = '?';
                $values[] = $val;
            }
        }

        if (empty($insertFields)) {
            throw new \RuntimeException('Нет полей для вставки в БД');
        }

        $sql = sprintf("INSERT INTO users (%s) VALUES (%s)", 
            implode(', ', $insertFields), 
            implode(', ', $placeholders)
        );
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        $id = (int)$this->pdo->lastInsertId();
        return ['id' => $id, 'login' => $login, 'password' => $password];
    }

    public function update(int $id, array $data): bool {
        $fields = [
            'fio' => $data['fullName'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'organization' => $data['organization'] ?? '',
            'message' => $data['message']
        ];

        $columns = $this->getTableColumns();
        $sets = [];
        $values = [];

        foreach ($fields as $col => $val) {
            if (in_array($col, $columns)) {
                $sets[] = "$col = ?";
                $values[] = $val;
            }
        }

        if (empty($sets)) {
            return false;
        }

        $values[] = $id;
        $sql = sprintf("UPDATE users SET %s WHERE id = ?", implode(', ', $sets));
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private function getTableColumns(): array {
        $stmt = $this->pdo->query("DESCRIBE users");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
