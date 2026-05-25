<?php
namespace App\Services;

class Validator {
    public static function validate(array $data, bool $isUpdate = false): array {
        $errors = [];

        // fullName — обязательно всегда
        if (empty($data['fullName']) || trim($data['fullName']) === '') {
            $errors['fullName'] = 'Пожалуйста, введите ФИО.';
        }

        // Email — обязательно + формат
        if (empty($data['email']) || trim($data['email']) === '') {
            $errors['email'] = 'Пожалуйста, введите Email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный формат Email.';
        }

        // Message — обязательно всегда
        if (empty($data['message']) || trim($data['message']) === '') {
            $errors['message'] = 'Пожалуйста, введите сообщение.';
        }

        // Privacy checkbox — только при регистрации
        if (!$isUpdate && empty($data['privacy'])) {
            $errors['privacy'] = 'Необходимо согласие на обработку персональных данных.';
        }

        return $errors;
    }
}
