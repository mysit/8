<?php
namespace App\Models;

use PDO;
use App\Services\Database;

class User {
    private PDO $pdo;
    private string $nameColumn;

    public function __construct() {
        $this->pdo = Database::getConnection();
        $this->detectNameColumn();
    }

    private function detectNameColumn(): void {
        $stmt = $this->pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach (['full_name', 'fullName', 'name', 'fio'] as $col) {
            if (in_array($col, $columns)) {
                $this->nameColumn = $col;
                return;
            }
        }
        $this->nameColumn = 'fio';
    }

    public function create(array $data): array {
        $login = 'user_' . bin2hex(random_bytes(2));
        $password = (string)random_int(100000, 999999);

        $fields = [
            $this->nameColumn => $data['fullName'],
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

        $sql = sprintf("INSERT INTO users (%s) VALUES (%s)", 
            implode(', ', $insertFields), 
            implode(', ', $placeholders)
        );
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        $id = (int)$this->pdo->lastInsertId();

        return [
            'id' => $id,
            'login' => $login,
            'password' => $password
        ];
    }

    public function update(int $id, array $data): bool {
        $fields = [
            $this->nameColumn => $data['fullName'],
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

    public function isOwner(int $userId): bool {
        return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId;
    }

    private function getTableColumns(): array {
        $stmt = $this->pdo->query("DESCRIBE users");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
