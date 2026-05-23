<?php
namespace App\Services;

class Validator {
    public static function validate(array $data): array {
        $errors = [];

        if (empty($data['fullName']) || trim($data['fullName']) === '') {
            $errors['fullName'] = 'Пожалуйста, введите ФИО.';
        }
        if (empty($data['email']) || trim($data['email']) === '') {
            $errors['email'] = 'Пожалуйста, введите Email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный формат Email.';
        }
        if (empty($data['message']) || trim($data['message']) === '') {
            $errors['message'] = 'Пожалуйста, введите сообщение.';
        }

        return $errors;
    }
}
