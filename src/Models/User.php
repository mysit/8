<?php
namespace App\Models;

use PDO;

class User {
    private PDO $db;

    public function __construct() {
        $username = 'u82196';
        $password = '4736526';
        $dbname = 'u82196';
        $host = 'localhost';
        
        $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public function create(array $data): array {
        $login = 'user_' . uniqid();
        $plainPassword = bin2hex(random_bytes(4)); // Генерируем 8 символов пароля
        $passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("
            INSERT INTO users (login, password_hash, full_name, email, phone, organization, message)
            VALUES (:login, :password_hash, :full_name, :email, :phone, :organization, :message)
        ");

        $stmt->execute([
            ':login' => $login,
            ':password_hash' => $passwordHash,
            ':full_name' => $data['fullName'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':organization' => $data['organization'] ?? null,
            ':message' => $data['message']
        ]);

        return [
            'id' => $this->db->lastInsertId(),
            'login' => $login,
            'password' => $plainPassword
        ];
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET full_name = :full_name, 
                email = :email, 
                phone = :phone, 
                organization = :organization, 
                message = :message
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':full_name' => $data['fullName'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':organization' => $data['organization'] ?? null,
            ':message' => $data['message']
        ]);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
